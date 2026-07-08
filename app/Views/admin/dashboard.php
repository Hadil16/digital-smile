<?php
/**
 * app/Views/admin/dashboard.php
 * -----------------------------------------------------------------
 * Tableau de bord administrateur : cartes de statistiques (chiffres
 * en direct) + accès à la gestion des demandes et de l'équipe.
 * Variables : $statusCounts, $totalOrders, $totalClients, $totalEmployees.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// Cartes de statistiques (icône, libellé FR, valeur). Définies une fois
// pour éviter de répéter le même bloc HTML huit fois.
$stats = [
    ['icon' => '📋', 'label' => 'Total demandes', 'value' => (int) ($totalOrders ?? 0)],
    ['icon' => '⏳', 'label' => 'En attente',      'value' => (int) ($statusCounts['pending'] ?? 0)],
    ['icon' => '✅', 'label' => 'À affecter',       'value' => (int) ($statusCounts['approved'] ?? 0)],
    ['icon' => '🔧', 'label' => 'En cours',         'value' => (int) ($statusCounts['in_progress'] ?? 0)],
    ['icon' => '📦', 'label' => 'Livrées',          'value' => (int) ($statusCounts['delivered'] ?? 0)],
    ['icon' => '✔️', 'label' => 'Terminées',        'value' => (int) ($statusCounts['completed'] ?? 0)],
    ['icon' => '👥', 'label' => 'Clients',          'value' => (int) ($totalClients ?? 0)],
    ['icon' => '🧑‍💼', 'label' => 'Employés',       'value' => (int) ($totalEmployees ?? 0)],
];
?>
<style>
    /* Styles auto-portés du tableau de bord (couleurs de marque). */
    .dash { --violet: #4A3F9E; --lime: #8BC63F;
        min-height: 70vh; padding: 96px 24px 64px; font-family: 'Inter', system-ui, sans-serif; }
    .intro { display: none !important; } /* pas d'intro flash hors accueil */
    .dash__wrap { max-width: 960px; margin: 0 auto; }
    .dash__head { text-align: center; margin: 0 0 32px; }
    .dash__role { display: inline-block; background: var(--violet); color: #fff; font-size: 13px;
        font-weight: 600; padding: 6px 14px; border-radius: 999px; margin: 0 0 12px; }
    .dash__title { font-family: 'Poppins', system-ui, sans-serif; font-weight: 800;
        font-size: clamp(26px, 4vw, 36px); color: var(--violet); margin: 0 0 8px; }
    .dash__note { color: #666; font-size: 15px; margin: 0; }

    /* Grille responsive de cartes chiffrées */
    .stats { list-style: none; padding: 0; margin: 0 0 34px;
        display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; }
    .stat { background: #fff; border: 1px solid #eee; border-top: 3px solid var(--lime);
        border-radius: 16px; padding: 22px 18px; text-align: center;
        box-shadow: 0 12px 40px rgba(74, 63, 158, .06); }
    .stat__icon { display: block; font-size: 24px; margin: 0 0 6px; }
    .stat__num { display: block; font-family: 'Poppins', system-ui, sans-serif; font-weight: 800;
        font-size: 34px; line-height: 1; color: var(--violet); }
    .stat__label { display: block; color: #666; font-size: 13px; font-weight: 600; margin: 8px 0 0; }

    /* Boutons d'accès (inchangés) */
    .dash__actions { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
    .dash__btn { display: inline-block; background: var(--violet); color: #fff;
        text-decoration: none; font-weight: 600; padding: 12px 26px; border-radius: 999px;
        transition: background .2s; }
    .dash__btn:hover { background: var(--lime); }
    .dash__btn--ghost { background: transparent; color: #444; border: 1px solid #d5d5db; }
    .dash__btn--ghost:hover { background: transparent; border-color: var(--violet); color: var(--violet); }
</style>

<main class="dash">
    <div class="dash__wrap">
        <header class="dash__head">
            <p class="dash__role">Espace administrateur</p>
            <h1 class="dash__title">Bienvenue, <?= e($_SESSION['name'] ?? '') ?> 👋</h1>
            <p class="dash__note">Vue d'ensemble de l'activité de l'agence.</p>
        </header>

        <ul class="stats" aria-label="Statistiques de l'agence">
            <?php foreach ($stats as $s): ?>
                <li class="stat" aria-label="<?= e($s['label']) ?> : <?= (int) $s['value'] ?>">
                    <span class="stat__icon" aria-hidden="true"><?= $s['icon'] ?></span>
                    <span class="stat__num"><?= (int) $s['value'] ?></span>
                    <span class="stat__label"><?= e($s['label']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="dash__actions">
            <a class="dash__btn" href="<?= e(BASE_URL) ?>/admin/commandes">Gérer les demandes</a>
            <a class="dash__btn" href="<?= e(BASE_URL) ?>/admin/employes">Gérer l'équipe</a>
            <a class="dash__btn dash__btn--ghost" href="<?= e(BASE_URL) ?>/logout">Déconnexion</a>
        </div>
    </div>
</main>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
