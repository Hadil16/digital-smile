<?php
/**
 * app/Controllers/AdminController.php
 * -----------------------------------------------------------------
 * Espace administrateur (rôle 'admin') : revue des demandes —
 * approuver / refuser / affecter à un employé. Aucune requête SQL
 * ici : tout passe par les modèles Order, Employee et Client.
 * -----------------------------------------------------------------
 */

// Le modèle Client n'est chargé nulle part ailleurs : on l'inclut ici
// (require_once = sans doublon si le front controller le charge un jour).
require_once ROOT_PATH . '/app/Models/Client.php';

class AdminController
{
    // Modèles chargés en "lazy" (pas de connexion DB inutile).
    private ?Order      $orderModel      = null;
    private ?Employee   $employeeModel   = null;
    private ?User       $userModel       = null;
    private ?Department $departmentModel = null;
    private ?Client     $clientModel     = null;
    private ?Invoice    $invoiceModel    = null;

    private function orderM(): Order          { return $this->orderModel      ??= new Order(); }
    private function employeeM(): Employee    { return $this->employeeModel   ??= new Employee(); }
    private function userM(): User            { return $this->userModel       ??= new User(); }
    private function deptM(): Department       { return $this->departmentModel ??= new Department(); }
    private function clientM(): Client         { return $this->clientModel     ??= new Client(); }
    private function invoiceM(): Invoice       { return $this->invoiceModel    ??= new Invoice(); }

    /** Tableau de bord : cartes de statistiques + accès à la gestion. */
    public function dashboard(): void
    {
        require_role('admin');

        // Chiffres pour les cartes (comptés en direct dans la base).
        $statusCounts   = $this->orderM()->countByStatus();     // par statut
        $totalOrders    = $this->orderM()->countTotal();
        $totalClients   = $this->clientM()->countTotal();
        $totalEmployees = $this->employeeM()->countTotal();
        $totalInvoices  = $this->invoiceM()->countTotal();

        // Séries pour les graphiques (converties en JSON dans la vue).
        $monthly     = $this->orderM()->monthlyCounts(6); // courbe : commandes/mois
        $topServices = $this->orderM()->topServices(5);   // barres : services les plus demandés

        require ROOT_PATH . '/app/Views/admin/dashboard.php';
    }

    /** Liste de revue : demandes en attente + vue d'ensemble + employés. */
    public function orders(): void
    {
        require_role('admin');
        $pending            = $this->orderM()->allPending();
        $approvedUnassigned = $this->orderM()->allApprovedUnassigned(); // acceptées, sans affectation
        $allOrders          = $this->orderM()->allWithStatus();
        $employees          = $this->employeeM()->allActive();

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

    /** Facturation : commandes terminées à facturer + factures déjà émises. */
    public function invoices(): void
    {
        require_role('admin');
        $toInvoice = $this->orderM()->completedWithoutInvoice(); // candidates
        $invoices  = $this->invoiceM()->allWithDetails();

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require ROOT_PATH . '/app/Views/admin/invoices.php';
    }

    /** Génère la facture d'une commande terminée (HT + TVA + TTC). */
    public function generateInvoice(): void
    {
        require_role('admin');
        if (!csrf_verify()) {
            $_SESSION['flash'] = 'Session expirée, merci de réessayer.';
            redirect('/admin/factures');
        }

        $code = $this->invoiceM()->createFromOrder((int) ($_POST['order_id'] ?? 0));
        $_SESSION['flash'] = $code !== ''
            ? "Facture $code générée."
            : 'Facturation impossible : commande non terminée ou déjà facturée.';
        redirect('/admin/factures');
    }

    /** Détail d'une facture (numéro dans l'URL). */
    public function showInvoice(string $number): void
    {
        require_role('admin');
        $invoice = $this->invoiceM()->findByNumber($number);
        if ($invoice === null) {
            http_response_code(404);
            require ROOT_PATH . '/app/Views/errors/404.php';
            return;
        }
        require ROOT_PATH . '/app/Views/admin/invoice-detail.php';
    }

    /** Gestion de l'équipe : formulaire de création + liste des employés. */
    public function employees(): void
    {
        require_role('admin');
        $employees   = $this->employeeM()->allWithDetails();
        $departments = $this->deptM()->allActive();

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $error = null;
        $old   = ['name' => '', 'email' => '', 'department_id' => ''];
        require ROOT_PATH . '/app/Views/admin/employees.php';
    }

    /** Crée un compte employé après validation (email unique, département valide). */
    public function createEmployee(): void
    {
        require_role('admin');

        // Données pour valider ET ré-afficher la page en cas d'erreur.
        $departments  = $this->deptM()->allActive();
        $employees    = $this->employeeM()->allWithDetails();
        $validDeptIds = array_map('intval', array_column($departments, 'id'));
        $flash        = null;

        $old = [
            'name'          => trim($_POST['name'] ?? ''),
            'email'         => trim($_POST['email'] ?? ''),
            'department_id' => (string) ($_POST['department_id'] ?? ''),
        ];
        $password = (string) ($_POST['password'] ?? '');

        // Validation, messages en français.
        $error = null;
        if (!csrf_verify()) {
            $error = 'Session expirée, merci de réessayer.';
        } elseif ($old['name'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            $error = 'Merci de saisir un nom, un email valide et un mot de passe d\'au moins 8 caractères.';
        } elseif (!in_array((int) $old['department_id'], $validDeptIds, true)) {
            $error = 'Merci de choisir un département valide.';
        } elseif ($this->userM()->emailExists($old['email'])) {
            $error = 'Un compte existe déjà avec cet email.';
        }

        if ($error !== null) {
            require ROOT_PATH . '/app/Views/admin/employees.php';
            return;
        }

        $this->userM()->createEmployee([
            'name'          => $old['name'],
            'email'         => $old['email'],
            'password'      => $password,
            'department_id' => (int) $old['department_id'],
        ]);
        $_SESSION['flash'] = 'Employé créé : ' . $old['name'] . '.';
        redirect('/admin/employes');
    }
}
