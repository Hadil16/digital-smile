<?php
/**
 * app/Views/admin/bilan-print.php
 * -----------------------------------------------------------------
 * Bilan financier imprimable (A4), page AUTONOME — aucune librairie.
 * Le bouton appelle window.print(). Variables : $g, $from, $to,
 * $summary, $paid, $byPeriod, $orders.
 * -----------------------------------------------------------------
 */
$fmtMoney = fn($m) => number_format((float) $m, 2, ',', ' ') . ' DZD';
$fmtDate  = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';

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

$ttc = (float) $summary['ttc'];
$unpaidPct = ($paid['available'] && $ttc > 0) ? round($paid['unpaid_ttc'] / $ttc * 100, 1) : null;

$tot = ['ht' => 0.0, 'tva' => 0.0, 'ttc' => 0.0, 'n' => 0, 'unpaid' => 0.0];
foreach ($byPeriod as $r) {
    $tot['ht'] += (float) $r['ht']; $tot['tva'] += (float) $r['tva']; $tot['ttc'] += (float) $r['ttc'];
    $tot['n'] += (int) $r['n'];     $tot['unpaid'] += (float) $r['unpaid_ttc'];
}
$granLabel = ['jour' => 'jour', 'mois' => 'mois', 'annee' => 'année'][$g] ?? 'mois';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bilan financier — Digital Smile</title>
<meta name="robots" content="noindex">
<style>
    :root { --violet: #4A3F9E; --lime: #8BC63F; --ink: #222; --grey: #777; }
    * { margin: 0; box-sizing: border-box; }
    body { font-family: 'Inter', system-ui, Arial, sans-serif; color: var(--ink); background: #eef0f4; padding: 24px; }

    .toolbar { max-width: 210mm; margin: 0 auto 16px; display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .toolbar a, .toolbar button { font-family: inherit; font-size: 14px; font-weight: 600; border: 0; cursor: pointer;
        padding: 11px 22px; border-radius: 999px; text-decoration: none; }
    .toolbar .back { background: #fff; color: var(--violet); border: 1px solid #d5d5db; }
    .toolbar .print { background: var(--violet); color: #fff; }
    .toolbar .print:hover { background: var(--lime); }

    .sheet { background: #fff; width: 210mm; min-height: 297mm; margin: 0 auto; padding: 20mm; box-shadow: 0 10px 40px rgba(0,0,0,.12); }
    .head { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; border-bottom: 3px solid var(--violet); padding-bottom: 18px; }
    .brand { font-family: 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 26px; color: var(--violet); }
    .brand small { display: block; font-weight: 500; font-size: 12px; color: var(--lime); margin-top: 2px; }
    .title { text-align: right; }
    .title h1 { font-family: 'Poppins', system-ui, sans-serif; font-size: 22px; color: var(--ink); letter-spacing: .04em; }
    .title span { font-size: 13px; color: var(--grey); }
    .company { font-size: 11px; color: var(--grey); line-height: 1.7; margin-top: 14px; }
    .context { font-size: 12px; color: var(--grey); margin: 18px 0 0; line-height: 1.6; }

    .kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 20px 0 0; }
    .kpi { border: 1px solid #eee; border-radius: 12px; padding: 14px 16px; }
    .kpi h3 { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: var(--grey); font-weight: 700; }
    .kpi p { font-family: 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 18px; color: var(--violet); margin-top: 6px; }
    .kpi small { color: var(--grey); font-weight: 500; font-size: 11px; }

    table.recap { width: 100%; border-collapse: collapse; margin: 22px 0 0; font-size: 13px; }
    table.recap th { background: var(--violet); color: #fff; text-align: right; padding: 10px 12px; font-weight: 600; }
    table.recap th:first-child { text-align: left; }
    table.recap td { padding: 10px 12px; border-bottom: 1px solid #eee; text-align: right; }
    table.recap td:first-child { text-align: left; font-weight: 600; }
    table.recap tfoot td { border-top: 2px solid var(--violet); border-bottom: 0; font-weight: 800; color: var(--violet); }

    .foot { margin-top: 30px; padding-top: 14px; border-top: 1px solid #eee; text-align: center; color: var(--grey); font-size: 12px; }

    @media print {
        body { background: #fff; padding: 0; }
        .toolbar { display: none !important; }
        .sheet { width: auto; min-height: auto; margin: 0; padding: 0; box-shadow: none; }
    }
    @page { size: A4; margin: 15mm; }
</style>
</head>
<body>
    <div class="toolbar">
        <a class="back" href="#" onclick="history.back(); return false;">← Retour</a>
        <button class="print" type="button" onclick="window.print()">Imprimer / Enregistrer en PDF</button>
    </div>

    <div class="sheet">
        <div class="head">
            <div class="brand">Digital Smile<small>Digital Like Never Before</small></div>
            <div class="title">
                <h1>BILAN FINANCIER</h1>
                <span>Période : <?= e($fmtDate($from)) ?> → <?= e($fmtDate($to)) ?><br>Édité le <?= e(date('d/m/Y')) ?></span>
            </div>
        </div>

        <p class="company">
            Agence de branding &amp; communication — Cité 1200 Logts, Bt S, Local N° 17, Bab Ezzouar, Alger<br>
            RC XXXXXXX A 22 · Gérant : Agency Owner · Tél : +213 (0) 5XX XX XX XX
        </p>

        <div class="kpis">
            <div class="kpi"><h3>CA HT</h3><p><?= e($fmtMoney($summary['ht'])) ?></p><small>Hors taxe</small></div>
            <div class="kpi"><h3>TVA collectée</h3><p><?= e($fmtMoney($summary['tva'])) ?></p><small>TVA facturée</small></div>
            <div class="kpi"><h3>CA TTC</h3><p><?= e($fmtMoney($summary['ttc'])) ?></p><small>Toutes taxes comprises</small></div>
            <div class="kpi"><h3>Factures</h3><p><?= (int) $summary['count'] ?></p><small>Sur la période</small></div>
            <div class="kpi"><h3>Panier moyen</h3><p><?= e($fmtMoney($summary['avg'])) ?></p><small>TTC par facture</small></div>
            <div class="kpi"><h3>Impayé</h3>
                <p><?= $paid['available'] ? e($fmtMoney($paid['unpaid_ttc'])) : '—' ?></p>
                <small><?= $paid['available'] ? ($unpaidPct !== null ? e($unpaidPct) . ' % du CA TTC' : 'Aucune facture') : 'Statut indisponible' ?></small>
            </div>
        </div>

        <table class="recap">
            <thead>
                <tr>
                    <th>Période (<?= e($granLabel) ?>)</th>
                    <th>CA HT</th><th>TVA</th><th>CA TTC</th><th>Nb</th><th>Impayé</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($byPeriod)): ?>
                    <tr><td colspan="6" style="text-align:center;color:#777">Aucune facture sur la période.</td></tr>
                <?php else: ?>
                    <?php foreach ($byPeriod as $r): ?>
                        <tr>
                            <td><?= e($formatPeriod($r['period'])) ?></td>
                            <td><?= e($fmtMoney($r['ht'])) ?></td>
                            <td><?= e($fmtMoney($r['tva'])) ?></td>
                            <td><?= e($fmtMoney($r['ttc'])) ?></td>
                            <td><?= (int) $r['n'] ?></td>
                            <td><?= e($fmtMoney($r['unpaid_ttc'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td><?= e($fmtMoney($tot['ht'])) ?></td>
                    <td><?= e($fmtMoney($tot['tva'])) ?></td>
                    <td><?= e($fmtMoney($tot['ttc'])) ?></td>
                    <td><?= (int) $tot['n'] ?></td>
                    <td><?= e($fmtMoney($tot['unpaid'])) ?></td>
                </tr>
            </tfoot>
        </table>

        <p class="context">
            Ce document présente le <strong>chiffre d'affaires (facturation)</strong> sur la période :
            HT (hors taxe), TVA collectée au taux facturé (par défaut 19 %), TTC (toutes taxes comprises).
            Il ne s'agit <strong>pas d'un résultat net</strong> : les charges et dépenses ne sont pas suivies.
            Activité : <?= (int) $orders['created'] ?> commande(s) créée(s), dont <?= (int) $orders['completed'] ?> terminée(s).
        </p>

        <p class="foot">Digital Smile — Bilan de chiffre d'affaires. Document interne.</p>
    </div>
</body>
</html>
