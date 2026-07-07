<?php
/**
 * app/Controllers/ClientController.php
 * -----------------------------------------------------------------
 * Espace client (rôle 'client') : tableau de bord + création et
 * liste des demandes de projet. Aucune requête SQL ici : tout passe
 * par les modèles Order et Service.
 * -----------------------------------------------------------------
 */

class ClientController
{
    // Modèles chargés en "lazy" (pas de connexion DB inutile).
    private ?Order   $orders   = null;
    private ?Service $services = null;

    private function orders(): Order     { return $this->orders   ??= new Order(); }
    private function services(): Service { return $this->services ??= new Service(); }

    /** Tableau de bord : bouton "nouvelle demande" + liste des commandes. */
    public function dashboard(): void
    {
        require_role('client');
        $orders = $this->orders()->allForClient((int) $_SESSION['user_id']);
        require ROOT_PATH . '/app/Views/client/dashboard.php';
    }

    /** Affiche le formulaire de nouvelle demande (services au choix). */
    public function showNewOrder(): void
    {
        require_role('client');
        $services = $this->services()->allActive();
        $error = null;
        $old   = ['service_id' => '', 'description' => '', 'budget' => '', 'deadline' => ''];
        require ROOT_PATH . '/app/Views/client/new-order.php';
    }

    /** Traite l'envoi du formulaire : validation puis création. */
    public function createOrder(): void
    {
        require_role('client');

        // Services (pour valider le choix ET ré-afficher le formulaire).
        $services = $this->services()->allActive();
        $names    = array_column($services, 'name', 'id'); // id => nom

        // Valeurs reçues (conservées pour re-remplir en cas d'erreur).
        $old = [
            'service_id'  => (string) ($_POST['service_id'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'budget'      => trim($_POST['budget'] ?? ''),
            'deadline'    => trim($_POST['deadline'] ?? ''),
        ];

        // Validation, messages en français.
        $error = null;
        if (!csrf_verify()) {
            $error = 'Session expirée, merci de réessayer.';
        } else {
            $serviceId = (int) $old['service_id'];
            $d = DateTime::createFromFormat('Y-m-d', $old['deadline']);

            if (!isset($names[$serviceId])) {
                $error = 'Merci de choisir un service valide.';
            } elseif ($old['description'] === '') {
                $error = 'Merci de décrire votre besoin.';
            } elseif ($old['budget'] !== '' && (!is_numeric($old['budget']) || (float) $old['budget'] < 0)) {
                $error = 'Le budget doit être un nombre positif (ou laissé vide).';
            } elseif (!($d && $d->format('Y-m-d') === $old['deadline'] && $d > new DateTime('today'))) {
                $error = 'Merci d\'indiquer une échéance valide (une date future).';
            }
        }

        if ($error !== null) {
            require ROOT_PATH . '/app/Views/client/new-order.php';
            return;
        }

        // Tout est valide : on crée la commande (statut pending).
        $serviceId = (int) $old['service_id'];
        $this->orders()->createForClient((int) $_SESSION['user_id'], [
            'service_id'   => $serviceId,
            // Pas de champ "nom de projet" dans le formulaire : on le dérive du service.
            'project_name' => $names[$serviceId],
            'description'  => $old['description'],
            'budget'       => ($old['budget'] !== '' ? (float) $old['budget'] : null),
            'deadline'     => $old['deadline'],
        ]);

        redirect('/client');
    }
}
