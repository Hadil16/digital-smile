<?php
/**
 * app/Views/admin/invoices.php
 * -----------------------------------------------------------------
 * Facturation (admin) : commandes terminées à facturer + factures
 * déjà émises. Variables : $toInvoice, $invoices, $flash.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

$statusLabels = ['unpaid' => 'Non payée', 'partial' => 'Partielle', 'paid' => 'Payée'];
$fmtMoney = fn($m) => number_format((float) $m, 2, ',', ' ') . ' DZD';
$fmtDate  = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
?>
<style>
    /* Styles auto-portés de la facturation (couleurs de marque). */
    .adm { --violet: #4A3F9E; --lime: #8BC63F;
        min-height: 70vh; padding: 96px 24px 64px; font-family: 'Inter', system-ui, sans-serif; }
    .intro { display: none !important; }
    .adm__wrap { max-width: 960px; margin: 0 auto; }
    .adm__role { display: inline-block; background: var(--violet); color: #fff; font-size: 13px;
        font-weight: 600; padding: 6px 14px; border-radius: 999px; margin: 0 0 10px; }
    .adm__title { font-family: 'Poppins', system-ui, sans-serif; font-weight: 800;
        font-size: clamp(24px, 4vw, 34px); color: var(--violet); margin: 0 0 8px; }
    .adm__back { display: inline-block; color: var(--violet); font-size: 14px; font-weight: 600;
        text-decoration: none; margin: 0 0 24px; }
    .adm__h2 { font-family: 'Poppins', system-ui, sans-serif; font-size: 20px; color: #222;
        margin: 34px 0 16px; }
    .flash { background: #eef7e0; color: #3B6D11; border: 1px solid #cfe6a8;
        border-radius: 12px; padding: 12px 16px; margin: 0 0 22px; font-size: 14px; }
    .empty { background: #faf9ff; border: 1px dashed #d7d2f0; color: #666;
        border-radius: 14px; padding: 36px; text-align: center; font-size: 15px; }

    /* Cartes des commandes à facturer */
    .order { background: #fff; border: 1px solid #eee; border-radius: 16px; padding: 22px 24px;
        margin: 0 0 16px; box-shadow: 0 12px 40px rgba(74, 63, 158, .06); }
    .order__head { display: flex; align-items: center; gap: 12px; margin: 0 0 12px; }
    .order__code { font-family: 'Poppins', system-ui, sans-serif; font-weight: 700; color: var(--violet); }
    .order__meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 8px 20px; margin: 0 0 18px; font-size: 14px; }
    .order__meta span { display: block; color: #999; font-size: 12px; }
    .order__actions { border-top: 1px solid #f0f0f0; padding-top: 16px; }
    .order__actions form { margin: 0; }

    .btn { display: inline-block; border: 0; cursor: pointer; text-decoration: none; font-weight: 600;
        font-size: 14px; padding: 11px 20px; border-radius: 999px; font-family: inherit;
        transition: background .2s, color .2s; }
    .btn--gen { background: var(--lime); color: #23400a; }
    .btn--gen:hover { background: #7cb034; }
    .btn--view { border: 1px solid #d5d5db; color: #444; padding: 7px 16px; }
    .btn--view:hover { border-color: var(--violet); color: var(--violet); }

    /* Tableau des factures émises */
    .table__scroll { overflow-x: auto; border: 1px solid #eee; border-radius: 14px; }
    .table { width: 100%; border-collapse: collapse; font-size: 14px; min-width: 640px; }
    .table th, .table td { text-align: left; padding: 13px 16px; border-bottom: 1px solid #f0f0f0; }
    .table th { background: #faf9ff; color: #555; font-weight: 600; white-space: nowrap; }
    .table tr:last-child td { border-bottom: 0; }
    .table td:first-child { font-weight: 600; color: var(--violet); white-space: nowrap; }
    .table td.num { text-align: right; white-space: nowrap; }
    .badge { display: inline-block; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 999px; }
    .badge--unpaid  { background: #fdecec; color: #b3261e; }
    .badge--partial { background: #fff7e6; color: #b8860b; }
    .badge--paid    { background: #eef7e0; color: #3B6D11; }
</style>

<main class="adm">
    <div class="adm__wrap">
        <p class="adm__role">Espace administrateur</p>
        <h1 class="adm__title">Facturation</h1>
        <a class="adm__back" href="<?= e(BASE_URL) ?>/admin">← Retour au tableau de bord</a>

        <?php if (!empty($flash)): ?>
            <p class="flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <!-- ============ Commandes terminées à facturer ============ -->
        <h2 class="adm__h2">Commandes à facturer</h2>

        <?php if (empty($toInvoice)): ?>
            <p class="empty">Aucune commande à facturer.</p>
        <?php else: ?>
            <?php foreach ($toInvoice as $o): ?>
                <article class="order">
                    <div class="order__head">
                        <span class="order__code"><?= e($o['code']) ?></span>
                        <span class="badge badge--paid">Terminée</span>
                    </div>
                    <div class="order__meta">
                        <div><span>Client</span><?= e($o['client_name']) ?></div>
                        <div><span>Service</span><?= e($o['service_name']) ?></div>
                        <div><span>Projet</span><?= e($o['project_name']) ?></div>
                        <div><span>Montant HT</span><?= e($fmtMoney($o['budget'] ?? 0)) ?></div>
                    </div>
                    <div class="order__actions">
                        <form method="post" action="<?= e(BASE_URL) ?>/admin/factures/generer">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                            <button class="btn btn--gen" type="submit">Générer la facture</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ============ Factures émises ============ -->
        <h2 class="adm__h2">Factures émises</h2>

        <?php if (empty($invoices)): ?>
            <p class="empty">Aucune facture émise pour le moment.</p>
        <?php else: ?>
            <div class="table__scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Client</th>
                            <th>Commande</th>
                            <th>Montant TTC</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td><?= e($inv['code']) ?></td>
                                <td><?= e($inv['client_name']) ?></td>
                                <td><?= e($inv['order_code']) ?></td>
                                <td class="num"><?= e($fmtMoney($inv['amount_ttc'])) ?></td>
                                <td>
                                    <span class="badge badge--<?= e($inv['status']) ?>">
                                        <?= e($statusLabels[$inv['status']] ?? $inv['status']) ?>
                                    </span>
                                </td>
                                <td><?= e($fmtDate($inv['issued_at'])) ?></td>
                                <td>
                                    <a class="btn btn--view"
                                       href="<?= e(BASE_URL) ?>/admin/factures/<?= e(rawurlencode($inv['code'])) ?>"
                                       aria-label="Voir la facture <?= e($inv['code']) ?>">Voir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
        overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
