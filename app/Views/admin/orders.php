<?php
/**
 * app/Views/admin/orders.php
 * -----------------------------------------------------------------
 * Revue des demandes (admin) : demandes en attente (avec actions
 * approuver / refuser / affecter) + vue d'ensemble de toutes les
 * commandes. Variables : $pending, $approvedUnassigned, $allOrders,
 * $employees, $flash.
 * PRÉSENTATION UNIQUEMENT — logique, formulaires et routes inchangés.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// Libellés FR + tonalité de la pastille, par statut (cohérent avec le dashboard).
$statusMeta = [
    'pending'     => ['En attente', 'violet'],
    'approved'    => ['Acceptée',   'violet'],
    'in_progress' => ['En cours',   'amber'],
    'delivered'   => ['Livrée',     'green'],
    'completed'   => ['Terminée',   'green'],
    'rejected'    => ['Refusée',    'red'],
    'cancelled'   => ['Annulée',    'muted'],
];

// Petit utilitaire d'affichage (budget / date), pour ne pas se répéter.
$fmtBudget = fn($b) => $b !== null ? number_format((float) $b, 0, ',', ' ') . ' DZD' : '—';
$fmtDate   = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';

// Coquille admin commune (sidebar + entête).
$adminActive  = 'commandes';
$pageTitle    = 'Gestion des demandes';
$pageSubtitle = 'Validez, refusez et affectez les commandes reçues';
require ROOT_PATH . '/app/Views/partials/admin-sidebar.php';

// Cartes KPI par statut (comptes réels fournis par countByStatus()).
$orderKpis = [
    ['En attente', (int) ($statusCounts['pending'] ?? 0),     '⏳', 'amber'],
    ['Approuvées', (int) ($statusCounts['approved'] ?? 0),    '✅', 'violet'],
    ['En cours',   (int) ($statusCounts['in_progress'] ?? 0), '🚧', 'amber'],
    ['Terminées',  (int) ($statusCounts['completed'] ?? 0),   '🏁', 'green'],
];
?>
        <?php if (!empty($flash)): ?>
            <p class="adm-flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <!-- Cartes KPI par statut (données réelles) -->
        <section class="adm__kpis" aria-label="Commandes par statut">
            <?php foreach ($orderKpis as [$lbl, $val, $ico, $tone]): ?>
                <article class="adm-kpi adm-kpi--<?= $tone ?>">
                    <div class="adm-kpi__top">
                        <span class="adm-kpi__label"><?= e($lbl) ?></span>
                        <span class="adm-kpi__ico" aria-hidden="true"><?= $ico ?></span>
                    </div>
                    <p class="adm-kpi__num"><?= $val ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- ============ Demandes en attente (avec actions) ============ -->
        <h2 class="adm-section">Demandes en attente</h2>

        <?php if (empty($pending)): ?>
            <p class="adm-empty">Aucune demande en attente.</p>
        <?php else: ?>
            <?php foreach ($pending as $o): ?>
                <article class="adm-order">
                    <div class="adm-order__head">
                        <span class="adm-order__code"><?= e($o['code']) ?></span>
                        <span class="adm-pill adm-pill--violet">En attente</span>
                    </div>

                    <div class="adm-order__meta">
                        <div><span>Client</span><?= e($o['client_name']) ?></div>
                        <div><span>Service</span><?= e($o['service_name']) ?></div>
                        <div><span>Projet</span><?= e($o['project_name']) ?></div>
                        <div><span>Budget</span><?= e($fmtBudget($o['budget'])) ?></div>
                        <div><span>Échéance</span><?= e($fmtDate($o['deadline'])) ?></div>
                        <div><span>Reçue le</span><?= e($fmtDate($o['created_at'])) ?></div>
                    </div>

                    <div class="adm-order__actions">
                        <!-- Approuver -->
                        <form method="post" action="<?= e(BASE_URL) ?>/admin/commandes/approuver">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                            <button class="adm-btn adm-btn--primary" type="submit">Approuver</button>
                        </form>

                        <!-- Refuser -->
                        <form method="post" action="<?= e(BASE_URL) ?>/admin/commandes/refuser">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                            <button class="adm-btn adm-btn--danger" type="submit">Refuser</button>
                        </form>

                        <!-- Affecter à un employé -->
                        <form method="post" action="<?= e(BASE_URL) ?>/admin/commandes/affecter">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                            <label class="sr-only" for="emp-<?= (int) $o['id'] ?>">Employé</label>
                            <select class="adm-select" id="emp-<?= (int) $o['id'] ?>" name="employee_id" required>
                                <option value="">— Employé —</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= (int) $emp['id'] ?>"><?= e($emp['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="adm-btn adm-btn--assign" type="submit">Affecter</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ============ Commandes acceptées à affecter ============ -->
        <h2 class="adm-section">À affecter</h2>

        <?php if (empty($approvedUnassigned)): ?>
            <p class="adm-empty">Aucune commande à affecter.</p>
        <?php else: ?>
            <?php foreach ($approvedUnassigned as $o): ?>
                <article class="adm-order">
                    <div class="adm-order__head">
                        <span class="adm-order__code"><?= e($o['code']) ?></span>
                        <span class="adm-pill adm-pill--violet">Acceptée</span>
                    </div>

                    <div class="adm-order__meta">
                        <div><span>Client</span><?= e($o['client_name']) ?></div>
                        <div><span>Service</span><?= e($o['service_name']) ?></div>
                        <div><span>Projet</span><?= e($o['project_name']) ?></div>
                        <div><span>Budget</span><?= e($fmtBudget($o['budget'])) ?></div>
                        <div><span>Échéance</span><?= e($fmtDate($o['deadline'])) ?></div>
                        <div><span>Reçue le</span><?= e($fmtDate($o['created_at'])) ?></div>
                    </div>

                    <div class="adm-order__actions">
                        <!-- Affecter à un employé (même formulaire que pour les demandes en attente) -->
                        <form method="post" action="<?= e(BASE_URL) ?>/admin/commandes/affecter">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                            <label class="sr-only" for="emp-<?= (int) $o['id'] ?>">Employé</label>
                            <select class="adm-select" id="emp-<?= (int) $o['id'] ?>" name="employee_id" required>
                                <option value="">— Employé —</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= (int) $emp['id'] ?>"><?= e($emp['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="adm-btn adm-btn--assign" type="submit">Affecter</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ============ Vue d'ensemble de toutes les commandes ============ -->
        <h2 class="adm-section">Toutes les commandes</h2>

        <?php if (empty($allOrders)): ?>
            <p class="adm-empty">Aucune commande pour le moment.</p>
        <?php else: ?>
            <!-- Filtre client (JS) : n'affiche que les lignes du statut choisi. -->
            <div class="adm-filter" role="group" aria-label="Filtrer les commandes par statut">
                <button type="button" class="adm-filter__btn is-active" data-filter="all">Toutes</button>
                <button type="button" class="adm-filter__btn" data-filter="pending">En attente</button>
                <button type="button" class="adm-filter__btn" data-filter="in_progress">En cours</button>
                <button type="button" class="adm-filter__btn" data-filter="completed">Terminées</button>
            </div>
            <div class="adm-card">
                <div class="adm-table__scroll">
                    <table class="adm-table" id="ordersTable">
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
                                <?php [$lbl, $tone] = $statusMeta[$o['status']] ?? [$o['status'], 'muted']; ?>
                                <tr data-status="<?= e($o['status']) ?>">
                                    <td class="adm-table__num"><?= e($o['code']) ?></td>
                                    <td><?= e($o['client_name']) ?></td>
                                    <td><?= e($o['service_name']) ?></td>
                                    <td><span class="adm-pill adm-pill--<?= $tone ?>"><?= e($lbl) ?></span></td>
                                    <td><?= e($fmtBudget($o['budget'])) ?></td>
                                    <td><?= e($fmtDate($o['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<style>
    /* Barre de filtre (auto-portée, tokens clair/sombre). */
    .adm-filter { display: flex; flex-wrap: wrap; gap: 8px; margin: 0 0 14px; }
    .adm-filter__btn { cursor: pointer; border: 1px solid var(--color-border); background: var(--color-surface);
        color: var(--color-muted); font-family: inherit; font-size: 13px; font-weight: 600;
        padding: 8px 16px; border-radius: 999px; transition: background var(--transition), color var(--transition), border-color var(--transition); }
    .adm-filter__btn:hover { border-color: var(--color-primary-light); color: var(--color-text); }
    .adm-filter__btn.is-active { background: linear-gradient(135deg, #4A3F9E, #6b5fd4); border-color: transparent; color: #fff; }
</style>

<script>
    // Filtre des commandes SANS rechargement : on masque les lignes dont le
    // statut ne correspond pas au bouton actif (data-status / data-filter).
    (function () {
        var bar = document.querySelector('.adm-filter');
        var table = document.getElementById('ordersTable');
        if (!bar || !table) return;

        bar.addEventListener('click', function (ev) {
            var btn = ev.target.closest('.adm-filter__btn');
            if (!btn) return;

            bar.querySelectorAll('.adm-filter__btn').forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');

            var want = btn.getAttribute('data-filter');
            table.querySelectorAll('tbody tr').forEach(function (row) {
                var show = (want === 'all') || (row.getAttribute('data-status') === want);
                row.hidden = !show;
            });
        });
    })();
</script>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
