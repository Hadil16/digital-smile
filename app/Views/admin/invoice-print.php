<?php
/**
 * app/Views/admin/invoice-print.php
 * -----------------------------------------------------------------
 * Facture imprimable (A4), page AUTONOME — aucune librairie externe.
 * Utilisée par l'admin ET par le client (les droits sont vérifiés dans
 * les contrôleurs). Le bouton appelle window.print() : l'utilisateur
 * choisit "Enregistrer en PDF" dans la boîte d'impression du navigateur.
 * Variable : $invoice.
 * -----------------------------------------------------------------
 */
$fmtMoney = fn($m) => number_format((float) $m, 2, ',', ' ') . ' DZD';
$fmtDate  = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
// Taux affiché sans zéros inutiles (19.00 -> "19").
$rate = rtrim(rtrim(number_format((float) $invoice['tax_rate'], 2, ',', ' '), '0'), ',');
$tva  = (float) $invoice['amount_ttc'] - (float) $invoice['amount_ht'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Facture <?= e($invoice['code']) ?> — Digital Smile</title>
<meta name="robots" content="noindex">
<style>
    :root { --violet: #4A3F9E; --lime: #8BC63F; --ink: #222; --grey: #777; }
    * { margin: 0; box-sizing: border-box; }
    body { font-family: 'Inter', system-ui, Arial, sans-serif; color: var(--ink);
        background: #eef0f4; padding: 24px; }

    /* Barre d'actions : visible à l'écran, masquée à l'impression. */
    .toolbar { max-width: 210mm; margin: 0 auto 16px; display: flex; justify-content: space-between;
        gap: 12px; flex-wrap: wrap; }
    .toolbar a, .toolbar button { font-family: inherit; font-size: 14px; font-weight: 600; border: 0;
        cursor: pointer; padding: 11px 22px; border-radius: 999px; text-decoration: none; }
    .toolbar .back { background: #fff; color: var(--violet); border: 1px solid #d5d5db; }
    .toolbar .print { background: var(--violet); color: #fff; }
    .toolbar .print:hover { background: var(--lime); }

    /* Feuille A4. */
    .sheet { background: #fff; width: 210mm; min-height: 297mm; margin: 0 auto; padding: 20mm;
        box-shadow: 0 10px 40px rgba(0, 0, 0, .12); }
    .head { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;
        border-bottom: 3px solid var(--violet); padding-bottom: 18px; }
    .brand { font-family: 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 26px; color: var(--violet); }
    .brand small { display: block; font-weight: 500; font-size: 12px; color: var(--lime); margin-top: 2px; }
    .title { text-align: right; }
    .title h1 { font-family: 'Poppins', system-ui, sans-serif; font-size: 22px; color: var(--ink); letter-spacing: .04em; }
    .title span { font-size: 13px; color: var(--grey); }

    .company { font-size: 11px; color: var(--grey); line-height: 1.7; margin-top: 14px; }
    .placeholder { color: #b3261e; font-style: italic; }

    .parties { display: flex; justify-content: space-between; gap: 24px; margin: 26px 0 4px; }
    .parties h2 { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: var(--grey); margin-bottom: 6px; }
    .parties p { font-size: 14px; line-height: 1.6; }
    .parties .right { text-align: right; }

    table.lines { width: 100%; border-collapse: collapse; margin: 18px 0 0; font-size: 14px; }
    table.lines th { background: var(--violet); color: #fff; text-align: left; padding: 11px 14px; font-weight: 600; }
    table.lines th.r, table.lines td.r { text-align: right; white-space: nowrap; }
    table.lines td { padding: 12px 14px; border-bottom: 1px solid #eee; vertical-align: top; }
    table.lines .sub { color: var(--grey); font-size: 13px; }

    .totals { margin: 18px 0 0 auto; width: 300px; font-size: 14px; }
    .totals div { display: flex; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid #eee; }
    .totals .ttc { border-bottom: 0; border-top: 2px solid var(--violet); margin-top: 4px; padding-top: 12px;
        font-weight: 800; font-size: 17px; color: var(--violet); }

    .foot { margin-top: 44px; padding-top: 16px; border-top: 1px solid #eee; text-align: center;
        color: var(--grey); font-size: 13px; }

    /* À l'impression : on ne garde QUE la facture. */
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
                <h1>FACTURE</h1>
                <span><?= e($invoice['code']) ?><br>Émise le <?= e($fmtDate($invoice['issued_at'])) ?></span>
            </div>
        </div>

        <p class="company">
            Agence de branding &amp; communication — Cité 1200 Logts, Bt S, Local N° 17, Bab Ezzouar, Alger<br>
            RC 5146243 A 22 · Gérant : Yahiaoui Arezki<br>
            Tél : +213 (0) 549 56 22 05 · Email : arezki69@gmail.com<br>
            NIF : <span class="placeholder">EXEMPLE À REMPLACER</span> ·
            NIS : <span class="placeholder">EXEMPLE À REMPLACER</span>
        </p>

        <div class="parties">
            <div>
                <h2>Facturé à</h2>
                <p>
                    <?= e($invoice['client_name']) ?><br>
                    <?php if (!empty($invoice['company'])): ?><?= e($invoice['company']) ?><br><?php endif; ?>
                    <?php if (!empty($invoice['address'])): ?><?= e($invoice['address']) ?><br><?php endif; ?>
                    <?php if (!empty($invoice['city'])): ?><?= e($invoice['city']) ?><br><?php endif; ?>
                    <?= e($invoice['client_email']) ?>
                </p>
            </div>
            <div class="right">
                <h2>Commande</h2>
                <p>N° <?= e($invoice['order_code']) ?></p>
            </div>
        </div>

        <table class="lines">
            <thead>
                <tr><th>Service / Description</th><th class="r">Montant HT</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong><?= e($invoice['service_name']) ?></strong><br>
                        <span class="sub"><?= e($invoice['project_name']) ?></span>
                    </td>
                    <td class="r"><?= e($fmtMoney($invoice['amount_ht'])) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="totals">
            <div><span>Montant HT</span><span><?= e($fmtMoney($invoice['amount_ht'])) ?></span></div>
            <div><span>TVA (<?= e($rate) ?> %)</span><span><?= e($fmtMoney($tva)) ?></span></div>
            <div class="ttc"><span>Total TTC</span><span><?= e($fmtMoney($invoice['amount_ttc'])) ?></span></div>
        </div>

        <p class="foot">Merci pour votre confiance.</p>
    </div>
</body>
</html>
