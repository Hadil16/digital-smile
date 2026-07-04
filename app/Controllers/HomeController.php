<?php
/**
 * app/Controllers/HomeController.php
 * -----------------------------------------------------------------
 * Controller de la page d'accueil.
 *
 * Pour l'instant, l'accueil est le fichier statique
 * public/index.html (design "Huge x Digital Smile" validé).
 * Ce controller se contente donc de le servir tel quel :
 * UNE seule source de vérité, aucun doublon de contenu.
 *
 * Sa conversion en vraie vue PHP (layout + partials nav/footer)
 * est prévue à l'item B3 de la roadmap (docs/ROADMAP.md).
 * -----------------------------------------------------------------
 */

class HomeController
{
    /**
     * Affiche la page d'accueil.
     */
    public function index(): void
    {
        readfile(ROOT_PATH . '/public/index.html');
    }
}
