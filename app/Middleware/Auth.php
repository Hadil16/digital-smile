<?php
/**
 * app/Middleware/Auth.php
 * -----------------------------------------------------------------
 * Gardes d'accès (RBAC) appelées au tout début d'une action protégée.
 *   - require_login()     : impose une session ouverte.
 *   - require_role($role) : impose un rôle précis, sinon page 403.
 * La session est déjà démarrée par public/index.php.
 * -----------------------------------------------------------------
 */

// Exige un utilisateur connecté ; sinon on renvoie vers la connexion.
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect('/login');
    }
}

// Exige un rôle précis : connecté d'abord, puis contrôle du rôle.
function require_role(string $role): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== $role) {
        deny_access();
    }
}

// Affiche une page "Accès refusé" (403) autonome, puis arrête le script.
// Page auto-portée (couleurs de marque), même esprit que errors/404.php.
function deny_access(): never
{
    http_response_code(403);
    $home = e(BASE_URL) . '/';
    echo <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Accès refusé — Digital Smile</title>
<meta name="robots" content="noindex">
<style>
  :root { --violet:#4A3F9E; --lime:#8BC63F; --ink:#111; --grey:#666; }
  *{margin:0;box-sizing:border-box;}
  body{font-family:'Inter',system-ui,sans-serif;min-height:100vh;display:flex;
       align-items:center;justify-content:center;background:#fff;color:var(--ink);
       text-align:center;padding:24px;}
  .deny__code{font-family:'Poppins',system-ui,sans-serif;font-weight:800;
       font-size:clamp(90px,22vw,220px);line-height:1;color:var(--violet);letter-spacing:-.04em;}
  .deny__code span{color:var(--lime);}
  h1{font-size:clamp(20px,3vw,30px);margin:12px 0 8px;}
  p{color:var(--grey);margin-bottom:28px;}
  .deny__btn{display:inline-block;background:var(--violet);color:#fff;text-decoration:none;
       padding:14px 34px;border-radius:999px;font-weight:600;}
  .deny__btn:hover{background:var(--lime);}
</style>
</head>
<body>
<main>
  <p class="deny__code" aria-hidden="true">4<span>0</span>3</p>
  <h1>Accès refusé</h1>
  <p>Vous n'avez pas les droits nécessaires pour voir cette page.</p>
  <a class="deny__btn" href="{$home}">Retour à l'accueil</a>
</main>
</body>
</html>
HTML;
    exit;
}
