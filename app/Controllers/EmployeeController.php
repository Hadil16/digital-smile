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

    // Photo de profil : images seules, 2 Mo max (même contrôle MIME réel).
    private const PHOTO_MAX     = 2 * 1024 * 1024; // 2 Mo
    private const PHOTO_ALLOWED = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
    ];

    private ?Project  $projectModel  = null;
    private ?Employee $employeeModel = null;
    private function projects(): Project   { return $this->projectModel  ??= new Project(); }
    private function employees(): Employee { return $this->employeeModel ??= new Employee(); }

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

    /** Liste des projets assignés à l'employé + entête de profil + KPI. */
    public function tasks(): void
    {
        require_role('employee');
        $employeeId = $this->currentEmployeeId();
        $projects   = $this->projects()->forEmployee($employeeId);
        $profile    = $this->employees()->profileForUser((int) $_SESSION['user_id']);
        $stats      = $this->projects()->statsForEmployee($employeeId);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require ROOT_PATH . '/app/Views/employee/tasks.php';
    }

    /** Mon profil : formulaire (photo, expérience, bio). */
    public function profile(): void
    {
        require_role('employee');
        $profile = $this->employees()->profileForUser((int) $_SESSION['user_id']);
        $canEdit = $this->employees()->hasProfileColumns();

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        $error = null;
        require ROOT_PATH . '/app/Views/employee/profile.php';
    }

    /** Enregistre le profil (CSRF + fiche propre + validation). */
    public function saveProfile(): void
    {
        require_role('employee');
        if (!csrf_verify()) {
            $_SESSION['flash'] = 'Session expirée, merci de réessayer.';
            redirect('/employe/profil');
        }

        // Fiche de l'employé connecté (contrôle de propriété : par son user_id).
        $profile = $this->employees()->profileForUser((int) $_SESSION['user_id']);
        if ($profile === null) {
            $_SESSION['flash'] = 'Fiche employé introuvable.';
            redirect('/employe/profil');
        }
        $canEdit = $this->employees()->hasProfileColumns();
        if (!$canEdit) {
            // Colonnes absentes : on l'explique au lieu de planter (dégradation propre).
            $_SESSION['flash'] = 'Profil indisponible : la migration base de données n\'a pas encore été appliquée.';
            redirect('/employe/profil');
        }

        // Validation des champs texte.
        $expRaw = trim((string) ($_POST['experience_years'] ?? ''));
        $exp    = ($expRaw === '') ? null : (int) $expRaw;
        $bio    = trim((string) ($_POST['bio'] ?? ''));

        $error = null;
        if ($exp !== null && ($exp < 0 || $exp > 50)) {
            $error = 'L\'expérience doit être comprise entre 0 et 50 ans.';
        } elseif (mb_strlen($bio) > 500) {
            $error = 'La biographie ne doit pas dépasser 500 caractères.';
        }

        // Photo (facultative) : même sécurité que les livrables, images seules.
        $photoStored = null; // reste null si aucune nouvelle photo
        if ($error === null && !empty($_FILES['photo']) && ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $photoStored = $this->handlePhotoUpload($_FILES['photo'], $error);
        }

        if ($error !== null) {
            // Ré-affiche le formulaire avec le message (valeurs saisies conservées).
            $flash = null;
            require ROOT_PATH . '/app/Views/employee/profile.php';
            return;
        }

        // Mise à jour : la photo n'est incluse que si une nouvelle a été déposée.
        $data = ['experience_years' => $exp, 'bio' => ($bio === '' ? null : $bio)];
        if ($photoStored !== null) {
            $data['photo'] = $photoStored;
        }
        $this->employees()->updateProfile((int) $profile['id'], $data);

        $_SESSION['flash'] = 'Profil mis à jour.';
        redirect('/employe/profil');
    }

    /** Ma bibliothèque : les livrables déposés par l'employé. */
    public function library(): void
    {
        require_role('employee');
        $deliverables = $this->projects()->deliverablesForEmployee($this->currentEmployeeId());
        require ROOT_PATH . '/app/Views/employee/library.php';
    }

    /**
     * Traite l'upload de la photo de profil (image seule, 2 Mo, MIME réel via
     * finfo, nom de stockage aléatoire). Renvoie le chemin stocké
     * (ex : 'uploads/ab..cd.jpg') ou null en cas d'erreur (message dans $error).
     */
    private function handlePhotoUpload(array $file, ?string &$error): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $error = 'Photo non reçue (ou trop volumineuse).';
            return null;
        }
        if ($file['size'] <= 0 || $file['size'] > self::PHOTO_MAX) {
            $error = 'Photo trop volumineuse (2 Mo maximum).';
            return null;
        }

        // Extension ET type MIME réel doivent concorder (jpg/png uniquement).
        $ext   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset(self::PHOTO_ALLOWED[$ext]) || !in_array($mime, self::PHOTO_ALLOWED[$ext], true)) {
            $error = 'Photo refusée. Formats acceptés : JPG, PNG.';
            return null;
        }

        // Nom de stockage aléatoire, extension normalisée (jamais le nom d'origine).
        $ext    = ($ext === 'jpeg') ? 'jpg' : $ext;
        $stored = bin2hex(random_bytes(16)) . '.' . $ext;

        if (!is_dir(UPLOAD_PATH)) {
            @mkdir(UPLOAD_PATH, 0755, true);
        }
        if (!move_uploaded_file($file['tmp_name'], UPLOAD_PATH . '/' . $stored)) {
            $error = 'Échec de l\'enregistrement de la photo.';
            return null;
        }
        return 'uploads/' . $stored;
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
