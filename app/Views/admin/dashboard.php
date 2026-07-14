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

// Prénom + initiales de l'admin (pour l'entête et l'avatar).
$adminName = $_SESSION['name'] ?? '';
$nameParts = array_values(array_filter(preg_split('/\s+/', trim($adminName))));
$firstName = $nameParts[0] ?? 'Admin';
$initials  = strtoupper(mb_substr($nameParts[0] ?? 'A', 0, 1, 'UTF-8')
           . (count($nameParts) > 1 ? mb_substr((string) end($nameParts), 0, 1, 'UTF-8') : ''));

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

// Éléments de la barre latérale (Clients : page à venir → ancre inerte).
$navItems = [
    ['Tableau de bord', BASE_URL . '/admin',            '📊', true],
    ['Commandes',       BASE_URL . '/admin/commandes',  '📋', false],
    ['Clients',         '#',                            '👥', false],
    ['Employés',        BASE_URL . '/admin/employes',   '🧑‍💼', false],
    ['Factures',        BASE_URL . '/admin/factures',   '🧾', false],
];
?>
<style>
    /* Shell d'administration premium — 100 % tokens (clair/sombre). */
    .nav, .foot, .intro { display: none !important; }   /* on remplace le chrome public */
    body { background: var(--color-bg); }

    .adm { font-family: 'Poppins', system-ui, sans-serif; color: var(--color-text); }

    /* --- Barre latérale fixe --- */
    .adm__side {
        position: fixed; top: 0; left: 0; width: 210px; height: 100vh;
        display: flex; flex-direction: column; padding: 22px 16px; gap: 8px;
        background: var(--color-surface-alt); border-right: 1px solid var(--color-border);
    }
    .adm__brand { display: flex; align-items: center; gap: 10px; padding: 4px 8px 18px; }
    .adm__brand img { height: 30px; border-radius: 7px; }
    .adm__brand span { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 700; font-size: 17px; color: var(--color-text); }
    .adm__nav { display: flex; flex-direction: column; gap: 4px; flex: 1; }
    .adm__link {
        display: flex; align-items: center; gap: 10px; padding: 11px 12px; border-radius: 12px;
        font-size: 14px; font-weight: 500; color: var(--color-muted); transition: background var(--transition), color var(--transition);
    }
    .adm__link:hover { background: var(--color-border); color: var(--color-text); }
    .adm__link.is-active { background: linear-gradient(135deg, #4A3F9E, #6b5fd4); color: #fff; font-weight: 600; }
    .adm__link-ico { width: 20px; text-align: center; }
    .adm__link--logout { margin-top: auto; color: var(--color-danger); }
    .adm__link--logout:hover { background: rgba(179, 38, 30, .12); color: var(--color-danger); }

    /* --- Contenu principal --- */
    .adm__main { margin-left: 210px; padding: 24px 26px 48px; min-height: 100vh; }
    .adm__top { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin: 0 0 26px; }
    .adm__greet { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 26px; color: var(--color-text); margin: 0; }
    .adm__subtitle { font-size: 13px; color: var(--color-muted); margin: 4px 0 0; }
    .adm__top-right { display: flex; align-items: center; gap: 14px; }
    .adm__bell { position: relative; display: inline-flex; align-items: center; justify-content: center;
        width: 42px; height: 42px; border-radius: 999px; font-size: 19px; background: var(--color-surface-alt); border: 1px solid var(--color-border); }
    .adm__bell-badge { position: absolute; top: -3px; right: -3px; min-width: 18px; height: 18px; box-sizing: border-box;
        padding: 0 5px; border-radius: 999px; background: #8BC63F; color: #1f3d07; font-size: 11px; font-weight: 700; line-height: 18px; text-align: center; }
    .adm__avatar { display: inline-grid; place-items: center; width: 42px; height: 42px; border-radius: 999px;
        font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 15px; color: #1a1730;
        background: linear-gradient(135deg, #8BC63F, #6BA02C); }

    /* --- Cartes KPI --- */
    .adm__kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px; margin: 0 0 22px; }
    .adm-kpi { border: 1px solid var(--color-border); border-radius: 16px; padding: 18px;
        transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition); }
    .adm-kpi:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); border-color: rgba(139, 198, 63, .5); }
    .adm-kpi--violet { background: rgba(74, 63, 158, .12); }
    .adm-kpi--amber  { background: rgba(240, 165, 0, .14); }
    .adm-kpi--green  { background: rgba(139, 198, 63, .16); }
    .adm-kpi__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
    .adm-kpi__label { font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--color-muted); }
    .adm-kpi__ico { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 10px;
        font-size: 17px; background: var(--color-surface); border: 1px solid var(--color-border); }
    .adm-kpi__num { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 28px;
        line-height: 1; color: var(--color-text); margin: 14px 0 0; }
    .adm-kpi__cap { font-size: 12px; color: var(--color-muted); margin: 6px 0 0; }

    /* --- Cartes (graphiques + tableau) --- */
    .adm-card { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 16px; padding: 20px; }
    .adm-card__title { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 700; font-size: 15px; color: var(--color-text); margin: 0 0 14px; }
    .adm__charts { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin: 0 0 22px; }
    .adm-card__canvas { position: relative; height: 260px; }

    /* --- Dernières commandes --- */
    .adm__recent-head { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin: 0 0 14px; }
    .adm__all { font-family: 'Poppins', system-ui, sans-serif; font-weight: 600; font-size: 13px; color: #8BC63F; }
    .adm-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .adm-table th { text-align: left; font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--color-muted); padding: 0 12px 12px; }
    .adm-table td { padding: 12px; border-top: 1px solid var(--color-border); color: var(--color-text); }
    .adm-table tbody tr { transition: background var(--transition); }
    .adm-table tbody tr:hover { background: var(--color-surface-alt); }
    .adm-table__num { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 600; color: var(--color-accent-dark); white-space: nowrap; }
    .adm-pill { display: inline-block; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 999px; }
    .adm-pill--violet { background: rgba(74, 63, 158, .16); color: var(--color-primary-light); }
    .adm-pill--amber  { background: rgba(240, 165, 0, .16); color: #b8860b; }
    .adm-pill--green  { background: rgba(139, 198, 63, .18); color: var(--color-success); }
    .adm-pill--red    { background: rgba(179, 38, 30, .16); color: var(--color-danger); }
    .adm-pill--muted  { background: var(--color-surface-alt); color: var(--color-muted); }
    .adm-table__scroll { overflow-x: auto; }
    .adm__empty { color: var(--color-muted); font-size: 14px; padding: 20px 12px; }

    /* --- Responsive : la sidebar devient une barre supérieure < 900px --- */
    @media (max-width: 900px) {
        .adm__side { position: static; width: auto; height: auto; flex-direction: row; align-items: center;
            gap: 6px; padding: 12px 14px; overflow-x: auto; border-right: 0; border-bottom: 1px solid var(--color-border); }
        .adm__brand { padding: 0 12px 0 4px; }
        .adm__brand span { display: none; }
        .adm__nav { flex-direction: row; flex: 0 0 auto; }
        .adm__link { white-space: nowrap; }
        .adm__link--logout { margin-top: 0; }
        .adm__main { margin-left: 0; padding: 20px 16px 40px; }
    }
</style>

<div class="adm">
    <!-- ============ Barre latérale ============ -->
    <aside class="adm__side">
        <div class="adm__brand">
            <img src="assets/img/logo.jpg" alt="">
            <span>Digital Smile</span>
        </div>
        <nav class="adm__nav" aria-label="Navigation admin">
            <?php foreach ($navItems as [$label, $href, $ico, $active]): ?>
                <a class="adm__link<?= $active ? ' is-active' : '' ?>" href="<?= e($href) ?>"<?= $active ? ' aria-current="page"' : '' ?>>
                    <span class="adm__link-ico" aria-hidden="true"><?= $ico ?></span><?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <a class="adm__link adm__link--logout" href="<?= e(BASE_URL) ?>/logout">
            <span class="adm__link-ico" aria-hidden="true">🚪</span>Déconnexion
        </a>
    </aside>

    <!-- ============ Contenu principal ============ -->
    <main class="adm__main">
        <header class="adm__top">
            <div>
                <h1 class="adm__greet">Bonjour, <?= e($firstName) ?> 👋</h1>
                <p class="adm__subtitle">Voici l'activité de votre agence aujourd'hui</p>
            </div>
            <div class="adm__top-right">
                <a class="adm__bell" href="<?= e(BASE_URL) ?>/notifications"
                   aria-label="Notifications<?= ($notifCount ?? 0) > 0 ? ' : ' . (int) $notifCount . ' non lue' . ($notifCount > 1 ? 's' : '') : ' : aucune non lue' ?>">
                    <span aria-hidden="true">&#128276;</span>
                    <?php if (($notifCount ?? 0) > 0): ?>
                        <span class="adm__bell-badge"><?= $notifCount > 99 ? '99+' : (int) $notifCount ?></span>
                    <?php endif; ?>
                </a>
                <span class="adm__avatar" aria-hidden="true"><?= e($initials) ?></span>
            </div>
        </header>

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
