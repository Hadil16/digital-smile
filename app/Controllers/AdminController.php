<?php
/**
 * app/Controllers/AdminController.php
 * -----------------------------------------------------------------
 * Espace administrateur. Accès réservé au rôle 'admin' (garde RBAC).
 * -----------------------------------------------------------------
 */

class AdminController
{
    /** Tableau de bord de l'admin (vide pour l'instant — Phase 6). */
    public function dashboard(): void
    {
        require_role('admin');
        require ROOT_PATH . '/app/Views/admin/dashboard.php';
    }
}
