<?php
/**
 * app/Models/Client.php
 * -----------------------------------------------------------------
 * Clients de l'agence (fiche liée à un compte user de rôle 'client').
 * Lecture seule ici : sert aux statistiques du tableau de bord admin.
 * -----------------------------------------------------------------
 */

class Client extends Model
{
    protected string $table = 'clients';

    /** Nombre total de clients. */
    public function countTotal(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM clients")->fetchColumn();
    }

    /**
     * Liste des clients avec leurs statistiques de commandes (pour la page
     * d'administration). Pour chaque client : nom, email, téléphone (si saisi),
     * nombre total de commandes, commandes terminées, commandes en cours, et
     * date de la première commande (client « depuis »).
     *
     * LEFT JOIN orders : les clients sans commande apparaissent avec des 0.
     * GROUP BY sur la fiche client. Ignore les comptes soft-supprimés.
     */
    public function allWithStats(): array
    {
        $sql = "SELECT u.full_name AS name, u.email, u.phone,
                       COUNT(o.id) AS total_orders,
                       SUM(CASE WHEN o.status = 'completed'   THEN 1 ELSE 0 END) AS completed_orders,
                       SUM(CASE WHEN o.status = 'in_progress' THEN 1 ELSE 0 END) AS active_orders,
                       MIN(o.created_at) AS first_order_at
                FROM clients c
                JOIN users  u ON u.id = c.user_id
                LEFT JOIN orders o ON o.client_id = c.id
                WHERE u.deleted_at IS NULL
                GROUP BY c.id, u.full_name, u.email, u.phone
                ORDER BY u.full_name ASC";
        return $this->db->query($sql)->fetchAll();
    }
}
