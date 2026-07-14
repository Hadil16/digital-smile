<?php
/**
 * app/Views/client/order-detail.php
 * -----------------------------------------------------------------
 * Détail d'une commande côté client (coquille A2) : infos, frise de
 * statut (stepper), et — si livrée — téléchargement + confirmation.
 * Variables : $order, $deliverable, $flash, $invoiceNumber.
 * Les actions (télécharger / confirmer) restent STRICTEMENT inchangées.
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

$statusMeta = [
    'pending'     => ['En attente', 'violet'],
    'approved'    => ['Acceptée',   'violet'],
    'in_progress' => ['En cours',   'amber'],
    'delivered'   => ['Livrée',     'green'],
    'completed'   => ['Terminée',   'green'],
    'rejected'    => ['Refusée',    'red'],
    'cancelled'   => ['Annulée',    'muted'],
];
[$stLabel, $stTone] = $statusMeta[$order['status']] ?? [$order['status'], 'muted'];
$fmtBudget = fn($b) => $b !== null ? number_format((float) $b, 0, ',', ' ') . ' DZD' : '—';
$fmtDate   = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
$orderUrl  = e(BASE_URL) . '/client/commande/' . rawurlencode($order['code']);

// Coquille client commune (sidebar + entête).
$clientActive = 'commandes';
$pageTitle    = $order['code'];
$pageSubtitle = 'Détail de la commande';
require ROOT_PATH . '/app/Views/partials/client-sidebar.php';
?>
        <?php if (!empty($flash)): ?>
            <p class="adm-flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <div class="cli-head">
            <span class="adm-pill adm-pill--<?= $stTone ?>"><?= e($stLabel) ?></span>
            <a class="cli-head__back" href="<?= e(BASE_URL) ?>/client">← Mes demandes</a>
        </div>

        <!-- Informations -->
        <div class="adm-card">
            <div class="adm-order__meta">
                <div><span>Service</span><?= e($order['service_name']) ?></div>
                <div><span>Projet</span><?= e($order['project_name']) ?></div>
                <div><span>Budget</span><?= e($fmtBudget($order['budget'])) ?></div>
                <div><span>Échéance</span><?= e($fmtDate($order['deadline'])) ?></div>
                <div><span>Demandée le</span><?= e($fmtDate($order['created_at'])) ?></div>
            </div>
        </div>

        <!-- Frise de statut (stepper) -->
        <div class="adm-card">
            <?php if ($isCancelled): ?>
                <p class="cli-cancel">
                    Cette commande est <strong><?= e($stLabel) ?></strong>.
                </p>
            <?php else: ?>
                <ol class="cli-step">
                    <?php foreach ($steps as $i => $key): ?>
                        <?php
                        $cls = '';
                        if ($currentIndex !== false && $i < $currentIndex)      $cls = 'is-done';
                        elseif ($currentIndex !== false && $i === $currentIndex) $cls = 'is-current';
                        ?>
                        <li class="cli-step__item <?= $cls ?>">
                            <span class="cli-step__line" aria-hidden="true"></span>
                            <span class="cli-step__dot" aria-hidden="true"></span>
                            <span class="cli-step__label"><?= e($flow[$key]) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>

        <!-- Actions livrable (formulaires conservés à l'identique) -->
        <?php if ($order['status'] === 'delivered'): ?>
            <div class="cli-actions">
                <?php if (!empty($deliverable)): ?>
                    <a class="adm-btn adm-btn--assign" href="<?= $orderUrl ?>/telecharger">
                        Télécharger le livrable
                    </a>
                <?php endif; ?>
                <form method="post" action="<?= $orderUrl ?>/confirmer">
                    <?= csrf_field() ?>
                    <button class="adm-btn adm-btn--primary" type="submit">Confirmer la réception</button>
                </form>
            </div>
        <?php elseif ($order['status'] === 'completed'): ?>
            <div class="cli-actions">
                <span class="cli-done">Terminé ✓</span>
                <?php if (!empty($deliverable)): ?>
                    <a class="adm-btn adm-btn--assign" href="<?= $orderUrl ?>/telecharger">
                        Télécharger le livrable
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($invoiceNumber)): ?>
            <!-- Facture disponible : lien vers la version imprimable / PDF. -->
            <div class="cli-actions">
                <a class="adm-btn adm-btn--ghost"
                   href="<?= e(BASE_URL) ?>/client/facture/<?= e(rawurlencode($invoiceNumber)) ?>/imprimer">
                    Télécharger / Imprimer la facture
                </a>
            </div>
        <?php endif; ?>
    </main>
</div>

<style>
    /* En-tête (pastille + retour) + actions (auto-portées, tokens). */
    .cli-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin: 0 0 6px; }
    .cli-head__back { color: var(--color-primary-light); font-size: 14px; font-weight: 600; text-decoration: none; }
    .cli-head__back:hover { text-decoration: underline; }
    .cli-actions { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin: 16px 0 0; }
    .cli-actions form { margin: 0; }
    .cli-done { display: inline-flex; align-items: center; gap: 8px; color: var(--color-success); font-weight: 600; font-size: 15px; }
    .cli-cancel { background: rgba(179, 38, 30, .12); color: var(--color-danger); border-radius: 12px; padding: 12px 14px; font-size: 14px; margin: 0; }

    /* Stepper : lime pour les étapes franchies, anneau violet pour l'étape en cours. */
    .cli-step { display: flex; gap: 6px; margin: 0; list-style: none; padding: 6px 0 0; }
    .cli-step__item { flex: 1; text-align: center; font-size: 12px; color: var(--color-muted); position: relative; }
    .cli-step__dot { display: block; width: 20px; height: 20px; border-radius: 999px; margin: 0 auto 10px;
        background: var(--color-surface-alt); border: 3px solid var(--color-border); box-sizing: border-box; }
    .cli-step__line { position: absolute; top: 8px; left: -50%; width: 100%; height: 3px;
        background: var(--color-border); z-index: 0; }
    .cli-step__item:first-child .cli-step__line { display: none; }
    .cli-step__label { position: relative; z-index: 1; }
    .cli-step__item.is-done .cli-step__dot,
    .cli-step__item.is-done .cli-step__line { background: var(--color-accent); border-color: var(--color-accent); }
    .cli-step__item.is-current .cli-step__dot { background: var(--color-surface); border-color: var(--color-primary-light); }
    .cli-step__item.is-current { color: var(--color-primary-light); font-weight: 600; }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
