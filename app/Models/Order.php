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

    /**
     * Commandes ACCEPTÉES ('approved') mais pas encore affectées (aucun projet).
     * Le LEFT JOIN + p.id IS NULL isole celles qui attendent une affectation.
     * Les plus récentes d'abord. Jointures : client (nom) et service (nom).
     */
    public function allApprovedUnassigned(): array
    {
        $sql = "SELECT o.id, o.code, u.full_name AS client_name, s.name AS service_name,
                       o.project_name, o.budget, o.deadline, o.created_at
                FROM orders o
                JOIN clients  c ON c.id = o.client_id
                JOIN users    u ON u.id = c.user_id
                JOIN services s ON s.id = o.service_id
                LEFT JOIN projects p ON p.order_id = o.id
                WHERE o.status = 'approved' AND p.id IS NULL
                ORDER BY o.created_at DESC, o.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Commandes TERMINÉES ('completed') FACTURABLES : pas encore facturées
     * (ni comme facture simple, ni comme ligne d'une facture groupée) ET
     * avec un montant strictement positif (une commande sans montant ne peut
     * pas être facturée). Renvoie aussi client_id (pour la facture groupée).
     * Dégrade proprement si la colonne `invoiced` / la table invoice_items
     * n'existent pas encore (migration non appliquée).
     */
    public function completedWithoutInvoice(): array
    {
        $hasInvoiced = $this->columnExists('orders', 'invoiced');
        $hasItems    = $this->tableExists('invoice_items');

        // Conditions cumulées : terminée, montant > 0, pas de facture liée.
        $conds = ["o.status = 'completed'", "o.budget IS NOT NULL", "o.budget > 0", "i.id IS NULL"];
        if ($hasInvoiced) {
            $conds[] = "o.invoiced = 0";
        }
        $itemsJoin = '';
        if ($hasItems) {
            $itemsJoin = "LEFT JOIN invoice_items ii ON ii.order_id = o.id";
            $conds[]   = "ii.id IS NULL";
        }

        $sql = "SELECT o.id, o.code, c.id AS client_id, u.full_name AS client_name,
                       s.name AS service_name, o.project_name, o.budget, o.created_at
                FROM orders o
                JOIN clients  c ON c.id = o.client_id
                JOIN users    u ON u.id = c.user_id
                JOIN services s ON s.id = o.service_id
                LEFT JOIN invoices i ON i.order_id = o.id
                $itemsJoin
                WHERE " . implode(' AND ', $conds) . "
                ORDER BY u.full_name ASC, o.created_at DESC, o.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /** Vrai si une colonne existe (mémorisé) — dégradation propre avant migration. */
    private function columnExists(string $table, string $column): bool
    {
        static $cache = [];
        $key = "$table.$column";
        if (!array_key_exists($key, $cache)) {
            // Table/colonne = littéraux du code (pas d'entrée utilisateur) : sûr.
            $stmt = $this->db->query("SHOW COLUMNS FROM `$table` LIKE " . $this->db->quote($column));
            $cache[$key] = ($stmt->fetch() !== false);
        }
        return $cache[$key];
    }

    /** Vrai si une table existe (mémorisé). */
    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (!array_key_exists($table, $cache)) {
            $stmt = $this->db->query("SHOW TABLES LIKE " . $this->db->quote($table));
            $cache[$table] = ($stmt->fetch() !== false);
        }
        return $cache[$table];
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
     * Nombre de commandes par statut (pour les cartes du tableau de bord).
     * Renvoie un tableau associatif avec TOUJOURS les 6 clés attendues
     * (à 0 si aucune commande dans ce statut). 'cancelled' est ignoré.
     */
    public function countByStatus(): array
    {
        $counts = [
            'pending' => 0, 'approved' => 0, 'in_progress' => 0,
            'delivered' => 0, 'completed' => 0, 'rejected' => 0,
        ];
        $rows = $this->db->query("SELECT status, COUNT(*) AS n FROM orders GROUP BY status")->fetchAll();
        foreach ($rows as $r) {
            if (array_key_exists($r['status'], $counts)) {
                $counts[$r['status']] = (int) $r['n'];
            }
        }
        return $counts;
    }

    /** Nombre total de commandes. */
    public function countTotal(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    }

    /**
     * Nombre de commandes par mois sur les N derniers mois (pour un graphique
     * en courbe). Renvoie ['labels' => [...], 'values' => [...]] alignés, avec
     * 0 pour un mois sans commande. Les mois vides restent visibles.
     */
    public function monthlyCounts(int $months = 6): array
    {
        $months = max(1, min(24, $months)); // borne raisonnable
        $fr = ['', 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin',
               'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

        // Liste des N derniers mois, du plus ancien au plus récent.
        $labels = [];
        $keys   = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $ts       = strtotime("first day of -$i month");
            $keys[]   = date('Y-m', $ts);
            $labels[] = $fr[(int) date('n', $ts)] . ' ' . date('Y', $ts);
        }

        // Comptage groupé par mois sur la période.
        $since = date('Y-m-01', strtotime('first day of -' . ($months - 1) . ' month'));
        $stmt  = $this->db->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS n
             FROM orders WHERE created_at >= :since GROUP BY ym"
        );
        $stmt->execute([':since' => $since]);
        $map = [];
        foreach ($stmt->fetchAll() as $r) {
            $map[$r['ym']] = (int) $r['n'];
        }

        $values = array_map(fn($k) => $map[$k] ?? 0, $keys);
        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Services les plus demandés (nom + nombre de commandes), pour un
     * graphique en barres. Renvoie ['labels' => [...], 'values' => [...]].
     */
    public function topServices(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        $stmt = $this->db->prepare(
            "SELECT s.name AS service_name, COUNT(*) AS n
             FROM orders o
             JOIN services s ON s.id = o.service_id
             GROUP BY s.id, s.name
             ORDER BY n DESC, s.name ASC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return [
            'labels' => array_column($rows, 'service_name'),
            'values' => array_map('intval', array_column($rows, 'n')),
        ];
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

    /**
     * Renvoie UNE commande (détails complets) par son numéro, mais SEULEMENT
     * si elle appartient à ce client (contrôle de propriété par clients.user_id).
     * null si introuvable OU pas à lui (on ne révèle pas la différence).
     */
    public function findForClient(string $number, int $clientUserId): ?array
    {
        $sql = "SELECT o.id, o.code, o.project_name, o.status, o.budget, o.deadline,
                       o.description, o.created_at, o.updated_at,
                       s.name AS service_name
                FROM orders o
                JOIN clients  c ON c.id = o.client_id
                JOIN services s ON s.id = o.service_id
                WHERE o.code = :code AND c.user_id = :uid
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $number, ':uid' => $clientUserId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Renvoie le fichier LIVRABLE lié à une commande (via son projet), ou null.
     * On remonte files → projects → orders et on ne garde que kind='deliverable'.
     */
    public function deliverableFor(int $orderId): ?array
    {
        $sql = "SELECT f.id, f.original_name, f.stored_path, f.size_bytes
                FROM files f
                JOIN projects p ON p.id = f.project_id
                WHERE p.order_id = :oid AND f.kind = 'deliverable'
                ORDER BY f.id DESC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':oid' => $orderId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Le client confirme la réception : passe la commande à 'completed'.
     * Propriété ET statut de départ ('delivered') vérifiés DANS la requête,
     * donc rien n'est modifié si ce n'est pas sa commande ou si elle n'est
     * pas encore livrée. Renvoie true si une ligne a bien été mise à jour.
     */
    public function markCompleted(int $orderId, int $clientUserId): bool
    {
        $sql = "UPDATE orders o
                JOIN clients c ON c.id = o.client_id
                SET o.status = 'completed'
                WHERE o.id = :oid AND c.user_id = :uid AND o.status = 'delivered'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':oid' => $orderId, ':uid' => $clientUserId]);
        return $stmt->rowCount() > 0;
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
