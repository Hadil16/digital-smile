<?php
/**
 * public/index.php — LE FRONT CONTROLLER (point d'entrée unique).
 * -----------------------------------------------------------------
 * Toutes les requêtes qui ne visent pas un vrai fichier (image,
 * CSS, JS...) arrivent ici, grâce à la règle de réécriture de
 * public/.htaccess qui ajoute ?url=... à la requête.
 *
 * Déroulé, dans l'ordre :
 *   1. charger la configuration et les classes de base ;
 *   2. déclarer les routes connues du site ;
 *   3. laisser le Router exécuter la bonne action (ou la page 404).
 *
 * Chaque dépendance est chargée explicitement (pas d'autoloader
 * "magique") : on voit d'un coup d'œil tout ce que le site utilise.
 * -----------------------------------------------------------------
 */

// 1. Configuration + classes de base.
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/helpers.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Core/Model.php';                 // classe mère des modèles
require_once __DIR__ . '/../app/Controllers/HomeController.php';
require_once __DIR__ . '/../app/Models/User.php';                // (dépend de Model)
require_once __DIR__ . '/../app/Controllers/AuthController.php'; // (dépend de User)

// 1 bis. Session durcie : cookie HttpOnly + SameSite (+ Secure en HTTPS).
//        On la démarre AVANT toute écriture de session ou jeton CSRF.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

// 2. Déclaration des routes.
//    Une ligne par page : chemin => action d'un controller.
$router = new Router();
$router->add('', [new HomeController(), 'index']); // Accueil

// Authentification. Le Router ne distingue pas GET/POST : on aiguille
// selon la méthode HTTP (GET = afficher le formulaire, POST = traiter).
$auth = new AuthController(); // aucune connexion DB tant qu'aucune méthode modèle n'est appelée
$router->add('login',    fn() => $_SERVER['REQUEST_METHOD'] === 'POST' ? $auth->login()    : $auth->showLogin());
$router->add('register', fn() => $_SERVER['REQUEST_METHOD'] === 'POST' ? $auth->register() : $auth->showRegister());
$router->add('logout',   [$auth, 'logout']);

// 3. Dispatch : le paramètre ?url= est posé par public/.htaccess
//    (chaîne vide si on arrive directement sur la racine).
$router->dispatch((string)($_GET['url'] ?? ''));
