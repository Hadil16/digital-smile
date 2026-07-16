<?php
/**
 * app/Views/admin/bilan.php
 * -----------------------------------------------------------------
 * Bilan financier (comptable) — chiffre d'affaires & facturation.
 * DONNÉES RÉELLES uniquement (aucun chiffre inventé). Variables :
 *   $g, $from, $to, $summary, $paid, $byPeriod, $byService, $orders.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

$fmtMoney = fn($m) => number_format((float) $m, 2, ',', ' ') . ' DZD';

// Libellé lisible d'une période selon la granularité.
$formatPeriod = function (string $p) use ($g) {
    if ($g === 'annee') { return $p; }
    if ($g === 'mois') {
        $dt = DateTime::createFromFormat('Y-m-d', $p . '-01');
        $mois = ['', 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
        return $dt ? $mois[(int) $dt->format('n')] . ' ' . $dt->format('Y') : $p;
    }
    $dt = DateTime::createFromFormat('Y-m-d', $p);
    return $dt ? $dt->format('d/m/Y') : $p;
};

$ttc       = (float) $summary['ttc'];
$unpaidPct = ($paid['available'] && $ttc > 0) ? round($paid['unpaid_ttc'] / $ttc * 100, 1) : null;

// Totaux du tableau récapitulatif (doivent égaler la synthèse).
$tot = ['ht' => 0.0, 'tva' => 0.0, 'ttc' => 0.0, 'n' => 0, 'unpaid' => 0.0];
foreach ($byPeriod as $r) {
    $tot['ht']     += (float) $r['ht'];
    $tot['tva']    += (float) $r['tva'];
    $tot['ttc']    += (float) $r['ttc'];
    $tot['n']      += (int) $r['n'];
    $tot['unpaid'] += (float) $r['unpaid_ttc'];
}

// Données des graphiques (rendues par le script auto-porté en bas de page).
$chartData = [
    'revenue' => [
        'labels' => array_map($formatPeriod, array_column($byPeriod, 'period')),
        'values' => array_map(fn($r) => (float) $r['ttc'], $byPeriod),
    ],
    'service' => [
        'labels' => array_column($byService, 'service'),
        'values' => array_map(fn($r) => (float) $r['ttc'], $byService),
    ],
    'paid' => [
        'available' => (bool) $paid['available'],
        'labels'    => ['Payées', 'Impayées'],
        'values'    => [(float) $paid['paid_ttc'], (float) $paid['unpaid_ttc']],
    ],
];

// KPI (réels). Les montants sont des chaînes déjà formatées (rendu --sm).
$kpis = [
    ['CA HT',        $fmtMoney($summary['ht']),  '💰', 'violet', 'Hors taxe'],
    ['TVA collectée', $fmtMoney($summary['tva']), '🧾', 'amber',  'TVA facturée'],
    ['CA TTC',       $fmtMoney($summary['ttc']), '💵', 'green',  'Toutes taxes comprises'],
    ['Factures',     (string) (int) $summary['count'], '📄', 'violet', 'Sur la période'],
    ['Panier moyen', $fmtMoney($summary['avg']), '🛒', 'green',  'TTC par facture'],
    ['Impayé',
        $paid['available'] ? $fmtMoney($paid['unpaid_ttc']) : '—',
        '⏳', 'red',
        $paid['available'] ? ($unpaidPct !== null ? $unpaidPct . ' % du CA TTC' : 'Aucune facture') : 'Statut indisponible'],
];

// Liens de période (conservent la plage de dates courante).
$periodLink = fn($gran) => e(BASE_URL) . '/admin/bilan?g=' . $gran . '&from=' . e($from) . '&to=' . e($to);

// Coquille admin commune (sidebar + entête).
$adminActive  = 'bilan';
$pageTitle    = 'Bilan financier';
$pageSubtitle = 'Vue comptable — chiffre d\'affaires et facturation';
require ROOT_PATH . '/app/Views/partials/admin-sidebar.php';
?>
        <!-- Sélecteur de période -->
        <div class="bl-controls">
            <div class="adm-gran" role="group" aria-label="Granularité">
                <?php foreach (['jour' => 'Jour', 'mois' => 'Mois', 'annee' => 'Année'] as $key => $lbl): ?>
                    <a class="adm-gran__btn<?= $g === $key ? ' is-active' : '' ?>" href="<?= $periodLink($key) ?>"><?= $lbl ?></a>
                <?php endforeach; ?>
            </div>
            <form class="bl-range" method="get" action="<?= e(BASE_URL) ?>/admin/bilan">
                <input type="hidden" name="g" value="<?= e($g) ?>">
                <label>Du <input class="adm-input" type="date" name="from" value="<?= e($from) ?>"></label>
                <label>Au <input class="adm-input" type="date" name="to" value="<?= e($to) ?>"></label>
                <button class="adm-btn adm-btn--ghost" type="submit">Appliquer</button>
                <a class="adm-btn adm-btn--assign" href="<?= e(BASE_URL) ?>/admin/bilan/imprimer?g=<?= e($g) ?>&from=<?= e($from) ?>&to=<?= e($to) ?>" target="_blank" rel="noopener">Imprimer le bilan</a>
            </form>
        </div>

        <p class="bl-note">
            Période : <strong><?= e($from) ?></strong> → <strong><?= e($to) ?></strong>.
            Ce bilan présente le <strong>chiffre d'affaires (facturation)</strong>, hors charges et dépenses :
            ce n'est <em>pas</em> un résultat net. TVA au taux facturé (par défaut 19 %).
        </p>

        <!-- KPI (données réelles) -->
        <section class="adm__kpis" aria-label="Indicateurs financiers">
            <?php foreach ($kpis as [$lbl, $val, $ico, $tone, $cap]): ?>
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

        <p class="bl-activity">Activité : <strong><?= (int) $orders['created'] ?></strong> commande(s) créée(s) sur la période, dont <strong><?= (int) $orders['completed'] ?></strong> terminée(s).</p>

        <!-- Graphiques -->
        <section class="bl-charts">
            <div class="adm-card">
                <h2 class="adm-card__title">Évolution du chiffre d'affaires</h2>
                <div class="bl-canvas"><canvas id="blRevenue"></canvas></div>
            </div>
            <div class="adm-card">
                <h2 class="adm-card__title">Répartition du CA par service</h2>
                <div class="bl-canvas"><canvas id="blService"></canvas></div>
            </div>
            <?php if ($paid['available']): ?>
                <div class="adm-card">
                    <h2 class="adm-card__title">Factures payées vs impayées</h2>
                    <div class="bl-canvas"><canvas id="blPaid"></canvas></div>
                </div>
            <?php else: ?>
                <div class="adm-card">
                    <h2 class="adm-card__title">Factures payées vs impayées</h2>
                    <p class="adm__empty">Donnée indisponible : le statut de paiement n'est pas suivi.</p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Tableau récapitulatif -->
        <h2 class="adm-section">Récapitulatif par période</h2>
        <?php if (empty($byPeriod)): ?>
            <p class="adm-empty">Aucune facture sur la période sélectionnée.</p>
        <?php else: ?>
            <div class="adm-card">
                <div class="adm-table__scroll">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Période</th>
                                <th class="adm-table__right">CA HT</th>
                                <th class="adm-table__right">TVA</th>
                                <th class="adm-table__right">CA TTC</th>
                                <th class="adm-table__right">Nb factures</th>
                                <th class="adm-table__right">Impayé</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($byPeriod as $r): ?>
                                <tr>
                                    <td class="adm-table__num"><?= e($formatPeriod($r['period'])) ?></td>
                                    <td class="adm-table__right"><?= e($fmtMoney($r['ht'])) ?></td>
                                    <td class="adm-table__right"><?= e($fmtMoney($r['tva'])) ?></td>
                                    <td class="adm-table__right"><?= e($fmtMoney($r['ttc'])) ?></td>
                                    <td class="adm-table__right"><?= (int) $r['n'] ?></td>
                                    <td class="adm-table__right"><?= e($fmtMoney($r['unpaid_ttc'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bl-total">
                                <td>Total</td>
                                <td class="adm-table__right"><?= e($fmtMoney($tot['ht'])) ?></td>
                                <td class="adm-table__right"><?= e($fmtMoney($tot['tva'])) ?></td>
                                <td class="adm-table__right"><?= e($fmtMoney($tot['ttc'])) ?></td>
                                <td class="adm-table__right"><?= (int) $tot['n'] ?></td>
                                <td class="adm-table__right"><?= e($fmtMoney($tot['unpaid'])) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<style>
    .bl-controls { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; margin: 0 0 14px; }
    .bl-range { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin: 0; }
    .bl-range label { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: var(--color-muted); }
    .bl-range .adm-input { width: auto; padding: 8px 10px; }
    .bl-note { font-size: 13px; color: var(--color-muted); background: var(--color-surface-alt); border: 1px solid var(--color-border);
        border-radius: 12px; padding: 12px 16px; margin: 0 0 18px; line-height: 1.5; }
    .bl-activity { font-size: 14px; color: var(--color-text); margin: 0 0 18px; }
    .bl-charts { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin: 0 0 8px; }
    .bl-canvas { position: relative; height: 280px; }
    .bl-total td { border-top: 2px solid var(--color-border); font-weight: 700; color: var(--color-text); padding-top: 14px; }
</style>

<!-- Rendu Chart.js AUTO-PORTÉ (couleurs de marque, libellés comptables honnêtes). -->
<script>
    window.__BILAN = <?= json_encode(
        $chartData,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
    ) ?>;
    (function () {
        var d = window.__BILAN; if (!d) return;
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
        s.onload = render; document.body.appendChild(s);
        function render() {
            if (typeof Chart === 'undefined') return;
            var violet = '#4A3F9E', lime = '#8BC63F', red = '#b3261e';
            var palette = ['#4A3F9E', '#8BC63F', '#f0a500', '#1e6fd9', '#0d9488', '#b3261e', '#6b5fd4', '#6BA02C'];
            var rev = document.getElementById('blRevenue');
            if (rev && d.revenue) new Chart(rev, { type: 'line',
                data: { labels: d.revenue.labels, datasets: [{ label: 'CA TTC (DZD)', data: d.revenue.values,
                    borderColor: violet, backgroundColor: 'rgba(74,63,158,.12)', fill: true, tension: .3, pointBackgroundColor: violet, pointRadius: 4 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
            var sv = document.getElementById('blService');
            if (sv && d.service) new Chart(sv, { type: 'doughnut',
                data: { labels: d.service.labels, datasets: [{ data: d.service.values, backgroundColor: palette, borderColor: '#fff', borderWidth: 2 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 14 } } } } });
            var pu = document.getElementById('blPaid');
            if (pu && d.paid && d.paid.available) new Chart(pu, { type: 'bar',
                data: { labels: d.paid.labels, datasets: [{ label: 'Montant TTC (DZD)', data: d.paid.values, backgroundColor: [lime, red], borderRadius: 6 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
        }
    })();
</script>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
