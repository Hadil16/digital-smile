<?php
/**
 * app/Controllers/EmployeeController.php
 * -----------------------------------------------------------------
 * Espace employé (rôle 'employee') : voir ses tâches assignées,
 * mettre à jour la progression, déposer le fichier livrable.
 * Aucune requête SQL ici : tout passe par le modèle Project. La
 * gestion du fichier téléversé (type/taille/déplacement) reste ici
 * car c'est de l'entrée HTTP, pas de la logique base de données.
 * -----------------------------------------------------------------
 */

class EmployeeController
{
    // Taille et types autorisés pour un livrable (sécurité uploads).
    private const MAX_BYTES = 10 * 1024 * 1024; // 10 Mo
    private const ALLOWED   = [                  // extension => types MIME réels acceptés
        'pdf'  => ['application/pdf'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'zip'  => ['application/zip', 'application/x-zip-compressed'],
    ];

    private ?Project $projectModel = null;
    private function projects(): Project { return $this->projectModel ??= new Project(); }

    /** Id de la fiche employé de l'utilisateur connecté (0 si aucune). */
    private function currentEmployeeId(): int
    {
        return $this->projects()->employeeIdForUser((int) $_SESSION['user_id']) ?? 0;
    }

    /** Tableau de bord : accès aux tâches + nombre de tâches assignées. */
    public function dashboard(): void
    {
        require_role('employee');
        $taskCount = count($this->projects()->forEmployee($this->currentEmployeeId()));
        require ROOT_PATH . '/app/Views/employee/dashboard.php';
    }

    /** Liste des projets assignés à l'employé. */
    public function tasks(): void
    {
        require_role('employee');
        $projects = $this->projects()->forEmployee($this->currentEmployeeId());

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require ROOT_PATH . '/app/Views/employee/tasks.php';
    }

    /** Met à jour la progression (0..100) d'une tâche de l'employé. */
    public function updateProgress(): void
    {
        require_role('employee');
        if (!csrf_verify()) {
            $_SESSION['flash'] = 'Session expirée, merci de réessayer.';
            redirect('/employe/taches');
        }

        $progress = (int) ($_POST['progress'] ?? -1);
        if ($progress < 0 || $progress > 100) {
            $_SESSION['flash'] = 'La progression doit être comprise entre 0 et 100.';
            redirect('/employe/taches');
        }

        // La propriété est revérifiée dans le modèle (par employeeId).
        $ok = $this->projects()->updateProgress(
            (int) ($_POST['project_id'] ?? 0),
            $this->currentEmployeeId(),
            $progress
        );
        $_SESSION['flash'] = $ok ? 'Progression mise à jour.' : 'Action impossible sur cette tâche.';
        redirect('/employe/taches');
    }

    /** Dépose le fichier livrable final (types/taille contrôlés). */
    public function uploadFile(): void
    {
        require_role('employee');
        if (!csrf_verify()) {
            $_SESSION['flash'] = 'Session expirée, merci de réessayer.';
            redirect('/employe/taches');
        }

        $employeeId = $this->currentEmployeeId();
        $projectId  = (int) ($_POST['project_id'] ?? 0);

        // 1. Propriété : la tâche doit appartenir à cet employé.
        $project = $this->projects()->find($projectId, $employeeId);
        if ($project === null) {
            $_SESSION['flash'] = 'Tâche introuvable ou non autorisée.';
            redirect('/employe/taches');
        }

        // 2. Fichier bien reçu ?
        $file = $_FILES['file'] ?? null;
        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = 'Aucun fichier reçu (ou fichier trop volumineux).';
            redirect('/employe/taches');
        }

        // 3. Taille (10 Mo max).
        if ($file['size'] <= 0 || $file['size'] > self::MAX_BYTES) {
            $_SESSION['flash'] = 'Fichier trop volumineux (10 Mo maximum).';
            redirect('/employe/taches');
        }

        // 4. Type autorisé : extension ET type MIME réel (finfo) doivent concorder.
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset(self::ALLOWED[$ext]) || !in_array($mime, self::ALLOWED[$ext], true)) {
            $_SESSION['flash'] = 'Type de fichier refusé. Autorisés : PDF, JPG, PNG, ZIP.';
            redirect('/employe/taches');
        }

        // 5. Nom de stockage ALÉATOIRE, extension normalisée (jamais le nom d'origine,
        //    jamais un exécutable). jpeg -> jpg.
        $ext    = ($ext === 'jpeg') ? 'jpg' : $ext;
        $stored = bin2hex(random_bytes(16)) . '.' . $ext;

        if (!is_dir(UPLOAD_PATH)) {
            @mkdir(UPLOAD_PATH, 0755, true);
        }
        $destPath = UPLOAD_PATH . '/' . $stored;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $_SESSION['flash'] = 'Échec de l\'enregistrement du fichier.';
            redirect('/employe/taches');
        }

        // 6. Enregistrer en base + passer la commande "delivered".
        $ok = $this->projects()->recordDeliverable(
            $projectId, $employeeId, (int) $_SESSION['user_id'],
            $file['name'], 'uploads/' . $stored, (int) $file['size']
        );
        if (!$ok) {
            @unlink($destPath); // on retire le fichier si la base refuse
            $_SESSION['flash'] = 'Action impossible sur cette tâche.';
            redirect('/employe/taches');
        }

        // Notifier le client que son projet est livré.
        $notif = new Notification();
        $info  = $notif->orderInfo((int) $project['order_id']);
        if ($info) {
            $notif->create((int) $info['client_user_id'],
                "Votre projet {$info['code']} est livré",
                '/client/commande/' . rawurlencode($info['code']));
        }

        $_SESSION['flash'] = 'Livrable envoyé. La commande est marquée « livrée ».';
        redirect('/employe/taches');
    }
}
