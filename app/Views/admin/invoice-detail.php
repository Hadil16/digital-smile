<?php
/**
 * app/Views/admin/invoice-detail.php
 * -----------------------------------------------------------------
 * Détail d'une facture (mise en page type document). Variable : $invoice.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

$statusLabels = ['unpaid' => 'Non payée', 'partial' => 'Partielle', 'paid' => 'Payée'];
$fmtMoney = fn($m) => number_format((float) $m, 2, ',', ' ') . ' DZD';
$fmtDate  = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
$tva = (float) $invoice['amount_ttc'] - (float) $invoice['amount_ht']; // montant de TVA
?>
<style>
    /* Styles auto-portés du détail de facture (couleurs de marque). */
    .fac { --violet: #4A3F9E; --lime: #8BC63F;
        min-height: 70vh; padding: 96px 24px 64px; font-family: 'Inter', system-ui, sans-serif; }
    .intro { display: none !important; }
    .fac__wrap { max-width: 720px; margin: 0 auto; }
    .fac__back { display: inline-block; color: var(--violet); font-size: 14px; font-weight: 600;
        text-decoration: none; margin: 0 0 20px; }
    .doc { background: #fff; border: 1px solid #eee; border-top: 5px solid var(--violet);
        border-radius: 16px; padding: 36px; box-shadow: 0 12px 40px rgba(74, 63, 158, .08); }
    .doc__head { display: flex; justify-content: space-between; align-items: flex-start;
        flex-wrap: wrap; gap: 16px; border-bottom: 1px solid #eee; padding-bottom: 20px; margin: 0 0 20px; }
    .doc__brand { font-family: 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 22px;
        color: var(--violet); }
    .doc__brand small { display: block; font-family: 'Inter', system-ui, sans-serif;
        font-weight: 500; font-size: 12px; color: #888; margin-top: 2px; }
    .doc__num { text-align: right; }
    .doc__num strong { display: block; font-family: 'Poppins', system-ui, sans-serif;
        font-size: 18px; color: #222; }
    .doc__num span { font-size: 13px; color: #888; }
    .doc__cols { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px; margin: 0 0 24px; font-size: 14px; }
    .doc__cols h3 { font-size: 12px; text-transform: uppercase; letter-spacing: .04em;
        color: #999; margin: 0 0 6px; }
    .doc__cols p { margin: 0; color: #333; line-height: 1.5; }

    .amounts { border: 1px solid #eee; border-radius: 12px; overflow: hidden; }
    .amounts div { display: flex; justify-content: space-between; padding: 12px 18px;
        font-size: 14px; border-bottom: 1px solid #f0f0f0; }
    .amounts div:last-child { border-bottom: 0; }
    .amounts .ttc { background: #faf9ff; font-weight: 700; font-size: 16px; color: var(--violet); }
    .badge { display: inline-block; font-size: 12px; font-weight: 600; padding: 4px 10px;
        border-radius: 999px; margin-top: 6px; }
    .badge--unpaid  { background: #fdecec; color: #b3261e; }
    .badge--partial { background: #fff7e6; color: #b8860b; }
    .badge--paid    { background: #eef7e0; color: #3B6D11; }
    .fac__bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
        gap: 12px; margin: 0 0 20px; }
    .fac__print { display: inline-block; background: var(--violet); color: #fff; text-decoration: none;
        font-weight: 600; font-size: 14px; padding: 10px 20px; border-radius: 999px; transition: background .2s; }
    .fac__print:hover { background: var(--lime); }
</style>

<main class="fac">
    <div class="fac__wrap">
        <div class="fac__bar">
            <a class="fac__back" href="<?= e(BASE_URL) ?>/admin/factures">← Retour aux factures</a>
            <a class="fac__print"
               href="<?= e(BASE_URL) ?>/admin/factures/<?= e(rawurlencode($invoice['code'])) ?>/imprimer">
                Télécharger / Imprimer
            </a>
        </div>

        <article class="doc">
            <div class="doc__head">
                <div class="doc__brand">Digital Smile
                    <small>Agence de branding &amp; communication — Bab Ezzouar, Alger</small>
                </div>
                <div class="doc__num">
                    <strong><?= e($invoice['code']) ?></strong>
                    <span>Émise le <?= e($fmtDate($invoice['issued_at'])) ?></span><br>
                    <span class="badge badge--<?= e($invoice['status']) ?>">
                        <?= e($statusLabels[$invoice['status']] ?? $invoice['status']) ?>
                    </span>
                </div>
            </div>

            <div class="doc__cols">
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
                    <h3>Commande</h3>
                    <p>
                        N° <?= e($invoice['order_code']) ?><br>
                        <?= e($invoice['project_name']) ?><br>
                        Service : <?= e($invoice['service_name']) ?>
                    </p>
                </div>
            </div>

            <div class="amounts">
                <div><span>Montant HT</span><span><?= e($fmtMoney($invoice['amount_ht'])) ?></span></div>
                <div><span>TVA (<?= e(rtrim(rtrim(number_format((float) $invoice['tax_rate'], 2, ',', ' '), '0'), ',')) ?> %)</span><span><?= e($fmtMoney($tva)) ?></span></div>
                <div class="ttc"><span>Total TTC</span><span><?= e($fmtMoney($invoice['amount_ttc'])) ?></span></div>
            </div>
        </article>
    </div>
</main>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
