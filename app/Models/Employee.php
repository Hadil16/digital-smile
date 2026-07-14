<?php
/**
 * app/Models/Employee.php
 * -----------------------------------------------------------------
 * Employés de l'agence. Lecture seule ici : alimente le menu
 * déroulant d'affectation d'une commande à un employé.
 * -----------------------------------------------------------------
 */

class Employee extends Model
{
    protected string $table = 'employees';

    /**
     * Renvoie les employés actifs (id de l'employé, nom complet).
     * NB : `employees` n'a pas de drapeau d'activité ; on le lit sur le
     * compte utilisateur associé (is_active = 1 et non supprimé).
     */
    public function allActive(): array
    {
        $stmt = $this->db->query(
            "SELECT e.id, u.full_name
             FROM employees e
             JOIN users u ON u.id = e.user_id
             WHERE u.is_active = 1 AND u.deleted_at IS NULL
             ORDER BY u.full_name ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Liste détaillée des employés (nom, email, département) pour la vue
     * d'ensemble de l'admin. Ignore les comptes soft-supprimés.
     */
    public function allWithDetails(): array
    {
        // active_projects : commandes de l'employé actuellement 'in_progress'
        // (via la table projects). LEFT JOIN pour garder les employés sans projet.
        $stmt = $this->db->query(
            "SELECT u.full_name, u.email, d.name AS department_name,
                    COUNT(DISTINCT CASE WHEN o.status = 'in_progress' THEN o.id END) AS active_projects
             FROM employees e
             JOIN users u        ON u.id = e.user_id
             JOIN departments d  ON d.id = e.department_id
             LEFT JOIN projects p ON p.employee_id = e.id
             LEFT JOIN orders o   ON o.id = p.order_id
             WHERE u.deleted_at IS NULL
             GROUP BY e.id, u.full_name, u.email, d.name
             ORDER BY u.full_name ASC"
        );
        return $stmt->fetchAll();
    }

    /** Nombre total d'employés. */
    public function countTotal(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM employees")->fetchColumn();
    }
}
