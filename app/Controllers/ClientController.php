<?php
/**
 * app/Controllers/ClientController.php
 * -----------------------------------------------------------------
 * Espace client. Accès réservé au rôle 'client' (garde RBAC).
 * -----------------------------------------------------------------
 */

class ClientController
{
    /** Tableau de bord du client (vide pour l'instant — Phase 6). */
    public function dashboard(): void
    {
        require_role('client');
        require ROOT_PATH . '/app/Views/client/dashboard.php';
    }
}
