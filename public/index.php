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
require_once __DIR__ . '/../app/Models/Service.php';             // catalogue (formulaire)
require_once __DIR__ . '/../app/Models/Order.php';               // commandes
require_once __DIR__ . '/../app/Models/Employee.php';            // employés (affectation)
require_once __DIR__ . '/../app/Models/Department.php';          // départements (création employé)
require_once __DIR__ . '/../app/Models/Project.php';             // projets (espace employé)
require_once __DIR__ . '/../app/Controllers/AuthController.php'; // (dépend de User)
require_once __DIR__ . '/../app/Middleware/Auth.php';            // gardes RBAC (require_login / require_role)
require_once __DIR__ . '/../app/Controllers/ClientController.php';
require_once __DIR__ . '/../app/Controllers/EmployeeController.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';

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

// Tableaux de bord par rôle. Le contrôle d'accès (RBAC) est fait
// dans chaque dashboard() via require_role().
$router->add('client',  [new ClientController(),   'dashboard']);
$router->add('employe', [new EmployeeController(), 'dashboard']);
$router->add('admin',   [new AdminController(),    'dashboard']);

// Nouvelle demande du client : GET = formulaire, POST = création.
$router->add('client/nouvelle-demande', fn() => $_SERVER['REQUEST_METHOD'] === 'POST'
    ? (new ClientController())->createOrder()
    : (new ClientController())->showNewOrder());

// Revue des demandes par l'admin. Les 3 mutations exigent un POST + jeton CSRF
// (vérifié dans le contrôleur) ; la liste est en GET.
$router->add('admin/commandes',           [new AdminController(), 'orders']);
$router->add('admin/commandes/approuver',  [new AdminController(), 'approveOrder']);
$router->add('admin/commandes/refuser',    [new AdminController(), 'rejectOrder']);
$router->add('admin/commandes/affecter',   [new AdminController(), 'assignOrder']);

// Gestion de l'équipe : GET = formulaire + liste, POST = création (CSRF vérifié).
$router->add('admin/employes', fn() => $_SERVER['REQUEST_METHOD'] === 'POST'
    ? (new AdminController())->createEmployee()
    : (new AdminController())->employees());

// Espace employé : liste des tâches (GET) + 2 mutations POST (CSRF vérifié).
$router->add('employe/taches',              [new EmployeeController(), 'tasks']);
$router->add('employe/taches/progression',  [new EmployeeController(), 'updateProgress']);
$router->add('employe/taches/livrer',       [new EmployeeController(), 'uploadFile']);

// 3. Dispatch : le paramètre ?url= est posé par public/.htaccess
//    (chaîne vide si on arrive directement sur la racine).
$router->dispatch((string)($_GET['url'] ?? ''));
