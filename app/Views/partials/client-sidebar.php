<?php
/**
 * app/Views/partials/client-sidebar.php
 * -----------------------------------------------------------------
 * Coquille (shell) commune de l'espace client : barre latérale fixe
 * + zone de contenu + entête. Calquée sur employee-sidebar.php. À
 * inclure APRÈS header.php.
 *
 * La page appelante définit AVANT l'inclusion :
 *   $clientActive → clé du lien actif ('commandes'|'nouvelle'|'factures')
 *   $pageTitle    → titre affiché dans l'entête
 *   $pageSubtitle → sous-titre (optionnel)
 *
 * Après le contenu, la page ferme la coquille : </main></div> puis footer.php.
 * PRÉSENTATION UNIQUEMENT — aucune logique métier ici.
 * -----------------------------------------------------------------
 */

// Initiales de l'utilisateur (pour l'avatar de l'entête).
$cliName  = $_SESSION['name'] ?? '';
$cliParts = array_values(array_filter(preg_split('/\s+/', trim($cliName))));
$cliInit  = strtoupper(mb_substr($cliParts[0] ?? 'C', 0, 1, 'UTF-8')
          . (count($cliParts) > 1 ? mb_substr((string) end($cliParts), 0, 1, 'UTF-8') : ''));

// Liens de la barre latérale du client.
$cliNav = [
    'commandes' => ['Mes commandes',    BASE_URL . '/client',                  '📋'],
    'nouvelle'  => ['Nouvelle demande', BASE_URL . '/client/nouvelle-demande', '➕'],
    'factures'  => ['Mes factures',     BASE_URL . '/client/factures',         '🧾'],
];
$clientActive = $clientActive ?? 'commandes';
$pageTitle    = $pageTitle    ?? 'Espace client';
$pageSubtitle = $pageSubtitle ?? '';

// Styles communs des espaces (mêmes .adm* que l'admin/employé) — source unique.
require ROOT_PATH . '/app/Views/partials/workspace-styles.php';
?>

<div class="adm">
    <!-- ============ Barre latérale ============ -->
    <aside class="adm__side">
        <div class="adm__brand">
            <img src="<?= e(BASE_URL) ?>/assets/img/logo.jpg" alt="">
            <span>Digital Smile</span>
        </div>
        <nav class="adm__nav" aria-label="Navigation client">
            <?php foreach ($cliNav as $key => [$label, $href, $ico]): ?>
                <?php $active = ($key === $clientActive); ?>
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
                <span class="adm__avatar" aria-hidden="true"><?= e($cliInit) ?></span>
            </div>
        </header>
        <!-- Le contenu de la page suit, puis fermeture </main></div> + footer. -->
