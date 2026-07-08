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
        $code = $this->orders()->createForClient((int) $_SESSION['user_id'], [
            'service_id'   => $serviceId,
            // Pas de champ "nom de projet" dans le formulaire : on le dérive du service.
            'project_name' => $names[$serviceId],
            'description'  => $old['description'],
            'budget'       => ($old['budget'] !== '' ? (float) $old['budget'] : null),
            'deadline'     => $old['deadline'],
        ]);

        // Notifier tous les admins de la nouvelle demande.
        $notif = new Notification();
        foreach ($notif->adminUserIds() as $adminId) {
            $notif->create($adminId, "Nouvelle demande $code", '/admin/commandes');
        }

        redirect('/client');
    }

    /** Détail d'une commande (avec contrôle de propriété par le numéro). */
    public function showOrder(string $number): void
    {
        require_role('client');

        $order = $this->orders()->findForClient($number, (int) $_SESSION['user_id']);
        if ($order === null) {
            $this->notFound();          // pas la sienne (ou inexistante)
            return;
        }

        // Livrable proposé seulement quand la commande est livrée/terminée.
        $deliverable = in_array($order['status'], ['delivered', 'completed'], true)
            ? $this->orders()->deliverableFor((int) $order['id'])
            : null;

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require ROOT_PATH . '/app/Views/client/order-detail.php';
    }

    /** Télécharge le livrable en flux, avec contrôle de propriété strict. */
    public function downloadFile(string $number): void
    {
        require_role('client');

        $order = $this->orders()->findForClient($number, (int) $_SESSION['user_id']);
        if ($order === null) { $this->notFound(); return; }

        $file = $this->orders()->deliverableFor((int) $order['id']);
        if ($file === null) { $this->notFound(); return; }

        // On ne fait JAMAIS confiance au chemin stocké : on ne garde que le nom
        // de fichier (anti-traversée) et on le cherche dans le dossier uploads.
        $absolute = UPLOAD_PATH . '/' . basename($file['stored_path']);
        if (!is_file($absolute)) { $this->notFound(); return; }

        // Nom présenté = nom d'origine, nettoyé pour éviter l'injection d'en-tête.
        $original  = $file['original_name'] !== '' ? $file['original_name'] : basename($absolute);
        $asciiName = preg_replace('/[\r\n"]+/', '_', $original);

        header('Content-Type: application/octet-stream');       // force le téléchargement
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: ' . (string) filesize($absolute));
        header(
            "Content-Disposition: attachment; filename=\"$asciiName\"; "
            . "filename*=UTF-8''" . rawurlencode($original)
        );
        readfile($absolute);
        exit;
    }

    /** Le client confirme la réception -> commande 'completed'. */
    public function confirmReception(string $number): void
    {
        require_role('client');
        if (!csrf_verify()) {
            $_SESSION['flash'] = 'Session expirée, merci de réessayer.';
            redirect('/client/commande/' . rawurlencode($number));
        }

        $order = $this->orders()->findForClient($number, (int) $_SESSION['user_id']);
        if ($order === null) { $this->notFound(); return; }

        $ok = $this->orders()->markCompleted((int) $order['id'], (int) $_SESSION['user_id']);
        $_SESSION['flash'] = $ok
            ? 'Réception confirmée, merci !'
            : 'Cette commande ne peut pas être confirmée pour le moment.';
        redirect('/client/commande/' . rawurlencode($number));
    }

    /** Page 404 propre du site (commande introuvable ou non autorisée). */
    private function notFound(): void
    {
        http_response_code(404);
        require ROOT_PATH . '/app/Views/errors/404.php';
    }
}
