<?php /** app/Views/partials/header.php — haut de page commun : head (meta, polices, CSS), intro flash, navigation. */ ?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Smile — Agence de branding à Alger</title>
    <meta name="description" content="Digital Smile, agence de branding et de communication à Alger : identité visuelle, impression, web, QR codes et production audiovisuelle.">

    <!-- Polices -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles (découpés par responsabilité, faciles à maintenir) -->
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/sections.css">
    <link rel="stylesheet" href="assets/css/motion.css">
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
    <a href="#contact" class="nav__cta">Connexion</a>
    <button class="nav__burger" aria-label="Menu">&#9776;</button>
</nav>
