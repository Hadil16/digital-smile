<?php
/**
 * app/Views/client/invoices.php
 * -----------------------------------------------------------------
 * Mes factures (client) : liste de ses propres factures avec lien
 * d'impression / PDF. Variable : $invoices (Invoice::allForClient()).
 * PRÉSENTATION UNIQUEMENT — aucune requête SQL ici.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

$statusMeta = [
    'unpaid'  => ['Non payée', 'red'],
    'partial' => ['Partielle', 'amber'],
    'paid'    => ['Payée',     'green'],
];
$fmtMoney = fn($m) => number_format((float) $m, 2, ',', ' ') . ' DZD';
$fmtDate  = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';

// Coquille client commune (sidebar + entête).
$clientActive = 'factures';
$pageTitle    = 'Mes factures';
$pageSubtitle = 'Consultez et imprimez vos factures';
require ROOT_PATH . '/app/Views/partials/client-sidebar.php';
?>
        <?php if (empty($invoices)): ?>
            <p class="adm-empty">Aucune facture pour le moment.</p>
        <?php else: ?>
            <div class="adm-card">
                <div class="adm-table__scroll">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Commande</th>
                                <th class="adm-table__right">Montant TTC</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $inv): ?>
                                <?php [$lbl, $tone] = $statusMeta[$inv['status']] ?? [$inv['status'], 'muted']; ?>
                                <tr>
                                    <td class="adm-table__num"><?= e($inv['code']) ?></td>
                                    <td><?= e($inv['order_code']) ?></td>
                                    <td class="adm-table__right"><?= e($fmtMoney($inv['amount_ttc'])) ?></td>
                                    <td><span class="adm-pill adm-pill--<?= $tone ?>"><?= e($lbl) ?></span></td>
                                    <td><?= e($fmtDate($inv['issued_at'])) ?></td>
                                    <td>
                                        <a class="adm-btn adm-btn--ghost"
                                           href="<?= e(BASE_URL) ?>/client/facture/<?= e(rawurlencode($inv['code'])) ?>/imprimer"
                                           aria-label="Imprimer la facture <?= e($inv['code']) ?>">Imprimer / PDF</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="cli-total">
                                <td colspan="2" class="adm-table__right">Total facturé</td>
                                <td class="adm-table__right"><?= e($fmtMoney(array_sum(array_map(fn($i) => (float) $i['amount_ttc'], $invoices)))) ?></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<style>
    .cli-total td { border-top: 2px solid var(--color-border); font-weight: 700;
        color: var(--color-text); padding-top: 14px; }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
