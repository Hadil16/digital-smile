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
    'unpaid'  => ['Non payée', 'red'],
    'partial' => ['Partielle', 'amber'],
    'paid'    => ['Payée',     'green'],
];
$fmtMoney = fn($m) => number_format((float) $m, 2, ',', ' ') . ' DZD';
$fmtDate  = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
$tva = (float) $invoice['amount_ttc'] - (float) $invoice['amount_ht']; // montant de TVA
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
    </main>
</div>

<style>
    .adm-doc__lines { margin: 6px 0 18px; }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
