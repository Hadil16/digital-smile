<?php
/**
 * app/Controllers/EmployeeController.php
 * -----------------------------------------------------------------
 * Espace employé. Accès réservé au rôle 'employee' (garde RBAC).
 * -----------------------------------------------------------------
 */

class EmployeeController
{
    /** Tableau de bord de l'employé (vide pour l'instant — Phase 6). */
    public function dashboard(): void
    {
        require_role('employee');
        require ROOT_PATH . '/app/Views/employee/dashboard.php';
    }
}
