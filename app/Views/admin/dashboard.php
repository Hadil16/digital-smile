<?php
/**
 * app/Views/admin/dashboard.php
 * -----------------------------------------------------------------
 * Tableau de bord admin — shell SaaS premium (sidebar + contenu).
 * PRÉSENTATION UNIQUEMENT : mêmes données que le contrôleur.
 * Variables : $statusCounts, $totalOrders, $totalClients, $totalEmployees,
 *             $totalInvoices, $monthly, $topServices, $notifCount (header).
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// Prénom de l'admin (pour la salutation de l'entête).
$adminName = $_SESSION['name'] ?? '';
$nameParts = array_values(array_filter(preg_split('/\s+/', trim($adminName))));
$firstName = $nameParts[0] ?? 'Admin';

// KPI — UNIQUEMENT des données déjà fournies (pas de chiffre inventé).
$kpis = [
    ['label' => 'Commandes',  'value' => (int) ($totalOrders ?? 0),            'icon' => '📋',  'tone' => 'violet', 'cap' => 'Total des demandes'],
    ['label' => 'En attente', 'value' => (int) ($statusCounts['pending'] ?? 0),'icon' => '⏳',  'tone' => 'amber',  'cap' => 'À traiter'],
    ['label' => 'Clients',    'value' => (int) ($totalClients ?? 0),           'icon' => '👥',  'tone' => 'green',  'cap' => 'Comptes clients'],
    ['label' => 'Employés',   'value' => (int) ($totalEmployees ?? 0),         'icon' => '🧑‍💼', 'tone' => 'violet', 'cap' => 'Équipe active'],
    ['label' => 'Factures',   'value' => (int) ($totalInvoices ?? 0),          'icon' => '🧾',  'tone' => 'violet', 'cap' => 'Émises'],
];

// Données graphiques (inchangées) — lues par le script Chart.js du footer.
$statusFrLabels = ['En attente', 'Acceptée', 'En cours', 'Livrée', 'Terminée', 'Refusée'];
$statusValues   = [
    (int) ($statusCounts['pending'] ?? 0),   (int) ($statusCounts['approved'] ?? 0),
    (int) ($statusCounts['in_progress'] ?? 0), (int) ($statusCounts['delivered'] ?? 0),
    (int) ($statusCounts['completed'] ?? 0), (int) ($statusCounts['rejected'] ?? 0),
];
$monthly     = $monthly     ?? ['labels' => [], 'values' => []];
$topServices = $topServices ?? ['labels' => [], 'values' => []];
$chartData = ['monthly' => $monthly, 'status' => ['labels' => $statusFrLabels, 'values' => $statusValues], 'services' => $topServices];
$pair = fn(array $l, array $v) => $l ? implode(', ', array_map(fn($a, $b) => "$a : $b", $l, $v)) : 'aucune donnée';
$sumMonthly = $pair($monthly['labels'], $monthly['values']);
$sumStatus  = $pair($statusFrLabels, $statusValues);
$sumServices = $pair($topServices['labels'], $topServices['values']);

// 5 dernières commandes — via une méthode EXISTANTE du modèle (affichage seul,
// aucune modif du contrôleur/modèle ; même approche que la cloche du header).
$recent = array_slice((new Order())->allWithStatus(), 0, 5);
$statusMeta = [
    'pending' => ['En attente', 'violet'], 'approved' => ['Acceptée', 'violet'],
    'in_progress' => ['En cours', 'amber'], 'delivered' => ['Livrée', 'green'],
    'completed' => ['Terminée', 'green'], 'rejected' => ['Refusée', 'red'], 'cancelled' => ['Annulée', 'muted'],
];

// Coquille admin commune (sidebar + entête). Lien actif + titre de la page.
$adminActive  = 'dashboard';
$pageTitle    = 'Bonjour, ' . $firstName . ' 👋';
$pageSubtitle = "Voici l'activité de votre agence aujourd'hui";
require ROOT_PATH . '/app/Views/partials/admin-sidebar.php';
?>
        <!-- Cartes KPI (données réelles uniquement) -->
        <section class="adm__kpis" aria-label="Indicateurs clés">
            <?php foreach ($kpis as $k): ?>
                <article class="adm-kpi adm-kpi--<?= $k['tone'] ?>">
                    <div class="adm-kpi__top">
                        <span class="adm-kpi__label"><?= e($k['label']) ?></span>
                        <span class="adm-kpi__ico" aria-hidden="true"><?= $k['icon'] ?></span>
                    </div>
                    <p class="adm-kpi__num"><?= (int) $k['value'] ?></p>
                    <p class="adm-kpi__cap"><?= e($k['cap']) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- Graphiques (canvas + données Chart.js inchangés) -->
        <section class="adm__charts" aria-label="Graphiques de l'activité">
            <div class="adm-card">
                <h2 class="adm-card__title">Commandes par mois</h2>
                <div class="adm-card__canvas">
                    <canvas id="chartMonthly" role="img" aria-label="Commandes par mois — <?= e($sumMonthly) ?>"><?= e($sumMonthly) ?></canvas>
                </div>
            </div>
            <div class="adm-card">
                <h2 class="adm-card__title">Répartition par statut</h2>
                <div class="adm-card__canvas">
                    <canvas id="chartStatus" role="img" aria-label="Répartition par statut — <?= e($sumStatus) ?>"><?= e($sumStatus) ?></canvas>
                </div>
            </div>
            <div class="adm-card">
                <h2 class="adm-card__title">Top 5 services</h2>
                <div class="adm-card__canvas">
                    <canvas id="chartServices" role="img" aria-label="Top 5 des services — <?= e($sumServices) ?>"><?= e($sumServices) ?></canvas>
                </div>
            </div>
        </section>

        <!-- Données PHP -> JS (identiques : le script Chart.js du footer les lit) -->
        <script>
            window.DS_CHARTS = <?= json_encode(
                $chartData,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
            ) ?>;
        </script>

        <!-- Dernières commandes -->
        <section class="adm-card" aria-label="Dernières commandes">
            <div class="adm__recent-head">
                <h2 class="adm-card__title" style="margin:0">Dernières commandes</h2>
                <a class="adm__all" href="<?= e(BASE_URL) ?>/admin/commandes">Tout voir →</a>
            </div>
            <?php if (empty($recent)): ?>
                <p class="adm__empty">Aucune commande pour le moment.</p>
            <?php else: ?>
                <div class="adm-table__scroll">
                    <table class="adm-table">
                        <thead>
                            <tr><th>N°</th><th>Client</th><th>Service</th><th>Statut</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $o): ?>
                                <?php [$lbl, $tone] = $statusMeta[$o['status']] ?? [$o['status'], 'muted']; ?>
                                <tr>
                                    <td class="adm-table__num"><?= e($o['code']) ?></td>
                                    <td><?= e($o['client_name']) ?></td>
                                    <td><?= e($o['service_name']) ?></td>
                                    <td><span class="adm-pill adm-pill--<?= $tone ?>"><?= e($lbl) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
