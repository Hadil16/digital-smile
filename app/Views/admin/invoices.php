<?php
/**
 * app/Views/admin/invoices.php
 * -----------------------------------------------------------------
 * Facturation (admin) : commandes à facturer (facture simple avec TVA
 * optionnelle), facture groupée (plusieurs commandes d'un client → une
 * facture), et factures déjà émises. Variables : $toInvoice, $invoices, $flash.
 * PRÉSENTATION UNIQUEMENT — toute la logique/SQL est dans les modèles.
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
$pageSubtitle = 'Générez les factures (avec ou sans TVA) et suivez les paiements';
require ROOT_PATH . '/app/Views/partials/admin-sidebar.php';

// Cartes KPI (agrégation d'affichage sur les données déjà chargées).
$totalBilled = array_sum(array_map(fn($i) => (float) $i['amount_ttc'], $invoices));
$invKpis = [
    ['Factures émises', (string) count($invoices),  '🧾', 'violet', 'Total émis'],
    ['Total facturé',   $fmtMoney($totalBilled),    '💰', 'green',  'Montant TTC cumulé'],
    ['À facturer',      (string) count($toInvoice), '📦', 'amber',  'Commandes facturables'],
];

// Regroupement des commandes facturables par client (pour la facture groupée).
$byClient = [];
foreach ($toInvoice as $o) {
    $cid = (int) ($o['client_id'] ?? 0);
    $byClient[$cid]['name']     = $o['client_name'];
    $byClient[$cid]['orders'][] = $o;
}

// Petit bloc de choix TVA réutilisé par les deux formulaires.
$tvaChoice = function (string $idPrefix) {
    ?>
    <div class="inv-tva" role="radiogroup" aria-label="Choix de la TVA">
        <label><input type="radio" name="tva" value="avec" checked> Avec TVA (19 %)</label>
        <label><input type="radio" name="tva" value="sans"> Sans TVA</label>
    </div>
    <?php
};
?>
        <?php if (!empty($flash)): ?>
            <p class="adm-flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <!-- Cartes KPI (données réelles) -->
        <section class="adm__kpis" aria-label="Indicateurs facturation">
            <?php foreach ($invKpis as [$lbl, $val, $ico, $tone, $cap]): ?>
                <article class="adm-kpi adm-kpi--<?= $tone ?>">
                    <div class="adm-kpi__top">
                        <span class="adm-kpi__label"><?= e($lbl) ?></span>
                        <span class="adm-kpi__ico" aria-hidden="true"><?= $ico ?></span>
                    </div>
                    <p class="adm-kpi__num adm-kpi__num--sm"><?= e($val) ?></p>
                    <p class="adm-kpi__cap"><?= e($cap) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- ============ Commandes à facturer (facture simple) ============ -->
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
                            <?php $tvaChoice('s' . (int) $o['id']); ?>
                            <button class="adm-btn adm-btn--primary" type="submit">Générer la facture</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ============ Facture groupée (plusieurs commandes d'un client) ============ -->
        <?php if (!empty($byClient)): ?>
            <h2 class="adm-section">Facture groupée</h2>
            <p class="inv-hint">Regroupez plusieurs commandes terminées d'un même client en une seule facture.</p>

            <?php foreach ($byClient as $cid => $grp): ?>
                <article class="adm-order">
                    <div class="adm-order__head">
                        <span class="adm-order__code"><?= e($grp['name']) ?></span>
                        <span class="adm-pill adm-pill--violet"><?= count($grp['orders']) ?> commande<?= count($grp['orders']) > 1 ? 's' : '' ?></span>
                    </div>
                    <form method="post" action="<?= e(BASE_URL) ?>/admin/factures/groupee">
                        <?= csrf_field() ?>
                        <input type="hidden" name="client_id" value="<?= (int) $cid ?>">
                        <div class="inv-picks">
                            <?php foreach ($grp['orders'] as $o): ?>
                                <label class="inv-pick">
                                    <input type="checkbox" name="order_ids[]" value="<?= (int) $o['id'] ?>">
                                    <span class="inv-pick__code"><?= e($o['code']) ?></span>
                                    <span class="inv-pick__svc"><?= e($o['service_name']) ?> — <?= e($o['project_name']) ?></span>
                                    <span class="inv-pick__ht"><?= e($fmtMoney($o['budget'] ?? 0)) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="inv-group__foot">
                            <?php $tvaChoice('g' . (int) $cid); ?>
                            <button class="adm-btn adm-btn--assign" type="submit">Générer la facture groupée</button>
                        </div>
                    </form>
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

<style>
    /* Choix TVA + sélection des commandes (auto-portés, tokens clair/sombre). */
    .inv-hint { color: var(--color-muted); font-size: 13px; margin: 0 0 14px; }
    .inv-tva { display: flex; flex-wrap: wrap; gap: 14px; margin: 0 0 14px; }
    .inv-tva label { display: inline-flex; align-items: center; gap: 6px; font-size: 13px;
        font-weight: 600; color: var(--color-text); cursor: pointer; }
    .inv-tva input { accent-color: var(--color-primary); }

    .inv-picks { display: flex; flex-direction: column; gap: 8px; margin: 4px 0 16px; }
    .inv-pick { display: grid; grid-template-columns: auto auto 1fr auto; align-items: center; gap: 12px;
        padding: 10px 12px; border: 1px solid var(--color-border); border-radius: 12px; cursor: pointer;
        transition: border-color var(--transition), background var(--transition); }
    .inv-pick:hover { border-color: var(--color-primary-light); background: var(--color-surface-alt); }
    .inv-pick input { accent-color: var(--color-accent); }
    .inv-pick__code { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 700; color: var(--color-primary-light); }
    .inv-pick__svc { color: var(--color-text); font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .inv-pick__ht { font-weight: 600; color: var(--color-accent-dark); white-space: nowrap; }
    .inv-group__foot { display: flex; flex-wrap: wrap; align-items: center; gap: 16px;
        border-top: 1px solid var(--color-border); padding-top: 14px; }
    .inv-group__foot .inv-tva { margin: 0; }
    @media (max-width: 620px) {
        .inv-pick { grid-template-columns: auto 1fr; }
        .inv-pick__ht { grid-column: 2; }
    }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
