<?php
/**
 * app/Models/Department.php
 * -----------------------------------------------------------------
 * Départements de l'agence. Lecture seule ici : alimente le menu
 * déroulant du formulaire de création d'employé.
 * -----------------------------------------------------------------
 */

class Department extends Model
{
    protected string $table = 'departments';

    /**
     * Renvoie les départements (id, name), triés par nom.
     * NB : `departments` n'a ni deleted_at ni is_active : tous sont actifs.
     */
    public function allActive(): array
    {
        $stmt = $this->db->query("SELECT id, name FROM departments ORDER BY name ASC");
        return $stmt->fetchAll();
    }
}
