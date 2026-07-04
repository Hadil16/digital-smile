<?php
/**
 * app/Views/errors/404.php
 * -----------------------------------------------------------------
 * Page affichée quand aucune route ne correspond à l'URL demandée.
 * Le code HTTP 404 est posé par le Router AVANT d'inclure ce fichier.
 *
 * Page autonome (styles intégrés) : elle doit s'afficher même si
 * les feuilles de style du site évoluent ou ne chargent pas.
 * Les couleurs reprennent les tokens de base.css (violet / lime).
 * -----------------------------------------------------------------
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page introuvable — Digital Smile</title>
    <meta name="robots" content="noindex">
    <style>
        /* Tokens locaux, copies de ceux de base.css (page autonome). */
        :root { --violet: #4A3F9E; --lime: #8BC63F; --ink: #111; --grey: #666; }
        * { margin: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: #fff; color: var(--ink);
            text-align: center; padding: 24px;
        }
        .err__code {
            font-family: 'Poppins', system-ui, sans-serif;
            font-weight: 800;
            font-size: clamp(90px, 22vw, 220px);
            line-height: 1;
            color: var(--violet);
            letter-spacing: -0.04em;
        }
        .err__code span { color: var(--lime); }
        h1 { font-size: clamp(20px, 3vw, 30px); margin: 12px 0 8px; }
        p  { color: var(--grey); margin-bottom: 28px; }
        .err__btn {
            display: inline-block;
            background: var(--violet); color: #fff;
            text-decoration: none;
            padding: 14px 34px; border-radius: 999px;
            font-weight: 600;
        }
        .err__btn:hover { background: var(--lime); }
    </style>
</head>
<body>
    <main>
        <p class="err__code" aria-hidden="true">4<span>0</span>4</p>
        <h1>Cette page n'existe pas.</h1>
        <p>L'adresse est peut-être erronée, ou la page a été déplacée.</p>
        <a class="err__btn" href="<?= htmlspecialchars(BASE_URL) ?>/">Retour à l'accueil</a>
    </main>
</body>
</html>
