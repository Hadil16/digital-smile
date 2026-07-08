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

    // Taux de TVA appliqué (Algérie).
    private const TVA_RATE = 19.00;

    /**
     * Crée une facture à partir d'une commande, et renvoie son numéro.
     * Ne fait rien (renvoie '') si la commande n'est pas 'completed' ou si
     * une facture existe déjà pour elle (pas de double facturation).
     * Le tout dans une transaction.
     */
    public function createFromOrder(int $orderId): string
    {
        $this->db->beginTransaction();
        try {
            // La commande doit exister et être terminée.
            $stmt = $this->db->prepare("SELECT status, budget FROM orders WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $orderId]);
            $order = $stmt->fetch();

            // Une facture existe-t-elle déjà pour cette commande ?
            $dup = $this->db->prepare("SELECT 1 FROM invoices WHERE order_id = :id LIMIT 1");
            $dup->execute([':id' => $orderId]);

            if ($order === false || $order['status'] !== 'completed' || $dup->fetchColumn() !== false) {
                $this->db->rollBack();
                return ''; // conditions non remplies : rien de créé
            }

            // Montants : HT (0 si pas de budget), TVA 19 %, TTC = HT + TVA.
            $ht  = round((float) ($order['budget'] ?? 0), 2);
            $ttc = round($ht + $ht * self::TVA_RATE / 100, 2);

            $code = $this->nextCode();

            $ins = $this->db->prepare(
                "INSERT INTO invoices (code, order_id, amount_ht, tax_rate, amount_ttc)
                 VALUES (:code, :oid, :ht, :rate, :ttc)"
            );
            $ins->execute([
                ':code' => $code, ':oid' => $orderId,
                ':ht'   => $ht,   ':rate' => self::TVA_RATE, ':ttc' => $ttc,
            ]);

            $this->db->commit();
            return $code;
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
        $sql = "SELECT i.code, i.amount_ht, i.tax_rate, i.amount_ttc, i.status, i.issued_at,
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

    /** Nombre total de factures (pour le tableau de bord). */
    public function countTotal(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
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
