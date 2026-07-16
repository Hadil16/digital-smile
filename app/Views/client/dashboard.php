<?php
/**
 * app/Views/client/dashboard.php
 * -----------------------------------------------------------------
 * Tableau de bord client — coquille A2 + cartes KPI + liste des
 * commandes. Variable fournie par ClientController : $orders.
 * PRÉSENTATION UNIQUEMENT (le nombre de factures est lu via une méthode
 * EXISTANTE du modèle, même approche que la cloche du header).
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// Libellés FR + tonalité de la pastille, par statut.
$statusMeta = [
    'pending'     => ['En attente', 'violet'],
    'approved'    => ['Acceptée',   'violet'],
    'in_progress' => ['En cours',   'amber'],
    'delivered'   => ['Livrée',     'green'],
    'completed'   => ['Terminée',   'green'],
    'rejected'    => ['Refusée',    'red'],
    'cancelled'   => ['Annulée',    'muted'],
];
$fmtDate = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';

// Prénom pour la salutation.
$parts     = array_values(array_filter(preg_split('/\s+/', trim($_SESSION['name'] ?? ''))));
$firstName = $parts[0] ?? 'client';

// KPI — uniquement des données réelles.
$inProgress = 0;
$delivered  = 0;
foreach ($orders as $o) {
    if ($o['status'] === 'in_progress') { $inProgress++; }
    if (in_array($o['status'], ['delivered', 'completed'], true)) { $delivered++; }
}
// Factures du client : nombre + total facturé (méthodes du modèle, affichage seul).
$clientUid    = (int) ($_SESSION['user_id'] ?? 0);
$invoiceModel = new Invoice();
$invoiceCount = count($invoiceModel->allForClient($clientUid));
$totalBilled  = $invoiceModel->sumTtcForClient($clientUid);
$fmtMoney     = fn($m) => number_format((float) $m, 2, ',', ' ') . ' DZD';

$kpis = [
    ['En cours',       $inProgress,             '🚧', 'amber',  'Projets en cours',  false],
    ['Livrées',        $delivered,              '🏁', 'green',  'Livrées et terminées', false],
    ['Factures',       $invoiceCount,           '🧾', 'violet', 'Factures émises',   false],
    ['Total facturé',  $fmtMoney($totalBilled), '💰', 'green',  'Montant TTC cumulé', true],
];

// Coquille client commune (sidebar + entête).
$clientActive = 'commandes';
$pageTitle    = 'Bonjour, ' . $firstName . ' 👋';
$pageSubtitle = 'Suivez vos demandes et vos livrables';
require ROOT_PATH . '/app/Views/partials/client-sidebar.php';
?>
        <!-- Cartes KPI (données réelles uniquement) -->
        <section class="adm__kpis" aria-label="Indicateurs de mes commandes">
            <?php foreach ($kpis as [$lbl, $val, $ico, $tone, $cap, $isMoney]): ?>
                <article class="adm-kpi adm-kpi--<?= $tone ?>">
                    <div class="adm-kpi__top">
                        <span class="adm-kpi__label"><?= e($lbl) ?></span>
                        <span class="adm-kpi__ico" aria-hidden="true"><?= $ico ?></span>
                    </div>
                    <p class="adm-kpi__num<?= $isMoney ? ' adm-kpi__num--sm' : '' ?>"><?= $isMoney ? e($val) : (int) $val ?></p>
                    <p class="adm-kpi__cap"><?= e($cap) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- En-tête de section + CTA nouvelle demande -->
        <div class="cli-bar">
            <h2 class="adm-section" style="margin:0">Mes demandes</h2>
            <a class="adm-btn adm-btn--primary" href="<?= e(BASE_URL) ?>/client/nouvelle-demande">+ Nouvelle demande</a>
        </div>

        <?php if (empty($orders)): ?>
            <div class="adm-empty">
                <p style="margin:0 0 16px">Aucune demande pour le moment.</p>
                <a class="adm-btn adm-btn--primary" href="<?= e(BASE_URL) ?>/client/nouvelle-demande">Créer ma première demande</a>
            </div>
        <?php else: ?>
            <div class="adm-card">
                <div class="adm-table__scroll">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Service</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <?php [$lbl, $tone] = $statusMeta[$o['status']] ?? [$o['status'], 'muted']; ?>
                                <tr>
                                    <td class="adm-table__num"><?= e($o['code']) ?></td>
                                    <td><?= e($o['service_name']) ?></td>
                                    <td><span class="adm-pill adm-pill--<?= $tone ?>"><?= e($lbl) ?></span></td>
                                    <td><?= e($fmtDate($o['created_at'])) ?></td>
                                    <td>
                                        <a class="adm-btn adm-btn--ghost"
                                           href="<?= e(BASE_URL) ?>/client/commande/<?= e(rawurlencode($o['code'])) ?>"
                                           aria-label="Voir la commande <?= e($o['code']) ?>">Voir</a>
                                    </td>
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
    /* Barre en-tête de section + CTA (auto-portée). */
    .cli-bar { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin: 30px 0 14px; }
    .cli-bar .adm-section { margin: 0; }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
