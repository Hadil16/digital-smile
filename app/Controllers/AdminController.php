<?php
/**
 * app/Controllers/AdminController.php
 * -----------------------------------------------------------------
 * Espace administrateur (rôle 'admin') : revue des demandes —
 * approuver / refuser / affecter à un employé. Aucune requête SQL
 * ici : tout passe par les modèles Order et Employee.
 * -----------------------------------------------------------------
 */

class AdminController
{
    // Modèles chargés en "lazy" (pas de connexion DB inutile).
    private ?Order    $orderModel    = null;
    private ?Employee $employeeModel = null;

    private function orderM(): Order       { return $this->orderModel    ??= new Order(); }
    private function employeeM(): Employee { return $this->employeeModel ??= new Employee(); }

    /** Tableau de bord : accès à la gestion des demandes + nombre en attente. */
    public function dashboard(): void
    {
        require_role('admin');
        $pendingCount = count($this->orderM()->allPending());
        require ROOT_PATH . '/app/Views/admin/dashboard.php';
    }

    /** Liste de revue : demandes en attente + vue d'ensemble + employés. */
    public function orders(): void
    {
        require_role('admin');
        $pending   = $this->orderM()->allPending();
        $allOrders = $this->orderM()->allWithStatus();
        $employees = $this->employeeM()->allActive();

        // Message éphémère (flash) posé par une action précédente.
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require ROOT_PATH . '/app/Views/admin/orders.php';
    }

    /** Approuve une commande (statut -> approved). */
    public function approveOrder(): void
    {
        require_role('admin');
        if (!csrf_verify()) {
            $_SESSION['flash'] = 'Session expirée, merci de réessayer.';
            redirect('/admin/commandes');
        }
        $ok = $this->orderM()->updateStatus((int) ($_POST['order_id'] ?? 0), 'approved');
        $_SESSION['flash'] = $ok ? 'Commande approuvée.' : 'Action impossible sur cette commande.';
        redirect('/admin/commandes');
    }

    /** Refuse une commande (statut -> rejected). */
    public function rejectOrder(): void
    {
        require_role('admin');
        if (!csrf_verify()) {
            $_SESSION['flash'] = 'Session expirée, merci de réessayer.';
            redirect('/admin/commandes');
        }
        $ok = $this->orderM()->updateStatus((int) ($_POST['order_id'] ?? 0), 'rejected');
        $_SESSION['flash'] = $ok ? 'Commande refusée.' : 'Action impossible sur cette commande.';
        redirect('/admin/commandes');
    }

    /** Affecte une commande à un employé (statut -> in_progress + projet). */
    public function assignOrder(): void
    {
        require_role('admin');
        if (!csrf_verify()) {
            $_SESSION['flash'] = 'Session expirée, merci de réessayer.';
            redirect('/admin/commandes');
        }

        $orderId    = (int) ($_POST['order_id'] ?? 0);
        $employeeId = (int) ($_POST['employee_id'] ?? 0);

        // L'employé doit exister (parmi les employés actifs).
        $validIds = array_map('intval', array_column($this->employeeM()->allActive(), 'id'));
        if ($orderId <= 0 || !in_array($employeeId, $validIds, true)) {
            $_SESSION['flash'] = 'Merci de choisir un employé valide.';
            redirect('/admin/commandes');
        }

        $this->orderM()->assignEmployee($orderId, $employeeId);
        $_SESSION['flash'] = 'Commande affectée à l\'employé.';
        redirect('/admin/commandes');
    }
}
