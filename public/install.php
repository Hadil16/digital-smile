<?php
/**
 * public/install.php
 * -----------------------------------------------------------------
 * Script d'installation à lancer UNE SEULE FOIS.
 * Affiche un formulaire pour créer le compte administrateur :
 * le mot de passe est SAISI par vous — il n'est plus jamais écrit
 * dans le code ni dans Git (règle AI_RULES.md §4.3 et §4.7).
 *
 * Sécurité :
 *  - jeton CSRF de session vérifié à chaque envoi (règle §4.5) ;
 *  - idempotent : refuse de créer un 2e administrateur ;
 *  - mot de passe haché avec password_hash(), jamais affiché ;
 *  - toute sortie échappée avec htmlspecialchars().
 *
 * >>> APRÈS UTILISATION, SUPPRIMEZ CE FICHIER. <<<
 * -----------------------------------------------------------------
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

session_start();

// Jeton CSRF : généré une fois par session, exigé sur chaque POST.
if (empty($_SESSION['csrf_install'])) {
    $_SESSION['csrf_install'] = bin2hex(random_bytes(32));
}

$errors  = [];      // Messages d'erreur à afficher.
$done    = false;   // Vrai quand le compte vient d'être créé.
$already = false;   // Vrai si un admin existe déjà.

// Valeurs re-remplies dans le formulaire en cas d'erreur
// (jamais le mot de passe : on ne renvoie JAMAIS un mot de passe au navigateur).
$old = ['full_name' => '', 'email' => '', 'phone' => ''];

try {
    $db = Database::getConnection();

    // Idempotent : s'il existe déjà un utilisateur avec le rôle admin
    // (role_id = 1), il n'y a rien à installer.
    $already = (bool) $db->query("SELECT id FROM users WHERE role_id = 1 LIMIT 1")->fetch();

    if (!$already && $_SERVER['REQUEST_METHOD'] === 'POST') {

        // 1. Vérification du jeton CSRF (comparaison en temps constant).
        $token = (string) ($_POST['csrf'] ?? '');
        if (!hash_equals($_SESSION['csrf_install'], $token)) {
            $errors[] = 'Session expirée : rechargez la page puis réessayez.';
        }

        // 2. Lecture + validation des champs.
        $old['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
        $old['email']     = trim((string) ($_POST['email'] ?? ''));
        $old['phone']     = trim((string) ($_POST['phone'] ?? ''));
        $password         = (string) ($_POST['password'] ?? '');
        $password2        = (string) ($_POST['password2'] ?? '');

        if ($old['full_name'] === '') {
            $errors[] = 'Le nom complet est obligatoire.';
        }
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        }
        if (strlen($password) < 10) {
            $errors[] = 'Mot de passe trop court : 10 caractères minimum.';
        }
        if ($password !== $password2) {
            $errors[] = 'Les deux mots de passe ne correspondent pas.';
        }

        // 3. Création du compte (uniquement si tout est valide).
        if (!$errors) {
            $stmt = $db->prepare(
                "INSERT INTO users (role_id, full_name, email, password_hash, phone, lang)
                 VALUES (1, :name, :email, :hash, :phone, 'fr')"
            );
            $stmt->execute([
                ':name'  => $old['full_name'],
                ':email' => $old['email'],
                ':hash'  => password_hash($password, PASSWORD_DEFAULT),
                ':phone' => ($old['phone'] !== '' ? $old['phone'] : null),
            ]);
            $done = true;

            // Le jeton a servi : on en régénère un autre.
            $_SESSION['csrf_install'] = bin2hex(random_bytes(32));
        }
    }
} catch (Throwable $e) {
    $errors[] = (APP_ENV === 'dev')
        ? $e->getMessage() . ' — Vérifiez que database/schema.sql a bien été importé dans phpMyAdmin.'
        : 'Erreur pendant l\'installation. Réessayez ou contactez le développeur.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Installation — Digital Smile</title>
    <meta name="robots" content="noindex">
    <style>
        body { font-family: system-ui, sans-serif; background:#f4f4f7; color:#222;
               display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
        .card { background:#fff; padding:2rem 2.5rem; border-radius:16px;
                box-shadow:0 8px 30px rgba(0,0,0,.08); max-width:520px; width:90%; }
        h1 { color:#4A3F9E; margin-top:0; font-size:1.4rem; }
        label { display:block; font-weight:600; font-size:.9rem; margin:1rem 0 .3rem; }
        input { width:100%; box-sizing:border-box; padding:.6rem .8rem; font-size:1rem;
                border:1px solid #ccc; border-radius:8px; }
        button { margin-top:1.4rem; width:100%; padding:.8rem; font-size:1rem; font-weight:600;
                 color:#fff; background:#4A3F9E; border:0; border-radius:999px; cursor:pointer; }
        button:hover { background:#8BC63F; }
        .ok    { color:#3B6D11; }
        .error { background:#fdecec; color:#A32D2D; border-radius:8px;
                 padding:.8rem 1rem; margin:.4rem 0; font-size:.92rem; }
        .foot  { margin-top:1.2rem; font-size:.85rem; color:#666; }
        .warn  { color:#A32D2D; font-weight:600; }
    </style>
</head>
<body>
    <div class="card">

        <?php if ($already): ?>
            <!-- Cas 1 : un admin existe déjà, rien à faire. -->
            <h1>ℹ️ Installation déjà effectuée</h1>
            <p>Un compte administrateur existe déjà. Rien à faire ici.</p>
            <p class="warn">⚠️ Supprimez maintenant le fichier <code>public/install.php</code>.</p>

        <?php elseif ($done): ?>
            <!-- Cas 2 : le compte vient d'être créé (le mot de passe n'est PAS réaffiché). -->
            <h1 class="ok">✅ Compte administrateur créé</h1>
            <p>Email : <strong><?= htmlspecialchars($old['email']) ?></strong></p>
            <p>Conservez votre mot de passe en lieu sûr : il n'est enregistré
               qu'en version hachée, personne ne peut le relire.</p>
            <p class="warn">⚠️ Supprimez maintenant le fichier <code>public/install.php</code>
               (et <code>health.php</code> avant toute mise en ligne).</p>

        <?php else: ?>
            <!-- Cas 3 : formulaire de création. -->
            <h1>🔧 Installation — compte administrateur</h1>

            <?php foreach ($errors as $err): ?>
                <p class="error">⚠️ <?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>

            <form method="post" autocomplete="off">
                <!-- Jeton CSRF : prouve que l'envoi vient bien de cette page. -->
                <input type="hidden" name="csrf"
                       value="<?= htmlspecialchars($_SESSION['csrf_install']) ?>">

                <label for="full_name">Nom complet</label>
                <input id="full_name" name="full_name" type="text" required
                       value="<?= htmlspecialchars($old['full_name']) ?>">

                <label for="email">Email</label>
                <input id="email" name="email" type="email" required
                       value="<?= htmlspecialchars($old['email']) ?>">

                <label for="phone">Téléphone (optionnel)</label>
                <input id="phone" name="phone" type="tel"
                       value="<?= htmlspecialchars($old['phone']) ?>">

                <label for="password">Mot de passe (10 caractères minimum)</label>
                <input id="password" name="password" type="password"
                       required minlength="10" autocomplete="new-password">

                <label for="password2">Confirmez le mot de passe</label>
                <input id="password2" name="password2" type="password"
                       required minlength="10" autocomplete="new-password">

                <button type="submit">Créer le compte administrateur</button>
            </form>

            <p class="foot">Ce script ne peut créer qu'<strong>un seul</strong> compte
               administrateur. Après usage, supprimez <code>public/install.php</code>.</p>
        <?php endif; ?>

    </div>
</body>
</html>
