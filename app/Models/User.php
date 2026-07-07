<?php
/**
 * app/Models/User.php
 * -----------------------------------------------------------------
 * Modèle des utilisateurs (une seule table pour admin/employé/client,
 * le rôle décide des droits — principe RBAC).
 *
 * Toutes les requêtes sont préparées (héritées ou définies ici) : on
 * ne concatène JAMAIS une variable dans du SQL. Le rôle est toujours
 * résolu par son NOM via la table `roles`, jamais par un ID en dur.
 * -----------------------------------------------------------------
 */

class User extends Model
{
    // Table gérée par ce modèle (utilisée par les méthodes héritées de Model).
    protected string $table = 'users';

    /**
     * Cherche un utilisateur par son email et renvoie aussi le NOM de son
     * rôle (jointure `roles`), pratique pour la redirection RBAC après login.
     * Ignore les comptes soft-supprimés (deleted_at NULL). null si absent.
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT u.*, r.name AS role_name
                FROM users u
                JOIN roles r ON r.id = u.role_id
                WHERE u.email = :email AND u.deleted_at IS NULL
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    /** Vrai si un compte actif utilise déjà cet email. */
    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    /**
     * Renvoie l'id d'un rôle à partir de son nom ('admin'|'employee'|'client').
     * Évite d'écrire un id de rôle en dur dans le code (règle RBAC).
     * Renvoie 0 si le rôle n'existe pas (base non initialisée).
     */
    public function roleId(string $name): int
    {
        $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = :name LIMIT 1");
        $stmt->execute([':name' => $name]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Crée un compte CLIENT et renvoie son id.
     * Le mot de passe est haché en BCRYPT — jamais stocké en clair.
     * Réutilise Model::insert() (requête préparée, colonnes codées en dur).
     * N.B. : seule la ligne `users` est créée ; les détails `clients`
     * (société, adresse) seront renseignés plus tard, au formulaire projet.
     *
     * @param array $data ['name' => ..., 'email' => ..., 'password' => ...]
     */
    public function createClient(array $data): int
    {
        return $this->insert([
            'role_id'       => $this->roleId('client'),
            'full_name'     => $data['name'],
            'email'         => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);
    }

    /**
     * Crée un compte EMPLOYÉ et renvoie l'id du user créé.
     * Deux écritures dans UNE transaction : la ligne `users` (rôle employee,
     * mot de passe BCRYPT) puis la fiche `employees` liée + son département.
     *
     * @param array $data ['name', 'email', 'password', 'department_id']
     */
    public function createEmployee(array $data): int
    {
        $this->db->beginTransaction();
        try {
            // 1. Compte utilisateur (rôle employee).
            $u = $this->db->prepare(
                "INSERT INTO users (role_id, full_name, email, password_hash)
                 VALUES (:role_id, :full_name, :email, :hash)"
            );
            $u->execute([
                ':role_id'   => $this->roleId('employee'),
                ':full_name' => $data['name'],
                ':email'     => $data['email'],
                ':hash'      => password_hash($data['password'], PASSWORD_BCRYPT),
            ]);
            $userId = (int) $this->db->lastInsertId();

            // 2. Fiche employé, rattachée au département choisi.
            $e = $this->db->prepare(
                "INSERT INTO employees (user_id, department_id) VALUES (:uid, :dept)"
            );
            $e->execute([':uid' => $userId, ':dept' => (int) $data['department_id']]);

            $this->db->commit();
            return $userId;
        } catch (Throwable $ex) {
            $this->db->rollBack();
            throw $ex; // on laisse remonter
        }
    }
}
