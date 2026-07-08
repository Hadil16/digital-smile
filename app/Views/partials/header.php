<?php
/** app/Views/partials/header.php — haut de page commun : head (meta, polices, CSS), intro flash, navigation. */
// Pastille de notifications : nombre de non lues (uniquement si connecté).
$notifCount = !empty($_SESSION['user_id'])
    ? (new Notification())->unreadCount((int) $_SESSION['user_id'])
    : 0;
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Smile — Agence de branding à Alger</title>
    <meta name="description" content="Digital Smile, agence de branding et de communication à Alger : identité visuelle, impression, web, QR codes et production audiovisuelle.">

    <!-- Thème : restaure le choix (clair/sombre) AVANT le rendu, pour éviter tout flash. -->
    <script>
        (function () {
            try {
                var t = localStorage.getItem('ds-theme');
                if (t === 'dark' || t === 'light') document.documentElement.setAttribute('data-theme', t);
            } catch (e) {}
        })();
    </script>

    <!-- Polices -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles (découpés par responsabilité, faciles à maintenir) -->
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/sections.css">
    <link rel="stylesheet" href="assets/css/motion.css">

    <style>
        /* Cloche de notifications (auto-portée, couleurs de marque). */
        .nav__bell { position: relative; display: inline-flex; align-items: center; justify-content: center;
            width: 42px; height: 42px; border-radius: 999px; text-decoration: none; font-size: 20px;
            background: #f2f0fb; }
        .nav__bell:hover { background: #e7e2f7; }
        .nav__bell-badge { position: absolute; top: -3px; right: -3px; min-width: 18px; height: 18px;
            box-sizing: border-box; padding: 0 5px; border-radius: 999px; background: #8BC63F;
            color: #1f3d07; font-size: 11px; font-weight: 700; line-height: 18px; text-align: center;
            font-family: 'Inter', system-ui, sans-serif; }
    </style>
</head>
<body>

<!-- ============ ① INTRO FLASH (une fois par session) ============ -->
<div class="intro" aria-hidden="true">
    <p class="intro__word"></p>
    <p class="intro__counter">0%</p>
</div>

<!-- ============ NAVIGATION ============ -->
<nav class="nav">
    <a href="#accueil"><img src="assets/img/logo.jpg" alt="Digital Smile" class="nav__logo"></a>
    <div class="nav__links">
        <a href="#services">Services</a>
        <a href="#croyances">Notre vision</a>
        <a href="#chiffres">Chiffres</a>
        <a href="#contact">Contact</a>
    </div>
    <!-- Bascule de thème clair / sombre (icône mise à jour par le script du footer). -->
    <button type="button" id="themeToggle" class="nav__theme"
            aria-label="Basculer entre thème clair et sombre" title="Thème clair / sombre">
        <span aria-hidden="true">&#127769;</span>
    </button>
    <?php if (!empty($_SESSION['user_id'])): ?>
        <!-- Connecté : cloche de notifications avec pastille du nombre non lu. -->
        <a href="<?= e(BASE_URL) ?>/notifications" class="nav__bell"
           aria-label="Notifications<?= $notifCount > 0 ? ' : ' . (int) $notifCount . ' non lue' . ($notifCount > 1 ? 's' : '') : ' : aucune non lue' ?>">
            <span aria-hidden="true">&#128276;</span>
            <?php if ($notifCount > 0): ?>
                <span class="nav__bell-badge"><?= $notifCount > 99 ? '99+' : (int) $notifCount ?></span>
            <?php endif; ?>
        </a>
    <?php else: ?>
        <a href="<?= e(BASE_URL) ?>/login" class="nav__cta">Connexion</a>
        <a href="<?= e(BASE_URL) ?>/register" class="nav__cta nav__cta--ghost">S'inscrire</a>
    <?php endif; ?>
    <button class="nav__burger" aria-label="Menu">&#9776;</button>
</nav>
