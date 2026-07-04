<?php
/**
 * app/Core/Router.php
 * -----------------------------------------------------------------
 * Le Routeur : l'aiguilleur du front controller.
 *
 * Son rôle : faire correspondre l'URL demandée (ex. "" pour
 * l'accueil, "services", "contact"...) à une action — c'est-à-dire
 * une méthode d'un controller. Si aucune route ne correspond,
 * il affiche la page 404.
 *
 * Volontairement minimal (pas de paramètres dynamiques, pas
 * d'expressions régulières) : on l'enrichira uniquement quand une
 * phase du projet en aura vraiment besoin. Simplicité d'abord.
 * -----------------------------------------------------------------
 */

class Router
{
    /**
     * Les routes enregistrées.
     * Clé   = chemin sans slash de début/fin ('' pour l'accueil).
     * Valeur = action à exécuter (callable : fonction ou [objet, 'méthode']).
     *
     * @var array<string, callable>
     */
    private array $routes = [];

    /**
     * Enregistre une route.
     *
     * @param string   $path   Chemin de l'URL (ex. '' ou 'contact').
     * @param callable $action Action à exécuter quand l'URL correspond.
     */
    public function add(string $path, callable $action): void
    {
        $this->routes[trim($path, '/')] = $action;
    }

    /**
     * Cherche la route qui correspond à l'URL reçue et l'exécute.
     * Si aucune ne correspond : réponse 404 propre.
     *
     * @param string $url L'URL transmise par public/.htaccess (?url=...).
     */
    public function dispatch(string $url): void
    {
        $path = trim($url, '/');

        if (isset($this->routes[$path])) {
            // Route connue : on exécute son action et c'est terminé.
            ($this->routes[$path])();
            return;
        }

        // Aucune route trouvée : code HTTP 404 + page d'erreur du site.
        http_response_code(404);
        require ROOT_PATH . '/app/Views/errors/404.php';
    }
}
