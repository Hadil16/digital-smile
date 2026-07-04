<?php
/**
 * app/Core/helpers.php
 * -----------------------------------------------------------------
 * Fonctions d'aide globales : échappement, CSRF, redirection.
 * -----------------------------------------------------------------
 */

// Échappe une valeur pour l'affichage HTML (anti-XSS).
function e(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

// Renvoie le jeton CSRF de la session (créé une seule fois par session).
function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

// Renvoie le champ caché CSRF à insérer dans chaque formulaire POST.
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

// Vérifie le jeton CSRF reçu en POST (comparaison en temps constant).
function csrf_verify(): bool
{
    return hash_equals(csrf_token(), (string) ($_POST['csrf'] ?? ''));
}

// Redirige vers un chemin du site puis arrête le script.
function redirect(string $path): never
{
    header('Location: ' . BASE_URL . $path);
    exit;
}
