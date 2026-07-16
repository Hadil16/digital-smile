<?php
/**
 * app/Controllers/AuthController.php
 * -----------------------------------------------------------------
 * Authentification : inscription client, connexion (tous rôles),
 * déconnexion. Aucune logique SQL ici — tout passe par le modèle User.
 *
 * Sécurité appliquée (cf. AI_RULES §4) :
 *   - jeton CSRF vérifié sur chaque POST ;
 *   - mots de passe via password_verify() / password_hash() ;
 *   - session régénérée à la connexion (anti fixation de session) ;
 *   - message d'erreur de login volontairement GÉNÉRIQUE.
 * -----------------------------------------------------------------
 */

class AuthController
{
    // Modèle chargé en "lazy" : pas de connexion DB tant qu'on ne fait
    // qu'afficher un formulaire (showLogin / showRegister / logout).
    private ?User $users = null;

    private function users(): User
    {
        return $this->users ??= new User();
    }

    /** Affiche le formulaire de connexion. */
    public function showLogin(): void
    {
        $error = null;
        require ROOT_PATH . '/app/Views/auth/login.php';
    }

    /** Affiche le formulaire d'inscription. */
    public function showRegister(): void
    {
        $error = null;
        require ROOT_PATH . '/app/Views/auth/register.php';
    }

    /**
     * Traite l'inscription d'un nouveau client, puis le connecte.
     * En cas d'erreur : on ré-affiche le formulaire avec un message FR.
     */
    public function register(): void
    {
        if (!csrf_verify()) {
            $error = 'Session expirée, merci de réessayer.';
            require ROOT_PATH . '/app/Views/auth/register.php';
            return;
        }

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        // Validation simple (nom, email valide, mot de passe >= 8).
        $error = null;
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            $error = 'Merci de saisir un nom, un email valide et un mot de passe d\'au moins 8 caractères.';
        } elseif ($this->users()->emailExists($email)) {
            $error = 'Un compte existe déjà avec cet email.';
        }

        if ($error !== null) {
            require ROOT_PATH . '/app/Views/auth/register.php';
            return;
        }

        // Création + ouverture de session (régénérée pour éviter la fixation).
        $id = $this->users()->createClient([
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
        ]);

        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        $_SESSION['role']    = 'client';
        $_SESSION['name']    = $name;
        redirect('/client');
    }

    /**
     * Traite la connexion (admin, employé ou client) puis redirige
     * selon le rôle. Erreur générique si identifiants invalides.
     */
    public function login(): void
    {
        if (!csrf_verify()) {
            $error = 'Session expirée, merci de réessayer.';
            require ROOT_PATH . '/app/Views/auth/login.php';
            return;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        $user = $this->users()->findByEmail($email);

        // Un seul message pour "email inconnu" ET "mauvais mot de passe" :
        // on ne révèle jamais si un email est enregistré ou non.
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            $error = 'Email ou mot de passe incorrect.';
            require ROOT_PATH . '/app/Views/auth/login.php';
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role']    = $user['role_name'];
        $_SESSION['name']    = $user['full_name'];

        // Redirection selon le rôle (RBAC — jamais de test d'email en dur).
        $target = match ($user['role_name']) {
            'admin'    => '/admin',
            'employee' => '/employe',
            default    => '/client',
        };
        redirect($target);
    }

    /**
     * « Changer mon mot de passe » — commun à tous les rôles connectés.
     * GET : affiche le formulaire. POST : valide (CSRF, mot de passe actuel
     * correct, nouveau ≥ 8 caractères, confirmation identique, différent de
     * l'actuel) puis ré-hache en BCRYPT. Messages en français.
     */
    public function changePassword(): void
    {
        require_login(); // tous rôles connectés

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current = (string) ($_POST['current'] ?? '');
            $new     = (string) ($_POST['new'] ?? '');
            $confirm = (string) ($_POST['confirm'] ?? '');
            $uid     = (int) ($_SESSION['user_id'] ?? 0);

            if (!csrf_verify()) {
                $error = 'Session expirée, merci de réessayer.';
            } elseif (!$this->users()->verifyPassword($uid, $current)) {
                $error = 'Mot de passe actuel incorrect.';
            } elseif (strlen($new) < 8) {
                $error = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
            } elseif ($new !== $confirm) {
                $error = 'La confirmation ne correspond pas au nouveau mot de passe.';
            } elseif ($new === $current) {
                $error = 'Le nouveau mot de passe doit être différent de l\'actuel.';
            } else {
                $this->users()->updatePassword($uid, password_hash($new, PASSWORD_BCRYPT));
                $_SESSION['flash'] = 'Mot de passe mis à jour.';
                redirect('/compte/mot-de-passe');
            }
        }

        require ROOT_PATH . '/app/Views/account/password.php';
    }

    /** Déconnexion : on vide la session, le cookie, puis retour accueil. */
    public function logout(): void
    {
        $_SESSION = [];

        // On supprime aussi le cookie de session côté navigateur.
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();
        redirect('/');
    }
}
