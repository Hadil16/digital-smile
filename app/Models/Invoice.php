<?php
/**
 * app/Models/Invoice.php
 * -----------------------------------------------------------------
 * Factures. Une facture naît d'une commande TERMINÉE ('completed') et
 * reçoit un numéro lisible unique FAC-AAAA-NNNN.
 *
 * Montants : HT = budget de la commande ; TVA 19 % (taux standard en
 * Algérie) ; TTC = HT + TVA. Colonnes réelles : amount_ht / tax_rate /
 * amount_ttc (cf. schema).
 * -----------------------------------------------------------------
 */

class Invoice extends Model
{
    protected string $table = 'invoices';

    // Taux de TVA par défaut (Algérie). L'admin peut choisir 0 (« sans TVA »).
    private const TVA_RATE = 19.00;

    /**
     * Crée une facture à partir d'UNE commande, et renvoie son numéro.
     * Renvoie '' (rien créé) si la commande n'est pas 'completed', déjà
     * facturée, ou SANS montant positif (une commande sans budget ne peut
     * pas être facturée). $tvaRate : 19 (défaut) ou 0 (« sans TVA »).
     * Insère aussi une ligne dans invoice_items (si la table existe) pour
     * que la facture simple se lise comme une facture groupée. Le tout en
     * transaction. Dégrade proprement si les colonnes/table sont absentes.
     */
    public function createFromOrder(int $orderId, float $tvaRate = self::TVA_RATE): string
    {
        $tvaRate = max(0.0, min(100.0, $tvaRate));

        $this->db->beginTransaction();
        try {
            // Commande + libellé (service — projet) pour la ligne de facture.
            $stmt = $this->db->prepare(
                "SELECT o.status, o.budget, o.project_name, s.name AS service_name
                 FROM orders o JOIN services s ON s.id = o.service_id
                 WHERE o.id = :id LIMIT 1"
            );
            $stmt->execute([':id' => $orderId]);
            $order = $stmt->fetch();

            if ($order === false || $order['status'] !== 'completed' || $this->orderAlreadyInvoiced($orderId)) {
                $this->db->rollBack();
                return ''; // conditions non remplies : rien de créé
            }

            // Montant HT = budget de la commande. Sans montant positif : refus.
            $ht = round((float) ($order['budget'] ?? 0), 2);
            if ($ht <= 0) {
                $this->db->rollBack();
                return '';
            }
            $ttc  = round($ht + $ht * $tvaRate / 100, 2);
            $code = $this->nextCode();

            $invoiceId = $this->insertInvoice($code, $orderId, $ht, $tvaRate, $ttc);
            $this->addItem($invoiceId, $orderId, $order['service_name'] . ' — ' . $order['project_name'], $ht);
            $this->markInvoiced([$orderId]);

            $this->db->commit();
            return $code;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Crée UNE facture groupant PLUSIEURS commandes d'un même client.
     * Contrôles côté serveur pour CHAQUE commande (jamais confiance aux
     * cases cochées) : appartenance au client, statut 'completed', non déjà
     * facturée, et montant > 0. Si une seule échoue, tout est annulé.
     * $tvaRate : 19 ou 0. Renvoie ['ok'=>bool, 'code'=>string, 'error'=>?string].
     */
    public function createGrouped(int $clientId, array $orderIds, float $tvaRate): array
    {
        // La facturation groupée exige la table invoice_items (migration).
        if (!$this->hasTable('invoice_items')) {
            return ['ok' => false, 'code' => '', 'error' => 'Facturation groupée indisponible : migration base de données non appliquée.'];
        }

        $orderIds = array_values(array_unique(array_filter(array_map('intval', $orderIds), fn($id) => $id > 0)));
        if ($clientId <= 0 || $orderIds === []) {
            return ['ok' => false, 'code' => '', 'error' => 'Merci de choisir un client et au moins une commande.'];
        }
        $tvaRate = max(0.0, min(100.0, $tvaRate));

        $this->db->beginTransaction();
        try {
            $ph   = implode(',', array_fill(0, count($orderIds), '?'));
            $stmt = $this->db->prepare(
                "SELECT o.id, o.client_id, o.status, o.budget, o.project_name, s.name AS service_name
                 FROM orders o JOIN services s ON s.id = o.service_id
                 WHERE o.id IN ($ph)"
            );
            $stmt->execute($orderIds);
            $rows = $stmt->fetchAll();

            // Toutes les commandes demandées doivent exister.
            if (count($rows) !== count($orderIds)) {
                $this->db->rollBack();
                return ['ok' => false, 'code' => '', 'error' => 'Commande introuvable.'];
            }

            $totalHt = 0.0;
            $items   = [];
            foreach ($rows as $r) {
                if ((int) $r['client_id'] !== $clientId) {
                    $this->db->rollBack();
                    return ['ok' => false, 'code' => '', 'error' => 'Une commande sélectionnée n\'appartient pas à ce client.'];
                }
                if ($r['status'] !== 'completed') {
                    $this->db->rollBack();
                    return ['ok' => false, 'code' => '', 'error' => 'Une commande sélectionnée n\'est pas terminée.'];
                }
                if ($this->orderAlreadyInvoiced((int) $r['id'])) {
                    $this->db->rollBack();
                    return ['ok' => false, 'code' => '', 'error' => 'Une commande sélectionnée est déjà facturée.'];
                }
                $ht = round((float) ($r['budget'] ?? 0), 2);
                if ($ht <= 0) {
                    $this->db->rollBack();
                    return ['ok' => false, 'code' => '', 'error' => 'Une commande sélectionnée n\'a pas de montant à facturer.'];
                }
                $totalHt += $ht;
                $items[]  = ['order_id' => (int) $r['id'], 'label' => $r['service_name'] . ' — ' . $r['project_name'], 'ht' => $ht];
            }

            $totalHt = round($totalHt, 2);
            $ttc     = round($totalHt + $totalHt * $tvaRate / 100, 2);
            $code    = $this->nextCode();

            // order_id de la facture = commande "représentative" (la 1re) : la
            // colonne est NOT NULL et sert aux jointures client existantes ; la
            // liste complète vit dans invoice_items.
            $invoiceId = $this->insertInvoice($code, $items[0]['order_id'], $totalHt, $tvaRate, $ttc);
            foreach ($items as $it) {
                $this->addItem($invoiceId, $it['order_id'], $it['label'], $it['ht']);
            }
            $this->markInvoiced(array_column($items, 'order_id'));

            $this->db->commit();
            return ['ok' => true, 'code' => $code, 'error' => null];
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Toutes les factures émises (numéro, client, commande, TTC, date),
     * les plus récentes d'abord. Jointures commande → client → user.
     */
    public function allWithDetails(): array
    {
        $sql = "SELECT i.code, i.amount_ttc, i.status, i.issued_at,
                       o.code AS order_code, u.full_name AS client_name
                FROM invoices i
                JOIN orders  o ON o.id = i.order_id
                JOIN clients c ON c.id = o.client_id
                JOIN users   u ON u.id = c.user_id
                ORDER BY i.issued_at DESC, i.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /** Une facture complète par son numéro (société + client + commande + montants). */
    public function findByNumber(string $number): ?array
    {
        // Taux appliqué : tva_rate si la migration existe, sinon tax_rate (dégradation).
        $rateSel = $this->hasColumn('invoices', 'tva_rate')
            ? 'COALESCE(i.tva_rate, i.tax_rate)'
            : 'i.tax_rate';

        $sql = "SELECT i.code, i.amount_ht, i.tax_rate, $rateSel AS applied_rate,
                       i.amount_ttc, i.status, i.issued_at,
                       o.code AS order_code, o.project_name,
                       u.full_name AS client_name, u.email AS client_email,
                       c.company, c.address, c.city,
                       s.name AS service_name
                FROM invoices i
                JOIN orders   o ON o.id = i.order_id
                JOIN clients  c ON c.id = o.client_id
                JOIN users    u ON u.id = c.user_id
                JOIN services s ON s.id = o.service_id
                WHERE i.code = :code
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $number]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Lignes d'une facture (une par commande) : n° commande, libellé, HT.
     * Si la table invoice_items est absente OU si la facture n'a pas de
     * ligne (anciennes factures), on retombe sur une ligne unique dérivée
     * de la commande liée + le montant HT stocké (jamais vide).
     */
    public function itemsForInvoice(string $code): array
    {
        if ($this->hasTable('invoice_items')) {
            $sql = "SELECT o.code AS order_code, ii.label, ii.amount_ht
                    FROM invoice_items ii
                    JOIN invoices i ON i.id = ii.invoice_id
                    JOIN orders   o ON o.id = ii.order_id
                    WHERE i.code = :code
                    ORDER BY ii.id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':code' => $code]);
            $rows = $stmt->fetchAll();
            if ($rows) {
                return $rows;
            }
        }
        // Repli : facture sans ligne -> une ligne à partir de la commande liée.
        $inv = $this->findByNumber($code);
        if ($inv === null) {
            return [];
        }
        return [[
            'order_code' => $inv['order_code'],
            'label'      => $inv['service_name'] . ' — ' . $inv['project_name'],
            'amount_ht'  => $inv['amount_ht'],
        ]];
    }

    /**
     * Somme des TTC des factures d'UN client (total facturé). Propriété
     * garantie DANS la requête via clients.user_id. 0.0 si aucune facture.
     */
    public function sumTtcForClient(int $clientUserId): float
    {
        $sql = "SELECT COALESCE(SUM(i.amount_ttc), 0)
                FROM invoices i
                JOIN orders  o ON o.id = i.order_id
                JOIN clients c ON c.id = o.client_id
                WHERE c.user_id = :uid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $clientUserId]);
        return (float) $stmt->fetchColumn();
    }

    /** Nombre total de factures (pour le tableau de bord). */
    public function countTotal(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
    }

    /**
     * Factures d'UN client (numéro, commande, TTC, statut, date), les plus
     * récentes d'abord. Propriété garantie DANS la requête via clients.user_id :
     * un client ne voit jamais que ses propres factures.
     */
    public function allForClient(int $clientUserId): array
    {
        $sql = "SELECT i.code, i.amount_ttc, i.status, i.issued_at,
                       o.code AS order_code
                FROM invoices i
                JOIN orders  o ON o.id = i.order_id
                JOIN clients c ON c.id = o.client_id
                WHERE c.user_id = :uid
                ORDER BY i.issued_at DESC, i.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $clientUserId]);
        return $stmt->fetchAll();
    }

    /** Numéro de la facture d'une commande (par order_id), ou null si aucune. */
    public function numberForOrder(int $orderId): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT code FROM invoices WHERE order_id = :oid ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([':oid' => $orderId]);
        $code = $stmt->fetchColumn();
        return $code !== false ? (string) $code : null;
    }

    /**
     * Insère la ligne `invoices` (avec tva_rate si la colonne existe) et
     * renvoie l'id créé. `tax_rate` reste synchronisé pour l'ancien code.
     */
    private function insertInvoice(string $code, int $orderId, float $ht, float $rate, float $ttc): int
    {
        $cols   = 'code, order_id, amount_ht, tax_rate, amount_ttc';
        $vals   = ':code, :oid, :ht, :rate, :ttc';
        $params = [':code' => $code, ':oid' => $orderId, ':ht' => $ht, ':rate' => $rate, ':ttc' => $ttc];
        if ($this->hasColumn('invoices', 'tva_rate')) {
            $cols .= ', tva_rate';
            $vals .= ', :tva';
            $params[':tva'] = $rate;
        }
        $this->db->prepare("INSERT INTO invoices ($cols) VALUES ($vals)")->execute($params);
        return (int) $this->db->lastInsertId();
    }

    /** Ajoute une ligne de facture (si la table existe ; sinon on ignore). */
    private function addItem(int $invoiceId, int $orderId, string $label, float $ht): void
    {
        if (!$this->hasTable('invoice_items')) {
            return;
        }
        $this->db->prepare(
            "INSERT INTO invoice_items (invoice_id, order_id, label, amount_ht)
             VALUES (:iid, :oid, :label, :ht)"
        )->execute([':iid' => $invoiceId, ':oid' => $orderId, ':label' => $label, ':ht' => $ht]);
    }

    /** Marque des commandes comme facturées (si la colonne existe). */
    private function markInvoiced(array $orderIds): void
    {
        if (!$this->hasColumn('orders', 'invoiced') || $orderIds === []) {
            return;
        }
        $stmt = $this->db->prepare("UPDATE orders SET invoiced = 1 WHERE id = :id");
        foreach ($orderIds as $id) {
            $stmt->execute([':id' => (int) $id]);
        }
    }

    /**
     * Vrai si la commande est déjà rattachée à une facture — que ce soit
     * comme commande représentative (invoices.order_id) ou comme ligne
     * d'une facture groupée (invoice_items.order_id).
     */
    private function orderAlreadyInvoiced(int $orderId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM invoices WHERE order_id = :oid LIMIT 1");
        $stmt->execute([':oid' => $orderId]);
        if ($stmt->fetchColumn() !== false) {
            return true;
        }
        if ($this->hasTable('invoice_items')) {
            $s2 = $this->db->prepare("SELECT 1 FROM invoice_items WHERE order_id = :oid LIMIT 1");
            $s2->execute([':oid' => $orderId]);
            if ($s2->fetchColumn() !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Vrai si une colonne existe (résultat mémorisé). Les noms de table/colonne
     * sont des littéraux du code (jamais d'entrée utilisateur) : pas d'injection.
     * Permet une dégradation propre tant que la migration n'est pas appliquée.
     */
    private function hasColumn(string $table, string $column): bool
    {
        static $cache = [];
        $key = "$table.$column";
        if (!array_key_exists($key, $cache)) {
            $stmt = $this->db->query("SHOW COLUMNS FROM `$table` LIKE " . $this->db->quote($column));
            $cache[$key] = ($stmt->fetch() !== false);
        }
        return $cache[$key];
    }

    /** Vrai si une table existe (résultat mémorisé). */
    private function hasTable(string $table): bool
    {
        static $cache = [];
        if (!array_key_exists($table, $cache)) {
            $stmt = $this->db->query("SHOW TABLES LIKE " . $this->db->quote($table));
            $cache[$table] = ($stmt->fetch() !== false);
        }
        return $cache[$table];
    }

    /**
     * Prochain numéro FAC-AAAA-NNNN (compteur par année). La contrainte
     * UNIQUE sur `code` garantit qu'aucun doublon ne persiste.
     */
    private function nextCode(): string
    {
        $prefix = 'FAC-' . date('Y') . '-';
        $stmt = $this->db->prepare(
            "SELECT COALESCE(MAX(CAST(SUBSTRING(code, :start) AS UNSIGNED)), 0)
             FROM invoices WHERE code LIKE :like"
        );
        $stmt->bindValue(':start', strlen($prefix) + 1, PDO::PARAM_INT);
        $stmt->bindValue(':like', $prefix . '%');
        $stmt->execute();
        $next = ((int) $stmt->fetchColumn()) + 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
