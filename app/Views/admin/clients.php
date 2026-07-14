<?php
/**
 * app/Views/admin/clients.php
 * -----------------------------------------------------------------
 * Liste des clients (admin) : 3 cartes KPI + tableau détaillé avec
 * statistiques de commandes. Variable : $clients (Client::allWithStats()).
 * PRÉSENTATION UNIQUEMENT — aucune requête SQL ici.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// Mois courant (pour "nouveaux ce mois", basé sur la 1re commande).
$thisMonth = date('Y-m');

// KPI calculés à partir des données déjà chargées (agrégation d'affichage).
$totalClients = count($clients);
$activeClients = 0;   // au moins 1 commande en cours
$newThisMonth  = 0;   // 1re commande passée ce mois-ci
foreach ($clients as $c) {
    if ((int) $c['active_orders'] > 0) {
        $activeClients++;
    }
    if (!empty($c['first_order_at']) && substr($c['first_order_at'], 0, 7) === $thisMonth) {
        $newThisMonth++;
    }
}

$kpis = [
    ['label' => 'Clients',       'value' => $totalClients,  'icon' => '👥', 'tone' => 'violet', 'cap' => 'Comptes clients'],
    ['label' => 'Actifs',        'value' => $activeClients, 'icon' => '🚀', 'tone' => 'green',  'cap' => 'Au moins 1 commande en cours'],
    ['label' => 'Nouveaux',      'value' => $newThisMonth,  'icon' => '✨', 'tone' => 'amber',  'cap' => 'Première commande ce mois'],
];

// Petit utilitaire : initiales à partir d'un nom (pour l'avatar).
$initialsOf = function (string $name): string {
    $parts = array_values(array_filter(preg_split('/\s+/', trim($name))));
    return strtoupper(mb_substr($parts[0] ?? '?', 0, 1, 'UTF-8')
        . (count($parts) > 1 ? mb_substr((string) end($parts), 0, 1, 'UTF-8') : ''));
};
$fmtDate = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';

// Coquille admin commune (sidebar + entête).
$adminActive  = 'clients';
$pageTitle    = 'Clients';
$pageSubtitle = 'Vos clients et leur activité';
require ROOT_PATH . '/app/Views/partials/admin-sidebar.php';
?>
        <!-- Cartes KPI (données réelles uniquement) -->
        <section class="adm__kpis" aria-label="Indicateurs clients">
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

        <!-- Tableau des clients -->
        <?php if (empty($clients)): ?>
            <p class="adm-empty">Aucun client pour le moment.</p>
        <?php else: ?>
            <div class="adm-card">
                <div class="adm-table__scroll">
                    <table class="adm-table adm-table--clients">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Commandes</th>
                                <th>Terminées</th>
                                <th>Depuis</th>
                                <th><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $c): ?>
                                <tr>
                                    <td>
                                        <span class="adm-client">
                                            <span class="adm-client__ava" aria-hidden="true"><?= e($initialsOf($c['name'])) ?></span>
                                            <span class="adm-client__id">
                                                <span class="adm-client__name"><?= e($c['name']) ?></span>
                                                <span class="adm-client__mail"><?= e($c['email']) ?></span>
                                                <?php if (!empty($c['phone'])): ?>
                                                    <span class="adm-client__mail"><?= e($c['phone']) ?></span>
                                                <?php endif; ?>
                                            </span>
                                        </span>
                                    </td>
                                    <td class="adm-table__num"><?= (int) $c['total_orders'] ?></td>
                                    <td>
                                        <span class="adm-pill adm-pill--green"><?= (int) $c['completed_orders'] ?></span>
                                    </td>
                                    <td><?= e($fmtDate($c['first_order_at'])) ?></td>
                                    <td>
                                        <a class="adm-btn adm-btn--ghost" href="<?= e(BASE_URL) ?>/admin/commandes"
                                           aria-label="Voir les commandes de <?= e($c['name']) ?>">Voir</a>
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
    /* Cellule "Client" : avatar initiales + nom + email (auto-portée). */
    .adm-client { display: flex; align-items: center; gap: 12px; }
    .adm-client__ava { display: inline-grid; place-items: center; width: 38px; height: 38px; border-radius: 999px;
        flex: 0 0 auto; font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 13px;
        color: #1a1730; background: linear-gradient(135deg, #8BC63F, #6BA02C); }
    .adm-client__id { display: flex; flex-direction: column; line-height: 1.3; }
    .adm-client__name { font-weight: 600; color: var(--color-text); }
    .adm-client__mail { font-size: 12px; color: var(--color-muted); }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
