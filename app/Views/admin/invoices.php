<?php
/**
 * app/Views/admin/invoices.php
 * -----------------------------------------------------------------
 * Facturation (admin) : commandes terminées à facturer + factures
 * déjà émises. Variables : $toInvoice, $invoices, $flash.
 * PRÉSENTATION UNIQUEMENT — logique, formulaire et routes inchangés.
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

// Coquille admin commune (sidebar + entête).
$adminActive  = 'factures';
$pageTitle    = 'Facturation';
$pageSubtitle = 'Générez les factures et suivez les paiements';
require ROOT_PATH . '/app/Views/partials/admin-sidebar.php';
?>
        <?php if (!empty($flash)): ?>
            <p class="adm-flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <!-- ============ Commandes terminées à facturer ============ -->
        <h2 class="adm-section">Commandes à facturer</h2>

        <?php if (empty($toInvoice)): ?>
            <p class="adm-empty">Aucune commande à facturer.</p>
        <?php else: ?>
            <?php foreach ($toInvoice as $o): ?>
                <article class="adm-order">
                    <div class="adm-order__head">
                        <span class="adm-order__code"><?= e($o['code']) ?></span>
                        <span class="adm-pill adm-pill--green">Terminée</span>
                    </div>
                    <div class="adm-order__meta">
                        <div><span>Client</span><?= e($o['client_name']) ?></div>
                        <div><span>Service</span><?= e($o['service_name']) ?></div>
                        <div><span>Projet</span><?= e($o['project_name']) ?></div>
                        <div><span>Montant HT</span><?= e($fmtMoney($o['budget'] ?? 0)) ?></div>
                    </div>
                    <div class="adm-order__actions">
                        <form method="post" action="<?= e(BASE_URL) ?>/admin/factures/generer">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                            <button class="adm-btn adm-btn--primary" type="submit">Générer la facture</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ============ Factures émises ============ -->
        <h2 class="adm-section">Factures émises</h2>

        <?php if (empty($invoices)): ?>
            <p class="adm-empty">Aucune facture émise pour le moment.</p>
        <?php else: ?>
            <div class="adm-card">
                <div class="adm-table__scroll">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Client</th>
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
                                    <td><?= e($inv['client_name']) ?></td>
                                    <td><?= e($inv['order_code']) ?></td>
                                    <td class="adm-table__right"><?= e($fmtMoney($inv['amount_ttc'])) ?></td>
                                    <td><span class="adm-pill adm-pill--<?= $tone ?>"><?= e($lbl) ?></span></td>
                                    <td><?= e($fmtDate($inv['issued_at'])) ?></td>
                                    <td>
                                        <a class="adm-btn adm-btn--ghost"
                                           href="<?= e(BASE_URL) ?>/admin/factures/<?= e(rawurlencode($inv['code'])) ?>"
                                           aria-label="Voir la facture <?= e($inv['code']) ?>">Voir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
