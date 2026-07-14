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

    /**
     * Vrai si la migration du profil employé a été appliquée (colonne `photo`
     * présente). Le résultat est mémorisé pour ne pas interroger la base à
     * chaque appel. Permet une dégradation propre avant la migration.
     */
    public function hasProfileColumns(): bool
    {
        static $has = null;
        if ($has === null) {
            $stmt = $this->db->query("SHOW COLUMNS FROM employees LIKE 'photo'");
            $has  = ($stmt->fetch() !== false);
        }
        return $has;
    }

    /**
     * Profil complet d'un employé à partir de son user_id (compte connecté).
     * Renvoie null si l'utilisateur n'a pas de fiche employé.
     * Les colonnes de profil ne sont lues que si la migration existe ; sinon
     * elles valent null (l'appelant retombe sur les initiales / masque les champs).
     */
    public function profileForUser(int $userId): ?array
    {
        // Colonnes de profil : réelles si migrées, sinon NULL (dégradation propre).
        $extra = $this->hasProfileColumns()
            ? "e.photo, e.experience_years, e.bio,"
            : "NULL AS photo, NULL AS experience_years, NULL AS bio,";

        $sql = "SELECT e.id, e.user_id, e.specialty, $extra
                       u.full_name, u.email,
                       d.name AS department_name
                FROM employees e
                JOIN users u        ON u.id = e.user_id
                JOIN departments d  ON d.id = e.department_id
                WHERE e.user_id = :uid
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Met à jour le profil de l'employé (expérience, bio, et photo si fournie).
     * Ne fait rien et renvoie false tant que la migration n'est pas appliquée.
     * $data : experience_years (?int), bio (?string), photo (?string, optionnel :
     * si la clé est absente, la photo existante est conservée).
     */
    public function updateProfile(int $employeeId, array $data): bool
    {
        if (!$this->hasProfileColumns()) {
            return false;
        }
        $sets   = ['experience_years = :exp', 'bio = :bio'];
        $params = [
            ':exp' => $data['experience_years'] ?? null,
            ':bio' => $data['bio'] ?? null,
            ':id'  => $employeeId,
        ];
        if (array_key_exists('photo', $data)) {   // nouvelle photo déposée
            $sets[]           = 'photo = :photo';
            $params[':photo'] = $data['photo'];
        }
        $sql  = "UPDATE employees SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return true;
    }
}
