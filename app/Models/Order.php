<?php
/**
 * app/Models/Order.php
 * -----------------------------------------------------------------
 * Commandes (les demandes des clients). Une commande naît au statut
 * 'pending' et reçoit un numéro lisible unique DS-AAAA-NNNN.
 * -----------------------------------------------------------------
 */

class Order extends Model
{
    protected string $table = 'orders';

    /**
     * Crée une commande pour un client (statut 'pending') et renvoie
     * son numéro DS-AAAA-NNNN. Tout se fait dans UNE transaction :
     * fiche client + génération du numéro + insertion.
     *
     * @param int   $clientUserId id de l'UTILISATEUR (rôle client)
     * @param array $data         service_id, project_name, description, budget, deadline
     */
    public function createForClient(int $clientUserId, array $data): string
    {
        $this->db->beginTransaction();
        try {
            // orders.client_id référence clients.id (et non users.id) :
            // on récupère la fiche client de l'utilisateur (créée si absente).
            $clientId = $this->clientIdForUser($clientUserId);

            // Numéro lisible unique, incrémental par année.
            $code = $this->nextCode();

            $stmt = $this->db->prepare(
                "INSERT INTO orders
                    (code, client_id, service_id, project_name, description, budget, deadline, status)
                 VALUES
                    (:code, :client_id, :service_id, :project_name, :description, :budget, :deadline, 'pending')"
            );
            $stmt->execute([
                ':code'         => $code,
                ':client_id'    => $clientId,
                ':service_id'   => (int) $data['service_id'],
                ':project_name' => $data['project_name'],
                ':description'  => $data['description'],
                ':budget'       => $data['budget'],   // null accepté
                ':deadline'     => $data['deadline'], // null accepté
            ]);

            $this->db->commit();
            return $code;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e; // on laisse remonter : le contrôleur/serveur gère
        }
    }

    /**
     * Liste les commandes d'un client (les plus récentes d'abord).
     * On relie orders → clients (par user_id) et → services (nom du service).
     * NB : `orders` n'a pas de colonne deleted_at, donc rien à filtrer ainsi.
     */
    public function allForClient(int $clientUserId): array
    {
        $sql = "SELECT o.code, s.name AS service_name, o.status,
                       o.budget, o.deadline, o.created_at
                FROM orders o
                JOIN clients  c ON c.id = o.client_id
                JOIN services s ON s.id = o.service_id
                WHERE c.user_id = :uid
                ORDER BY o.created_at DESC, o.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $clientUserId]);
        return $stmt->fetchAll();
    }

    /**
     * Toutes les commandes en attente (statut 'pending'), les plus récentes
     * d'abord. Jointures : client (nom) et service (nom).
     */
    public function allPending(): array
    {
        $sql = "SELECT o.id, o.code, u.full_name AS client_name, s.name AS service_name,
                       o.project_name, o.budget, o.deadline, o.created_at
                FROM orders o
                JOIN clients  c ON c.id = o.client_id
                JOIN users    u ON u.id = c.user_id
                JOIN services s ON s.id = o.service_id
                WHERE o.status = 'pending'
                ORDER BY o.created_at DESC, o.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /** Vue d'ensemble : toutes les commandes avec leur statut (pour l'admin). */
    public function allWithStatus(): array
    {
        $sql = "SELECT o.id, o.code, u.full_name AS client_name, s.name AS service_name,
                       o.status, o.budget, o.deadline, o.created_at
                FROM orders o
                JOIN clients  c ON c.id = o.client_id
                JOIN users    u ON u.id = c.user_id
                JOIN services s ON s.id = o.service_id
                ORDER BY o.created_at DESC, o.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Change le statut d'une commande. Liste blanche STRICTE : seuls
     * 'approved', 'rejected', 'in_progress' sont acceptés depuis l'admin.
     * Renvoie true si une ligne a bien été modifiée.
     */
    public function updateStatus(int $orderId, string $status): bool
    {
        $allowed = ['approved', 'rejected', 'in_progress'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $orderId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Affecte un employé à une commande : la commande passe 'in_progress'
     * et le projet correspondant est créé (ou mis à jour) avec l'employé.
     * NB : l'affectation vit dans la table `projects` (orders n'a pas de
     * colonne employé). order_id y est UNIQUE : 1 projet par commande.
     * Le tout dans une transaction.
     */
    public function assignEmployee(int $orderId, int $employeeId): bool
    {
        $this->db->beginTransaction();
        try {
            // La commande avance au statut 'in_progress'.
            $up = $this->db->prepare("UPDATE orders SET status = 'in_progress' WHERE id = :id");
            $up->execute([':id' => $orderId]);

            // Projet existant pour cette commande ? -> mise à jour, sinon création.
            $sel = $this->db->prepare("SELECT id FROM projects WHERE order_id = :oid LIMIT 1");
            $sel->execute([':oid' => $orderId]);

            if ($sel->fetchColumn() !== false) {
                $q = $this->db->prepare("UPDATE projects SET employee_id = :emp WHERE order_id = :oid");
                $q->execute([':emp' => $employeeId, ':oid' => $orderId]);
            } else {
                // 'assigned' = statut de départ d'un projet (cf. schema).
                $q = $this->db->prepare(
                    "INSERT INTO projects (order_id, employee_id, status) VALUES (:oid, :emp, 'assigned')"
                );
                $q->execute([':oid' => $orderId, ':emp' => $employeeId]);
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Renvoie l'id de la fiche client de l'utilisateur, en la créant si besoin. */
    private function clientIdForUser(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT id FROM clients WHERE user_id = :uid LIMIT 1");
        $stmt->execute([':uid' => $userId]);
        $id = $stmt->fetchColumn();
        if ($id !== false) {
            return (int) $id;
        }
        // Aucune fiche : on la crée (société/adresse à compléter plus tard).
        $ins = $this->db->prepare("INSERT INTO clients (user_id) VALUES (:uid)");
        $ins->execute([':uid' => $userId]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Calcule le prochain numéro DS-AAAA-NNNN (compteur par année).
     * La contrainte UNIQUE sur `code` garantit qu'aucun doublon ne persiste.
     */
    private function nextCode(): string
    {
        $prefix = 'DS-' . date('Y') . '-';
        // Plus grand numéro déjà attribué cette année (0 si aucun).
        $stmt = $this->db->prepare(
            "SELECT COALESCE(MAX(CAST(SUBSTRING(code, :start) AS UNSIGNED)), 0)
             FROM orders WHERE code LIKE :like"
        );
        $stmt->bindValue(':start', strlen($prefix) + 1, PDO::PARAM_INT); // position du 1er chiffre
        $stmt->bindValue(':like', $prefix . '%');
        $stmt->execute();
        $next = ((int) $stmt->fetchColumn()) + 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
