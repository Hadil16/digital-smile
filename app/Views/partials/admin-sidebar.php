<?php
/**
 * app/Views/partials/admin-sidebar.php
 * -----------------------------------------------------------------
 * Coquille (shell) commune des pages d'administration : barre latérale
 * fixe + zone de contenu + entête. À inclure APRÈS header.php.
 *
 * La page appelante définit AVANT l'inclusion :
 *   $adminActive   → clé du lien actif ('dashboard'|'commandes'|'clients'|'employes'|'factures')
 *   $pageTitle     → titre affiché dans l'entête
 *   $pageSubtitle  → sous-titre (optionnel)
 *
 * Après le contenu, la page ferme la coquille : </main></div> puis footer.php.
 * PRÉSENTATION UNIQUEMENT — aucune logique métier ici.
 * -----------------------------------------------------------------
 */

// Prénom + initiales de l'utilisateur (pour l'avatar de l'entête).
$admName   = $_SESSION['name'] ?? '';
$admParts  = array_values(array_filter(preg_split('/\s+/', trim($admName))));
$admInit   = strtoupper(mb_substr($admParts[0] ?? 'A', 0, 1, 'UTF-8')
           . (count($admParts) > 1 ? mb_substr((string) end($admParts), 0, 1, 'UTF-8') : ''));

// Liens de la barre latérale (Clients : page à venir → ancre inerte).
$admNav = [
    'dashboard' => ['Tableau de bord', BASE_URL . '/admin',           '📊'],
    'commandes' => ['Commandes',       BASE_URL . '/admin/commandes',  '📋'],
    'clients'   => ['Clients',         BASE_URL . '/admin/clients',    '👥'],
    'employes'  => ['Employés',        BASE_URL . '/admin/employes',   '🧑‍💼'],
    'factures'  => ['Factures',        BASE_URL . '/admin/factures',   '🧾'],
];
$adminActive  = $adminActive  ?? 'dashboard';
$pageTitle    = $pageTitle    ?? 'Administration';
$pageSubtitle = $pageSubtitle ?? '';

// Styles communs des espaces (admin + employé) — source unique, chargée
// uniquement sur les pages d'espace (donc pas de pollution du site public).
require ROOT_PATH . '/app/Views/partials/workspace-styles.php';
?>

<div class="adm">
    <!-- ============ Barre latérale ============ -->
    <aside class="adm__side">
        <div class="adm__brand">
            <img src="<?= e(BASE_URL) ?>/assets/img/logo.jpg" alt="">
            <span>Digital Smile</span>
        </div>
        <nav class="adm__nav" aria-label="Navigation admin">
            <?php foreach ($admNav as $key => [$label, $href, $ico]): ?>
                <?php $active = ($key === $adminActive); ?>
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
                <span class="adm__avatar" aria-hidden="true"><?= e($admInit) ?></span>
            </div>
        </header>
        <!-- Le contenu de la page suit, puis fermeture </main></div> + footer. -->
