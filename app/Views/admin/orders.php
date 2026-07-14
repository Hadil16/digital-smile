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
?>
        <?php if (!empty($flash)): ?>
            <p class="adm-flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

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
            <div class="adm-card">
                <div class="adm-table__scroll">
                    <table class="adm-table">
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
                                <tr>
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

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
