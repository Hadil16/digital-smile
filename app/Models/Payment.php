<?php
/**
 * app/Models/Payment.php
 * -----------------------------------------------------------------
 * Paiements d'une facture (table existante `payments` : invoice_id,
 * amount, method, paid_at). Enregistrer/supprimer un paiement recalcule
 * automatiquement invoices.status : payée / partielle / impayée.
 * Toutes les requêtes sont préparées. Aucune nouvelle colonne.
 * -----------------------------------------------------------------
 */

class Payment extends Model
{
    protected string $table = 'payments';

    // Modes de paiement autorisés (= enum de la table `payments`).
    private const METHODS = ['cash', 'transfer', 'cheque', 'other'];

    /**
     * Enregistre un paiement pour une facture, puis recalcule son statut.
     * Refuse un montant <= 0, un mode invalide, ou un montant qui dépasse le
     * reste à payer (message FR). Le tout dans UNE transaction.
     * Renvoie ['ok' => bool, 'error' => ?string].
     */
    public function record(int $invoiceId, float $amount, string $date, string $method): array
    {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            return ['ok' => false, 'error' => 'Le montant doit être supérieur à 0.'];
        }
        if (!in_array($method, self::METHODS, true)) {
            return ['ok' => false, 'error' => 'Mode de paiement invalide.'];
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d'); // défaut : aujourd'hui
        }

        $this->db->beginTransaction();
        try {
            // Facture + TTC (verrou implicite le temps de la transaction).
            $stmt = $this->db->prepare("SELECT amount_ttc FROM invoices WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $invoiceId]);
            $ttc = $stmt->fetchColumn();
            if ($ttc === false) {
                $this->db->rollBack();
                return ['ok' => false, 'error' => 'Facture introuvable.'];
            }
            $ttc       = round((float) $ttc, 2);
            $remaining = round($ttc - $this->paidFor($invoiceId), 2);

            // On refuse tout dépassement (petit epsilon pour les arrondis).
            if ($amount > $remaining + 0.001) {
                $this->db->rollBack();
                return ['ok' => false, 'error' => 'Le montant dépasse le reste à payer (' . number_format($remaining, 2, ',', ' ') . ' DZD).'];
            }

            $this->db->prepare(
                "INSERT INTO payments (invoice_id, amount, method, paid_at) VALUES (:iid, :amt, :m, :d)"
            )->execute([':iid' => $invoiceId, ':amt' => $amount, ':m' => $method, ':d' => $date]);

            $this->recomputeStatus($invoiceId, $ttc);
            $this->db->commit();
            return ['ok' => true, 'error' => null];
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Supprime un paiement (uniquement s'il appartient bien à cette facture)
     * puis recalcule le statut. Renvoie true si une ligne a été supprimée.
     */
    public function delete(int $paymentId, int $invoiceId): bool
    {
        $this->db->beginTransaction();
        try {
            $del = $this->db->prepare("DELETE FROM payments WHERE id = :pid AND invoice_id = :iid");
            $del->execute([':pid' => $paymentId, ':iid' => $invoiceId]);
            $removed = $del->rowCount() > 0;
            if ($removed) {
                $this->recomputeStatus($invoiceId);
            }
            $this->db->commit();
            return $removed;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Paiements d'une facture (les plus récents d'abord). */
    public function forInvoice(int $invoiceId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, amount, method, paid_at FROM payments WHERE invoice_id = :id ORDER BY paid_at DESC, id DESC"
        );
        $stmt->execute([':id' => $invoiceId]);
        return $stmt->fetchAll();
    }

    /** Total déjà encaissé pour une facture. */
    private function paidFor(int $invoiceId): float
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE invoice_id = :id");
        $stmt->execute([':id' => $invoiceId]);
        return round((float) $stmt->fetchColumn(), 2);
    }

    /**
     * Recalcule invoices.status d'après le total encaissé vs TTC :
     * encaissé >= TTC -> 'paid' ; 0 < encaissé < TTC -> 'partial' ; sinon 'unpaid'.
     */
    private function recomputeStatus(int $invoiceId, ?float $ttc = null): void
    {
        if ($ttc === null) {
            $s = $this->db->prepare("SELECT amount_ttc FROM invoices WHERE id = :id LIMIT 1");
            $s->execute([':id' => $invoiceId]);
            $ttc = round((float) $s->fetchColumn(), 2);
        }
        $paid   = $this->paidFor($invoiceId);
        $status = ($ttc > 0 && $paid >= $ttc) ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');
        $this->db->prepare("UPDATE invoices SET status = :st WHERE id = :id")
                 ->execute([':st' => $status, ':id' => $invoiceId]);
    }
}
