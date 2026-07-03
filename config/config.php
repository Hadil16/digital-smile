<?php
/**
 * config/config.php
 * -----------------------------------------------------------------
 * Fichier central de configuration.
 * C'est le SEUL endroit où on met les réglages sensibles.
 * Il est situé HORS du dossier public/ : le navigateur ne peut
 * jamais y accéder directement. C'est un choix de sécurité.
 * -----------------------------------------------------------------
 */

// --- Base de données (valeurs par défaut de XAMPP) -----------------
// Sous XAMPP, l'utilisateur MySQL est 'root' SANS mot de passe.
define('DB_HOST', 'localhost');
define('DB_NAME', 'digital_smile');
define('DB_USER', 'root');
define('DB_PASS', '');            // vide par défaut sous XAMPP
define('DB_CHARSET', 'utf8mb4');

// --- Application ---------------------------------------------------
define('APP_NAME', 'Digital Smile');

// URL de base. Si vous placez le projet dans htdocs/digital-smile,
// alors l'URL publique est http://localhost/digital-smile/public
define('BASE_URL', '/digital-smile/public');

// Chemin absolu vers la racine du projet (utile pour inclure des fichiers).
define('ROOT_PATH', dirname(__DIR__));

// Dossier où sont stockés les fichiers uploadés par les clients/employés.
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads');

// Langues supportées. La 1re est la langue par défaut.
define('LANGUAGES', ['fr', 'ar', 'en']);
define('DEFAULT_LANG', 'fr');

// --- Environnement -------------------------------------------------
// 'dev' = on affiche les erreurs (utile pendant le développement).
// 'prod' = on cache les erreurs (obligatoire en production réelle).
define('APP_ENV', 'dev');

if (APP_ENV === 'dev') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
