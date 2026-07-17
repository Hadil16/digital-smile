<?php
/**
 * app/Views/admin/invoice-detail.php
 * -----------------------------------------------------------------
 * Détail d'une facture (mise en page type document). Variable : $invoice.
 * PRÉSENTATION UNIQUEMENT — le lien d'impression et les données sont inchangés.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

$statusMeta = [
    'unpaid'  => ['Impayée',   'muted'],
    'partial' => ['Partielle', 'amber'],
    'paid'    => ['Payée',     'green'],
];
$methodLabels = ['cash' => 'Espèces', 'transfer' => 'Virement', 'cheque' => 'Chèque', 'other' => 'Autre'];
$fmtMoney = fn($m) => number_format((float) $m, 2, ',', ' ') . ' DZD';
$fmtDate  = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
$tva = (float) $invoice['amount_ttc'] - (float) $invoice['amount_ht']; // montant de TVA
$payments  = $payments  ?? [];
$paid      = $paid      ?? 0.0;
$remaining = $remaining ?? 0.0;
// Taux réellement appliqué (tva_rate si migré, sinon tax_rate), sans zéros inutiles.
$rate = rtrim(rtrim(number_format((float) ($invoice['applied_rate'] ?? $invoice['tax_rate']), 2, ',', ' '), '0'), ',');
[$stLabel, $stTone] = $statusMeta[$invoice['status']] ?? [$invoice['status'], 'muted'];
$items = $items ?? []; // lignes (une par commande) fournies par le contrôleur

// Coquille admin commune (sidebar + entête).
$adminActive  = 'factures';
$pageTitle    = 'Facture ' . $invoice['code'];
$pageSubtitle = 'Détail de la facture';
require ROOT_PATH . '/app/Views/partials/admin-sidebar.php';
?>
        <div class="adm-doc__bar">
            <a class="adm-btn adm-btn--ghost" href="<?= e(BASE_URL) ?>/admin/factures">← Retour aux factures</a>
            <a class="adm-btn adm-btn--assign"
               href="<?= e(BASE_URL) ?>/admin/factures/<?= e(rawurlencode($invoice['code'])) ?>/imprimer">
                Télécharger / Imprimer
            </a>
        </div>

        <?php if (!empty($flash)): ?>
            <p class="adm-flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <article class="adm-doc">
            <div class="adm-doc__head">
                <div class="adm-doc__brand">Digital Smile
                    <small>Agence de branding &amp; communication — Bab Ezzouar, Alger</small>
                </div>
                <div class="adm-doc__num">
                    <strong><?= e($invoice['code']) ?></strong>
                    <span>Émise le <?= e($fmtDate($invoice['issued_at'])) ?></span><br>
                    <span class="adm-pill adm-pill--<?= $stTone ?>"><?= e($stLabel) ?></span>
                </div>
            </div>

            <div class="adm-doc__cols">
                <div>
                    <h3>Client</h3>
                    <p>
                        <?= e($invoice['client_name']) ?><br>
                        <?php if (!empty($invoice['company'])): ?><?= e($invoice['company']) ?><br><?php endif; ?>
                        <?php if (!empty($invoice['address'])): ?><?= e($invoice['address']) ?><br><?php endif; ?>
                        <?php if (!empty($invoice['city'])): ?><?= e($invoice['city']) ?><br><?php endif; ?>
                        <?= e($invoice['client_email']) ?>
                    </p>
                </div>
                <div>
                    <h3><?= count($items) > 1 ? 'Commandes' : 'Commande' ?></h3>
                    <p><?= count($items) ?> ligne<?= count($items) > 1 ? 's' : '' ?> — détail ci-dessous</p>
                </div>
            </div>

            <!-- Lignes : une par commande (facture simple = 1 ligne). -->
            <div class="adm-table__scroll">
                <table class="adm-table adm-doc__lines">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Service / Projet</th>
                            <th class="adm-table__right">Montant HT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td class="adm-table__num"><?= e($it['order_code']) ?></td>
                                <td><?= e($it['label']) ?></td>
                                <td class="adm-table__right"><?= e($fmtMoney($it['amount_ht'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="adm-amounts">
                <div><span>Montant HT</span><span><?= e($fmtMoney($invoice['amount_ht'])) ?></span></div>
                <div><span>TVA (<?= e($rate) ?> %)</span><span><?= e($fmtMoney($tva)) ?></span></div>
                <div class="adm-amounts__ttc"><span>Total TTC</span><span><?= e($fmtMoney($invoice['amount_ttc'])) ?></span></div>
            </div>
        </article>

        <!-- ============ Paiements ============ -->
        <section class="adm-card pay-card">
            <div class="pay-head">
                <h2 class="adm-card__title" style="margin:0">Paiements</h2>
                <span class="adm-pill adm-pill--<?= $stTone ?>"><?= e($stLabel) ?></span>
            </div>

            <div class="pay-summary">
                <div><span class="pay-summary__k">Payé</span><span class="pay-summary__v pay-summary__v--ok"><?= e($fmtMoney($paid)) ?></span></div>
                <div><span class="pay-summary__k">Reste à payer</span><span class="pay-summary__v"><?= e($fmtMoney($remaining)) ?></span></div>
                <div><span class="pay-summary__k">Total TTC</span><span class="pay-summary__v"><?= e($fmtMoney($invoice['amount_ttc'])) ?></span></div>
            </div>

            <?php if (empty($payments)): ?>
                <p class="adm__empty">Aucun paiement enregistré.</p>
            <?php else: ?>
                <div class="adm-table__scroll">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Méthode</th>
                                <th class="adm-table__right">Montant</th>
                                <th><span class="sr-only">Action</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td><?= e($fmtDate($p['paid_at'])) ?></td>
                                    <td><?= e($methodLabels[$p['method']] ?? $p['method']) ?></td>
                                    <td class="adm-table__right"><?= e($fmtMoney($p['amount'])) ?></td>
                                    <td class="adm-table__right">
                                        <form method="post" action="<?= e(BASE_URL) ?>/admin/factures/<?= e(rawurlencode($invoice['code'])) ?>/paiement/supprimer"
                                              onsubmit="return confirm('Supprimer ce paiement ? Le statut sera recalculé.');" style="margin:0">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="payment_id" value="<?= (int) $p['id'] ?>">
                                            <button class="adm-btn adm-btn--danger" type="submit">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if ($remaining > 0): ?>
                <h3 class="pay-form-title">Enregistrer un paiement</h3>
                <form class="pay-form" method="post" action="<?= e(BASE_URL) ?>/admin/factures/<?= e(rawurlencode($invoice['code'])) ?>/paiement">
                    <?= csrf_field() ?>
                    <div class="adm-field">
                        <label class="adm-label" for="pay-amount">Montant (DZD)</label>
                        <input class="adm-input" type="number" id="pay-amount" name="amount"
                               min="0.01" max="<?= e(number_format($remaining, 2, '.', '')) ?>" step="0.01"
                               value="<?= e(number_format($remaining, 2, '.', '')) ?>" required>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label" for="pay-date">Date</label>
                        <input class="adm-input" type="date" id="pay-date" name="date" value="<?= e(date('Y-m-d')) ?>" required>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label" for="pay-method">Méthode</label>
                        <select class="adm-input" id="pay-method" name="method">
                            <?php foreach ($methodLabels as $val => $lbl): ?>
                                <option value="<?= e($val) ?>"<?= $val === 'transfer' ? ' selected' : '' ?>><?= e($lbl) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="adm-btn adm-btn--primary" type="submit">Enregistrer le paiement</button>
                </form>
            <?php else: ?>
                <p class="pay-done">✓ Cette facture est intégralement réglée.</p>
            <?php endif; ?>
        </section>
    </main>
</div>

<style>
    .adm-doc__lines { margin: 6px 0 18px; }
    .pay-card { margin-top: 16px; }
    .pay-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 0 0 14px; }
    .pay-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin: 0 0 16px; }
    .pay-summary > div { background: var(--color-surface-alt); border: 1px solid var(--color-border); border-radius: 12px; padding: 12px 14px; }
    .pay-summary__k { display: block; font-size: 12px; color: var(--color-muted); }
    .pay-summary__v { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 18px; color: var(--color-text); }
    .pay-summary__v--ok { color: var(--color-accent-dark); }
    .pay-form-title { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 700; font-size: 15px; color: var(--color-text); margin: 18px 0 12px; }
    .pay-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px; align-items: end; }
    .pay-form .adm-field { margin: 0; }
    .pay-form button { grid-column: 1 / -1; justify-self: start; }
    .pay-done { color: var(--color-success); font-weight: 600; margin: 6px 0 0; }
    @media (min-width: 640px) { .pay-form button { grid-column: auto; } }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
