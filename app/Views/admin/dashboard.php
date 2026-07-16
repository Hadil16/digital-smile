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

// Granularité du graphique de chiffre d'affaires (jour/mois/année, défaut mois).
$g = in_array($_GET['g'] ?? '', ['jour', 'mois', 'annee'], true) ? $_GET['g'] : 'mois';

// Courbe « Chiffre d'affaires (TTC) » — à partir de $revenue (revenueByPeriod).
$revenue   = $revenue ?? [];
$revLabels = array_map(fn($r) => $r['period'], $revenue);
$revValues = array_map(fn($r) => (float) $r['ttc'], $revenue);

// Répartition des commandes par statut (donut).
$statusFrLabels = ['En attente', 'Acceptée', 'En cours', 'Livrée', 'Terminée', 'Refusée'];
$statusValues   = [
    (int) ($statusCounts['pending'] ?? 0),   (int) ($statusCounts['approved'] ?? 0),
    (int) ($statusCounts['in_progress'] ?? 0), (int) ($statusCounts['delivered'] ?? 0),
    (int) ($statusCounts['completed'] ?? 0), (int) ($statusCounts['rejected'] ?? 0),
];
$topServices = $topServices ?? ['labels' => [], 'values' => []];

// Données pour le script de graphiques auto-porté (rendu en bas de page).
$chartData = [
    'revenue'  => ['labels' => $revLabels, 'values' => $revValues],
    'status'   => ['labels' => $statusFrLabels, 'values' => $statusValues],
    'services' => $topServices,
];
$pair = fn(array $l, array $v) => $l ? implode(', ', array_map(fn($a, $b) => "$a : $b", $l, $v)) : 'aucune donnée';
$sumRevenue  = $pair($revLabels, $revValues);
$sumStatus   = $pair($statusFrLabels, $statusValues);
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

        <!-- Graphiques (rendus par le script auto-porté en bas de page) -->
        <section class="adm__charts" aria-label="Graphiques de l'activité">
            <div class="adm-card">
                <div class="adm__recent-head">
                    <h2 class="adm-card__title" style="margin:0">Chiffre d'affaires (TTC)</h2>
                    <div class="adm-gran" role="group" aria-label="Granularité">
                        <?php foreach (['jour' => 'Jour', 'mois' => 'Mois', 'annee' => 'Année'] as $key => $lbl): ?>
                            <a class="adm-gran__btn<?= $g === $key ? ' is-active' : '' ?>"
                               href="<?= e(BASE_URL) ?>/admin?g=<?= $key ?>"><?= $lbl ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="adm-card__canvas">
                    <canvas id="chartRevenue" role="img" aria-label="Chiffre d'affaires TTC — <?= e($sumRevenue) ?>"><?= e($sumRevenue) ?></canvas>
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

        <!-- Données PHP -> JS + rendu Chart.js AUTO-PORTÉ (libellés honnêtes). -->
        <script>
            window.DS_DASH = <?= json_encode(
                $chartData,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
            ) ?>;
            (function () {
                var d = window.DS_DASH; if (!d) return;
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
                s.onload = render; document.body.appendChild(s);
                function render() {
                    if (typeof Chart === 'undefined') return;
                    var violet = '#4A3F9E', lime = '#8BC63F';
                    var palette = ['#f0a500', '#4A3F9E', '#1e6fd9', '#0d9488', '#8BC63F', '#b3261e'];
                    var r = document.getElementById('chartRevenue');
                    if (r && d.revenue) new Chart(r, { type: 'line',
                        data: { labels: d.revenue.labels, datasets: [{ label: 'CA TTC (DZD)', data: d.revenue.values,
                            borderColor: violet, backgroundColor: 'rgba(74,63,158,.12)', fill: true, tension: .3, pointBackgroundColor: violet, pointRadius: 4 }] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
                    var st = document.getElementById('chartStatus');
                    if (st && d.status) new Chart(st, { type: 'doughnut',
                        data: { labels: d.status.labels, datasets: [{ data: d.status.values, backgroundColor: palette, borderColor: '#fff', borderWidth: 2 }] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 14 } } } } });
                    var sv = document.getElementById('chartServices');
                    if (sv && d.services) new Chart(sv, { type: 'bar',
                        data: { labels: d.services.labels, datasets: [{ label: 'Demandes', data: d.services.values, backgroundColor: lime, borderRadius: 6 }] },
                        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } } });
                }
            })();
        </script>

        <style>
            /* Sélecteur de granularité (auto-porté, tokens). */
            .adm-gran { display: inline-flex; gap: 4px; }
            .adm-gran__btn { font-size: 12px; font-weight: 600; color: var(--color-muted);
                border: 1px solid var(--color-border); border-radius: 999px; padding: 5px 12px; text-decoration: none; transition: all var(--transition); }
            .adm-gran__btn:hover { border-color: var(--color-primary-light); color: var(--color-text); }
            .adm-gran__btn.is-active { background: linear-gradient(135deg, #4A3F9E, #6b5fd4); border-color: transparent; color: #fff; }
        </style>

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
