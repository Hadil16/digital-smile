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
require_once __DIR__ . '/../app/Controllers/HomeController.php';

// 2. Déclaration des routes.
//    Une ligne par page : chemin => action d'un controller.
$router = new Router();
$router->add('', [new HomeController(), 'index']); // Accueil

// 3. Dispatch : le paramètre ?url= est posé par public/.htaccess
//    (chaîne vide si on arrive directement sur la racine).
$router->dispatch((string)($_GET['url'] ?? ''));
