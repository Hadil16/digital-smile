<?php
/**
 * app/Views/client/order-detail.php
 * -----------------------------------------------------------------
 * Détail d'une commande côté client : infos, frise de statut, et —
 * si la commande est livrée — téléchargement du livrable + confirmation
 * de réception. Variables : $order, $deliverable, $flash.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// Frise du parcours normal d'une commande (dans l'ordre).
$flow = [
    'pending'     => 'Demandée',
    'approved'    => 'Acceptée',
    'in_progress' => 'En cours',
    'delivered'   => 'Livrée',
    'completed'   => 'Terminée',
];
$steps        = array_keys($flow);
$currentIndex = array_search($order['status'], $steps, true); // false si refusée/annulée
$isCancelled  = in_array($order['status'], ['rejected', 'cancelled'], true);

$statusLabels = $flow + ['rejected' => 'Refusée', 'cancelled' => 'Annulée'];
$fmtBudget = fn($b) => $b !== null ? number_format((float) $b, 0, ',', ' ') . ' DZD' : '—';
$fmtDate   = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
$orderUrl  = e(BASE_URL) . '/client/commande/' . rawurlencode($order['code']);
?>
<style>
    /* Styles auto-portés du détail de commande (couleurs de marque). */
    .det { --violet: #4A3F9E; --lime: #8BC63F;
        min-height: 70vh; padding: 96px 24px 64px; font-family: 'Inter', system-ui, sans-serif; }
    .intro { display: none !important; } /* pas d'intro flash hors accueil */
    .det__wrap { max-width: 760px; margin: 0 auto; }
    .det__back { display: inline-block; color: var(--violet); font-size: 14px; font-weight: 600;
        text-decoration: none; margin: 0 0 20px; }
    .det__head { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin: 0 0 6px; }
    .det__code { font-family: 'Poppins', system-ui, sans-serif; font-weight: 800;
        font-size: clamp(24px, 4vw, 32px); color: var(--violet); margin: 0; }
    .flash { background: #eef7e0; color: #3B6D11; border: 1px solid #cfe6a8;
        border-radius: 12px; padding: 12px 16px; margin: 18px 0 0; font-size: 14px; }

    .card { background: #fff; border: 1px solid #eee; border-radius: 16px; padding: 28px;
        margin: 20px 0 0; box-shadow: 0 12px 40px rgba(74, 63, 158, .06); }
    .meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px 20px; font-size: 14px; }
    .meta span { display: block; color: #999; font-size: 12px; }

    /* Frise de statut */
    .timeline { display: flex; gap: 6px; margin: 8px 0 0; list-style: none; padding: 0; }
    .timeline li { flex: 1; text-align: center; font-size: 12px; color: #999; position: relative; }
    .timeline .dot { width: 18px; height: 18px; border-radius: 999px; background: #e3e3ea;
        margin: 0 auto 8px; border: 3px solid #e3e3ea; }
    .timeline .line { position: absolute; top: 9px; left: -50%; width: 100%; height: 3px;
        background: #e3e3ea; z-index: -1; }
    .timeline li:first-child .line { display: none; }
    .timeline li.is-done  .dot,
    .timeline li.is-done  .line { background: var(--lime); border-color: var(--lime); }
    .timeline li.is-current .dot { background: #fff; border-color: var(--violet); }
    .timeline li.is-current { color: var(--violet); font-weight: 600; }
    .cancel-note { background: #fdecec; color: #b3261e; border-radius: 10px;
        padding: 12px 14px; font-size: 14px; }

    .actions { display: flex; flex-wrap: wrap; gap: 12px; align-items: center;
        margin: 22px 0 0; }
    .actions form { margin: 0; }
    .btn { display: inline-block; border: 0; cursor: pointer; text-decoration: none; font-weight: 600;
        font-size: 14px; padding: 12px 22px; border-radius: 999px; font-family: inherit;
        transition: background .2s, color .2s; }
    .btn--download { background: var(--violet); color: #fff; }
    .btn--download:hover { background: #372f78; }
    .btn--confirm { background: var(--lime); color: #23400a; }
    .btn--confirm:hover { background: #7cb034; }
    .done-note { display: inline-flex; align-items: center; gap: 8px; color: #3B6D11;
        font-weight: 600; font-size: 15px; }

    .badge { display: inline-block; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 999px; }
    .badge--pending     { background: #fff7e6; color: #b8860b; }
    .badge--approved    { background: #ece9fb; color: #4A3F9E; }
    .badge--in_progress { background: #e6f0fc; color: #1e6fd9; }
    .badge--delivered   { background: #e3f7f4; color: #0d9488; }
    .badge--completed   { background: #eef7e0; color: #3B6D11; }
    .badge--rejected    { background: #fdecec; color: #b3261e; }
    .badge--cancelled   { background: #eee; color: #666; }
</style>

<main class="det">
    <div class="det__wrap">
        <a class="det__back" href="<?= e(BASE_URL) ?>/client">← Retour à mes demandes</a>

        <div class="det__head">
            <h1 class="det__code"><?= e($order['code']) ?></h1>
            <span class="badge badge--<?= e($order['status']) ?>">
                <?= e($statusLabels[$order['status']] ?? $order['status']) ?>
            </span>
        </div>

        <?php if (!empty($flash)): ?>
            <p class="flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <!-- Informations -->
        <div class="card">
            <div class="meta">
                <div><span>Service</span><?= e($order['service_name']) ?></div>
                <div><span>Projet</span><?= e($order['project_name']) ?></div>
                <div><span>Budget</span><?= e($fmtBudget($order['budget'])) ?></div>
                <div><span>Échéance</span><?= e($fmtDate($order['deadline'])) ?></div>
                <div><span>Demandée le</span><?= e($fmtDate($order['created_at'])) ?></div>
            </div>
        </div>

        <!-- Frise de statut -->
        <div class="card">
            <?php if ($isCancelled): ?>
                <p class="cancel-note">
                    Cette commande est <strong><?= e($statusLabels[$order['status']]) ?></strong>.
                </p>
            <?php else: ?>
                <ol class="timeline">
                    <?php foreach ($steps as $i => $key): ?>
                        <?php
                        $cls = '';
                        if ($currentIndex !== false && $i < $currentIndex)      $cls = 'is-done';
                        elseif ($currentIndex !== false && $i === $currentIndex) $cls = 'is-current';
                        ?>
                        <li class="<?= $cls ?>">
                            <span class="line"></span>
                            <span class="dot" aria-hidden="true"></span>
                            <?= e($flow[$key]) ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>

        <!-- Actions livrable -->
        <?php if ($order['status'] === 'delivered'): ?>
            <div class="actions">
                <?php if (!empty($deliverable)): ?>
                    <a class="btn btn--download" href="<?= $orderUrl ?>/telecharger">
                        Télécharger le livrable
                    </a>
                <?php endif; ?>
                <form method="post" action="<?= $orderUrl ?>/confirmer">
                    <?= csrf_field() ?>
                    <button class="btn btn--confirm" type="submit">Confirmer la réception</button>
                </form>
            </div>
        <?php elseif ($order['status'] === 'completed'): ?>
            <div class="actions">
                <span class="done-note">Terminé ✓</span>
                <?php if (!empty($deliverable)): ?>
                    <a class="btn btn--download" href="<?= $orderUrl ?>/telecharger">
                        Télécharger le livrable
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($invoiceNumber)): ?>
            <!-- Facture disponible : lien vers la version imprimable / PDF. -->
            <div class="actions">
                <a class="btn btn--download"
                   href="<?= e(BASE_URL) ?>/client/facture/<?= e(rawurlencode($invoiceNumber)) ?>/imprimer">
                    Télécharger / Imprimer la facture
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
