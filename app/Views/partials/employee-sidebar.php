<?php
/**
 * app/Views/partials/employee-sidebar.php
 * -----------------------------------------------------------------
 * Coquille (shell) commune de l'espace employé : barre latérale fixe
 * + zone de contenu + entête. Calquée sur admin-sidebar.php. À inclure
 * APRÈS header.php.
 *
 * La page appelante définit AVANT l'inclusion :
 *   $employeeActive → clé du lien actif ('taches'|'profil'|'bibliotheque')
 *   $pageTitle      → titre affiché dans l'entête
 *   $pageSubtitle   → sous-titre (optionnel)
 *
 * Après le contenu, la page ferme la coquille : </main></div> puis footer.php.
 * PRÉSENTATION UNIQUEMENT — aucune logique métier ici.
 * -----------------------------------------------------------------
 */

// Initiales de l'utilisateur (pour l'avatar de l'entête).
$empName  = $_SESSION['name'] ?? '';
$empParts = array_values(array_filter(preg_split('/\s+/', trim($empName))));
$empInit  = strtoupper(mb_substr($empParts[0] ?? 'E', 0, 1, 'UTF-8')
          . (count($empParts) > 1 ? mb_substr((string) end($empParts), 0, 1, 'UTF-8') : ''));

// Liens de la barre latérale de l'employé.
$empNav = [
    'taches'       => ['Mes tâches',      BASE_URL . '/employe/taches',       '📋'],
    'profil'       => ['Mon profil',      BASE_URL . '/employe/profil',       '👤'],
    'bibliotheque' => ['Ma bibliothèque', BASE_URL . '/employe/bibliotheque', '📚'],
];
$employeeActive = $employeeActive ?? 'taches';
$pageTitle      = $pageTitle      ?? 'Espace employé';
$pageSubtitle   = $pageSubtitle   ?? '';

// Styles communs des espaces (mêmes .adm* que l'admin) — source unique.
require ROOT_PATH . '/app/Views/partials/workspace-styles.php';
?>

<div class="adm">
    <!-- ============ Barre latérale ============ -->
    <aside class="adm__side">
        <div class="adm__brand">
            <img src="assets/img/logo.jpg" alt="">
            <span>Digital Smile</span>
        </div>
        <nav class="adm__nav" aria-label="Navigation employé">
            <?php foreach ($empNav as $key => [$label, $href, $ico]): ?>
                <?php $active = ($key === $employeeActive); ?>
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
                <h1 class="adm__greet"><?= e($pageTitle) ?></h1>
                <?php if ($pageSubtitle !== ''): ?>
                    <p class="adm__subtitle"><?= e($pageSubtitle) ?></p>
                <?php endif; ?>
            </div>
            <div class="adm__top-right">
                <a class="adm__bell" href="<?= e(BASE_URL) ?>/notifications"
                   aria-label="Notifications<?= ($notifCount ?? 0) > 0 ? ' : ' . (int) $notifCount . ' non lue' . ($notifCount > 1 ? 's' : '') : ' : aucune non lue' ?>">
                    <span aria-hidden="true">&#128276;</span>
                    <?php if (($notifCount ?? 0) > 0): ?>
                        <span class="adm__bell-badge"><?= $notifCount > 99 ? '99+' : (int) $notifCount ?></span>
                    <?php endif; ?>
                </a>
                <span class="adm__avatar" aria-hidden="true"><?= e($empInit) ?></span>
            </div>
        </header>
        <!-- Le contenu de la page suit, puis fermeture </main></div> + footer. -->
