<?php
/**
 * app/Views/client/dashboard.php
 * -----------------------------------------------------------------
 * Tableau de bord client : bouton "nouvelle demande" + liste de ses
 * commandes. Variables fournies par ClientController : $orders.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// Libellés FR des statuts (le style du badge vient de la classe .badge--<statut>).
$statusLabels = [
    'pending'     => 'En attente',
    'approved'    => 'Acceptée',
    'rejected'    => 'Refusée',
    'in_progress' => 'En cours',
    'delivered'   => 'Livrée',
    'completed'   => 'Terminée',
    'cancelled'   => 'Annulée',
];
?>
<style>
    /* Styles auto-portés du tableau de bord (couleurs de marque). */
    .dash { --violet: #4A3F9E; --lime: #8BC63F;
        min-height: 70vh; padding: 96px 24px 64px; font-family: 'Inter', system-ui, sans-serif; }
    .intro { display: none !important; } /* pas d'intro flash hors accueil */
    .dash__wrap { max-width: 940px; margin: 0 auto; }
    .dash__head { display: flex; align-items: center; justify-content: space-between; gap: 16px;
        flex-wrap: wrap; margin: 0 0 32px; }
    .dash__role { display: inline-block; background: var(--violet); color: #fff; font-size: 13px;
        font-weight: 600; padding: 6px 14px; border-radius: 999px; margin: 0 0 10px; }
    .dash__title { font-family: 'Poppins', system-ui, sans-serif; font-weight: 800;
        font-size: clamp(24px, 4vw, 34px); color: var(--violet); margin: 0; }
    .dash__bar { display: flex; align-items: center; justify-content: space-between; gap: 16px;
        flex-wrap: wrap; margin: 0 0 18px; }
    .dash__h2 { font-family: 'Poppins', system-ui, sans-serif; font-size: 20px; color: #222; margin: 0; }
    .btn { display: inline-block; text-decoration: none; font-weight: 600; font-size: 14px;
        padding: 11px 22px; border-radius: 999px; transition: background .2s, color .2s; }
    .btn--primary { background: var(--violet); color: #fff; }
    .btn--primary:hover { background: var(--lime); }
    .btn--ghost { border: 1px solid #d5d5db; color: #444; }
    .btn--ghost:hover { border-color: var(--violet); color: var(--violet); }
    .dash__empty { background: #faf9ff; border: 1px dashed #d7d2f0; color: #666;
        border-radius: 14px; padding: 40px; text-align: center; font-size: 15px; }
    .table__scroll { overflow-x: auto; border: 1px solid #eee; border-radius: 14px; }
    .table { width: 100%; border-collapse: collapse; font-size: 14px; min-width: 640px; }
    .table th, .table td { text-align: left; padding: 14px 16px; border-bottom: 1px solid #f0f0f0; }
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

<main class="dash">
    <div class="dash__wrap">
        <header class="dash__head">
            <div>
                <p class="dash__role">Espace client</p>
                <h1 class="dash__title">Bonjour, <?= e($_SESSION['name'] ?? '') ?> 👋</h1>
            </div>
            <a class="btn btn--ghost" href="<?= e(BASE_URL) ?>/logout">Déconnexion</a>
        </header>

        <div class="dash__bar">
            <h2 class="dash__h2">Mes demandes</h2>
            <a class="btn btn--primary" href="<?= e(BASE_URL) ?>/client/nouvelle-demande">+ Nouvelle demande</a>
        </div>

        <?php if (empty($orders)): ?>
            <p class="dash__empty">Aucune demande pour le moment.</p>
        <?php else: ?>
            <div class="table__scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Service</th>
                            <th>Statut</th>
                            <th>Budget</th>
                            <th>Échéance</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><?= e($o['code']) ?></td>
                                <td><?= e($o['service_name']) ?></td>
                                <td>
                                    <span class="badge badge--<?= e($o['status']) ?>">
                                        <?= e($statusLabels[$o['status']] ?? $o['status']) ?>
                                    </span>
                                </td>
                                <td><?= $o['budget'] !== null
                                        ? e(number_format((float) $o['budget'], 0, ',', ' ')) . ' DZD'
                                        : '—' ?></td>
                                <td><?= $o['deadline']
                                        ? e(date('d/m/Y', strtotime($o['deadline'])))
                                        : '—' ?></td>
                                <td><?= e(date('d/m/Y', strtotime($o['created_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
