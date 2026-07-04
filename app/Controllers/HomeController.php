<?php
/**
 * app/Controllers/HomeController.php
 * -----------------------------------------------------------------
 * Controller de la page d'accueil.
 *
 * L'accueil est rendu par la vue app/Views/public/home.php
 * (design "Huge x Digital Smile" validé), qui assemble les
 * partials communs header.php + footer.php.
 * -----------------------------------------------------------------
 */

class HomeController
{
    /**
     * Affiche la page d'accueil.
     */
    public function index(): void
    {
        require ROOT_PATH . '/app/Views/public/home.php';
    }
}
