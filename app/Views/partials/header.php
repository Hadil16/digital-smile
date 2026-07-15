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
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles (découpés par responsabilité, faciles à maintenir).
         URL ABSOLUES (préfixe BASE_URL) : indispensables car ces pages sont
         servies sur des URL à plusieurs segments (ex. /client/nouvelle-demande).
         Un chemin relatif « assets/... » y serait résolu sous /client/... → 404. -->
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/layout.css">
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/sections.css">
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/motion.css">

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
    <a href="#accueil"><img src="<?= e(BASE_URL) ?>/assets/img/logo.jpg" alt="Digital Smile" class="nav__logo"></a>
    <div class="nav__links">
        <a href="#services">Services</a>
        <a href="#croyances">Notre vision</a>
        <a href="#chiffres">Chiffres</a>
        <a href="#faq">FAQ</a>
        <a href="#contact">Contact</a>
    </div>
    <!-- Groupe d'actions : bascule de thème + accès selon l'état de connexion. -->
    <div class="nav__actions">
        <button type="button" id="themeToggle" class="nav__theme"
                aria-label="Basculer entre thème clair et sombre" title="Thème clair / sombre">
            <span aria-hidden="true">&#127769;</span>
        </button>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <!-- Connecté : cloche + nom + déconnexion (pas de Connexion/Inscription). -->
            <a href="<?= e(BASE_URL) ?>/notifications" class="nav__bell"
               aria-label="Notifications<?= $notifCount > 0 ? ' : ' . (int) $notifCount . ' non lue' . ($notifCount > 1 ? 's' : '') : ' : aucune non lue' ?>">
                <span aria-hidden="true">&#128276;</span>
                <?php if ($notifCount > 0): ?>
                    <span class="nav__bell-badge"><?= $notifCount > 99 ? '99+' : (int) $notifCount ?></span>
                <?php endif; ?>
            </a>
            <span class="nav__user"><?= e($_SESSION['name'] ?? '') ?></span>
            <a href="<?= e(BASE_URL) ?>/logout" class="btn btn--ghost">Déconnexion</a>
        <?php else: ?>
            <!-- Déconnecté : Connexion (contour) + S'inscrire (plein, action mise en avant). -->
            <a href="<?= e(BASE_URL) ?>/login" class="btn btn--ghost">Connexion</a>
            <a href="<?= e(BASE_URL) ?>/register" class="btn btn--primary">S'inscrire</a>
        <?php endif; ?>
    </div>
    <button class="nav__burger" aria-label="Menu">&#9776;</button>
</nav>
