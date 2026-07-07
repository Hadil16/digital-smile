<?php
/**
 * app/Views/admin/orders.php
 * -----------------------------------------------------------------
 * Revue des demandes (admin) : demandes en attente (avec actions
 * approuver / refuser / affecter) + vue d'ensemble de toutes les
 * commandes. Variables : $pending, $allOrders, $employees, $flash.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// Libellés FR des statuts (le style du badge vient de .badge--<statut>).
$statusLabels = [
    'pending'     => 'En attente',
    'approved'    => 'Acceptée',
    'rejected'    => 'Refusée',
    'in_progress' => 'En cours',
    'delivered'   => 'Livrée',
    'completed'   => 'Terminée',
    'cancelled'   => 'Annulée',
];

// Petit utilitaire d'affichage (budget / date), pour ne pas se répéter.
$fmtBudget = fn($b) => $b !== null ? number_format((float) $b, 0, ',', ' ') . ' DZD' : '—';
$fmtDate   = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
?>
<style>
    /* Styles auto-portés de la revue des demandes (couleurs de marque). */
    .adm { --violet: #4A3F9E; --lime: #8BC63F;
        min-height: 70vh; padding: 96px 24px 64px; font-family: 'Inter', system-ui, sans-serif; }
    .intro { display: none !important; } /* pas d'intro flash hors accueil */
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

    /* Cartes des demandes en attente */
    .order { background: #fff; border: 1px solid #eee; border-radius: 16px; padding: 22px 24px;
        margin: 0 0 16px; box-shadow: 0 12px 40px rgba(74, 63, 158, .06); }
    .order__head { display: flex; align-items: center; gap: 12px; margin: 0 0 12px; }
    .order__code { font-family: 'Poppins', system-ui, sans-serif; font-weight: 700; color: var(--violet); }
    .order__meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 8px 20px; margin: 0 0 18px; font-size: 14px; }
    .order__meta div { color: #333; }
    .order__meta span { display: block; color: #999; font-size: 12px; }
    .order__actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
        border-top: 1px solid #f0f0f0; padding-top: 16px; }
    .order__actions form { display: flex; align-items: center; gap: 8px; margin: 0; }

    .btn { display: inline-block; border: 0; cursor: pointer; text-decoration: none; font-weight: 600;
        font-size: 14px; padding: 10px 18px; border-radius: 999px; font-family: inherit;
        transition: background .2s, color .2s; }
    .btn--approve { background: var(--lime); color: #23400a; }
    .btn--approve:hover { background: #7cb034; }
    .btn--reject { background: #fdecec; color: #b3261e; }
    .btn--reject:hover { background: #f7d6d6; }
    .btn--assign { background: var(--violet); color: #fff; }
    .btn--assign:hover { background: #372f78; }
    .btn--ghost { border: 1px solid #d5d5db; color: #444; }
    .btn--ghost:hover { border-color: var(--violet); color: var(--violet); }
    .select { padding: 9px 12px; border: 1px solid #d5d5db; border-radius: 999px;
        font-size: 14px; font-family: inherit; background: #fff; }
    .select:focus { outline: 2px solid var(--violet); outline-offset: 1px; border-color: var(--violet); }

    /* Tableau vue d'ensemble */
    .table__scroll { overflow-x: auto; border: 1px solid #eee; border-radius: 14px; }
    .table { width: 100%; border-collapse: collapse; font-size: 14px; min-width: 620px; }
    .table th, .table td { text-align: left; padding: 13px 16px; border-bottom: 1px solid #f0f0f0; }
    .table th { background: #faf9ff; color: #555; font-weight: 600; white-space: nowrap; }
    .table tr:last-child td { border-bottom: 0; }
    .table td:first-child { font-weight: 600; color: var(--violet); white-space: nowrap; }
    .badge { display: inline-block; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 999px; }
    .badge--pending     { background: #fff7e6; color: #b8860b; }
    .badge--approved    { background: #ece9fb; color: #4A3F9E; }
    .badge--in_progress { background: #e6f0fc; color: #1e6fd9; }
    .badge--delivered   { background: #e3f7f4; color: #0d9488; }
    .badge--completed   { background: #eef7e0; color: #3B6D11; }
    .badge--rejected    { background: #fdecec; color: #b3261e; }
    .badge--cancelled   { background: #eee; color: #666; }
</style>

<main class="adm">
    <div class="adm__wrap">
        <p class="adm__role">Espace administrateur</p>
        <h1 class="adm__title">Gestion des demandes</h1>
        <a class="adm__back" href="<?= e(BASE_URL) ?>/admin">← Retour au tableau de bord</a>

        <?php if (!empty($flash)): ?>
            <p class="flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <!-- ============ Demandes en attente (avec actions) ============ -->
        <h2 class="adm__h2">Demandes en attente</h2>

        <?php if (empty($pending)): ?>
            <p class="empty">Aucune demande en attente.</p>
        <?php else: ?>
            <?php foreach ($pending as $o): ?>
                <article class="order">
                    <div class="order__head">
                        <span class="order__code"><?= e($o['code']) ?></span>
                        <span class="badge badge--pending">En attente</span>
                    </div>

                    <div class="order__meta">
                        <div><span>Client</span><?= e($o['client_name']) ?></div>
                        <div><span>Service</span><?= e($o['service_name']) ?></div>
                        <div><span>Projet</span><?= e($o['project_name']) ?></div>
                        <div><span>Budget</span><?= e($fmtBudget($o['budget'])) ?></div>
                        <div><span>Échéance</span><?= e($fmtDate($o['deadline'])) ?></div>
                        <div><span>Reçue le</span><?= e($fmtDate($o['created_at'])) ?></div>
                    </div>

                    <div class="order__actions">
                        <!-- Approuver -->
                        <form method="post" action="<?= e(BASE_URL) ?>/admin/commandes/approuver">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                            <button class="btn btn--approve" type="submit">Approuver</button>
                        </form>

                        <!-- Refuser -->
                        <form method="post" action="<?= e(BASE_URL) ?>/admin/commandes/refuser">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                            <button class="btn btn--reject" type="submit">Refuser</button>
                        </form>

                        <!-- Affecter à un employé -->
                        <form method="post" action="<?= e(BASE_URL) ?>/admin/commandes/affecter">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                            <label class="sr-only" for="emp-<?= (int) $o['id'] ?>">Employé</label>
                            <select class="select" id="emp-<?= (int) $o['id'] ?>" name="employee_id" required>
                                <option value="">— Employé —</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= (int) $emp['id'] ?>"><?= e($emp['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn--assign" type="submit">Affecter</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ============ Vue d'ensemble de toutes les commandes ============ -->
        <h2 class="adm__h2">Toutes les commandes</h2>

        <?php if (empty($allOrders)): ?>
            <p class="empty">Aucune commande pour le moment.</p>
        <?php else: ?>
            <div class="table__scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Client</th>
                            <th>Service</th>
                            <th>Statut</th>
                            <th>Budget</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allOrders as $o): ?>
                            <tr>
                                <td><?= e($o['code']) ?></td>
                                <td><?= e($o['client_name']) ?></td>
                                <td><?= e($o['service_name']) ?></td>
                                <td>
                                    <span class="badge badge--<?= e($o['status']) ?>">
                                        <?= e($statusLabels[$o['status']] ?? $o['status']) ?>
                                    </span>
                                </td>
                                <td><?= e($fmtBudget($o['budget'])) ?></td>
                                <td><?= e($fmtDate($o['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    /* Étiquette réservée aux lecteurs d'écran (accessibilité). */
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
        overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
