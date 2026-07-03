<?php
/**
 * app/Core/Model.php
 * -----------------------------------------------------------------
 * Classe MÈRE de tous les modèles (Client, Order, Service...).
 * Elle fournit les opérations de base (find, all, insert...) déjà
 * sécurisées via requêtes préparées. Chaque modèle enfant définit
 * juste sa table, et hérite de toute cette logique. Principe DRY.
 * -----------------------------------------------------------------
 */

abstract class Model
{
    protected PDO $db;
    protected string $table = '';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Renvoie une ligne par son id, ou null si absente. */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Renvoie toutes les lignes de la table. */
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Insère une ligne à partir d'un tableau [colonne => valeur].
     * Renvoie l'id créé. Toujours en requête préparée = sûr.
     */
    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $fields  = implode(', ', $columns);
        $holders = ':' . implode(', :', $columns);

        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($holders)";
        $stmt = $this->db->prepare($sql);

        foreach ($data as $col => $val) {
            $stmt->bindValue(':' . $col, $val);
        }
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }
}
