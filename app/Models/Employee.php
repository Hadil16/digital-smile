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
}
