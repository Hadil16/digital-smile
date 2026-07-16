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

// --- Configuration LOCALE / PRODUCTION (prioritaire) ---------------
// En production (hébergeur), on NE modifie PAS ce fichier. On crée
// config/config.local.php (ignoré par Git) à partir du modèle
// config/config.local.php.example, et on y met les 4 identifiants MySQL
// de l'hébergeur + l'URL du domaine + APP_ENV='prod'. S'il existe, il
// définit les constantes AVANT nous ; sinon on retombe sur les valeurs
// XAMPP ci-dessous. (Une variable d'environnement du même nom est aussi
// prise en compte, pour les hébergeurs qui en proposent.)
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

// --- Base de données (valeurs par défaut = XAMPP local) ------------
// « defined() || define() » = on ne fixe la valeur QUE si config.local.php
// (ou une variable d'environnement) ne l'a pas déjà définie.
defined('DB_HOST')    || define('DB_HOST',    getenv('DB_HOST') ?: 'localhost');
defined('DB_NAME')    || define('DB_NAME',    getenv('DB_NAME') ?: 'digital_smile');
defined('DB_USER')    || define('DB_USER',    getenv('DB_USER') ?: 'root');
defined('DB_PASS')    || define('DB_PASS',    getenv('DB_PASS') ?: '');   // vide sous XAMPP
defined('DB_CHARSET') || define('DB_CHARSET', 'utf8mb4');

// --- Application ---------------------------------------------------
defined('APP_NAME') || define('APP_NAME', 'Digital Smile');

// URL de base (le dossier public/ est la racine web). En local XAMPP :
// /digital-smile/public. En production : défini dans config.local.php
// (ex. '' si le domaine pointe directement sur public/).
defined('BASE_URL') || define('BASE_URL', getenv('BASE_URL') ?: '/digital-smile/public');

// Chemin absolu vers la racine du projet (utile pour inclure des fichiers).
define('ROOT_PATH', dirname(__DIR__));

// Dossier où sont stockés les fichiers uploadés par les clients/employés.
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads');

// Langues supportées. La 1re est la langue par défaut.
define('LANGUAGES', ['fr', 'ar', 'en']);
define('DEFAULT_LANG', 'fr');

// --- Environnement -------------------------------------------------
// 'dev'  = on affiche les erreurs (utile pendant le développement).
// 'prod' = on cache les erreurs (obligatoire en production réelle).
// À passer sur 'prod' dans config.local.php lors du déploiement.
defined('APP_ENV') || define('APP_ENV', getenv('APP_ENV') ?: 'dev');

if (APP_ENV === 'dev') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
