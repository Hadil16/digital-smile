# CODING_STANDARDS.md — Conventions de code

> Conventions **réellement observées** dans le codebase (2026-07-03), érigées en
> standard. Tout nouveau code doit s'y conformer (voir aussi `AI_RULES.md`).

---

## 1. Règle transverse : commentaires en FRANÇAIS, pédagogiques

Le propriétaire n'est pas expert : chaque fichier explique **pourquoi**, pas
seulement quoi. Style observé partout :

```php
/**
 * app/Core/Database.php
 * -----------------------------------------------------------------
 * Connexion à MySQL via PDO.
 * Pourquoi PDO ? Parce qu'il permet les "requêtes préparées", ...
 * -----------------------------------------------------------------
 */
```

- **En-tête de fichier obligatoire** : chemin du fichier + bloc encadré de tirets expliquant le rôle et les choix.
- Commentaires de section avec bandeaux (`// --- Titre ---` en PHP, `/* ============ ① TITRE ============ */` en CSS/HTML, numérotation ①②③ pour l'ordre de page).
- Les avertissements critiques sont criés : `>>> APRÈS UTILISATION, SUPPRIMEZ CE FICHIER. <<<`.
- Les données fictives sont marquées **« EXEMPLE À REMPLACER »** (cf. témoignage `index.html`).

## 2. PHP

- **Version cible** : PHP 8+ (vérifiée par `health.php`) ; propriétés typées (`private static ?PDO $instance`), types de retour (`: PDO`, `: ?array`, `: int`).
- **Classes** : PascalCase, une classe par fichier, nom du fichier = nom de la classe (`Database.php`). Classes de base `abstract` (Model).
- **Constantes de config** : `define('SNAKE_CASE_MAJUSCULE', ...)` dans `config/config.php` uniquement — jamais de réglage en dur ailleurs.
- **Accès BDD** : uniquement via `Database::getConnection()` (singleton) et requêtes préparées `prepare()` + `execute([':param' => $val])`. Jamais de variable concaténée dans le SQL. `Model::insert()` : n'appeler qu'avec des clés de colonnes **codées en dur** (jamais `$_POST` brut).
- **Sortie HTML** : tout affichage de donnée passe par `htmlspecialchars()` (pattern observé dans `health.php` et `install.php`) ; syntaxe alternative dans les vues (`<?php foreach ... : ?>` / `<?= ... ?>`).
- **Erreurs** : comportement dépendant de `APP_ENV` — détail en `dev`, message générique en `prod` (pattern `Database.php`).
- **Fichiers de langue** : `return [ 'cle' => 'texte' ]`, clés en `snake_case`, identiques dans les 3 fichiers `fr/ar/en`.

## 3. CSS

- **Nommage BEM-light** :
  - Bloc : `.hero`, `.nav`, `.service`
  - Élément : `.hero__title`, `.nav__links` (double underscore)
  - Modificateur : `.btn--primary`, `.btn--marquee` (double tiret)
  - État piloté par JS : préfixe `is-` (`.is-compact`)
  - Utilitaires ponctuels : `.accent`, `.muted`, `.will-reveal`
- **Variables CSS obligatoires** pour couleurs, polices, largeur, padding (`:root` de `base.css`). Pas de couleur de marque en dur.
- **Tailles fluides** : `clamp(min, vw, max)` pour toute typo/espacement d'échelle.
- **Découpage par responsabilité** : base / layout / sections / motion — pas de fichier fourre-tout. Dans `sections.css`, les blocs suivent l'ordre du HTML ; le responsive est regroupé en fin de fichier (breakpoint 860px).
- **Animations** : uniquement `transform` + `opacity` ; `will-change` seulement sur les éléments animés en continu ; toujours prévoir `prefers-reduced-motion`.

## 4. JavaScript

- **ES6+ vanilla** : `const`/`let`, template literals, arrow functions ; **aucun framework**, GSAP seul (CDN).
- **Nommage en français** pour les fonctions/variables métier : `lancerHero()`, `fermerIntro()`, `compteur`, `suivreX`, `reduceMotion` — cohérent avec les commentaires français.
- **Progressive enhancement obligatoire** : première ligne du script = `document.documentElement.classList.add('js')` ; le CSS ne masque que sous `html.js`.
- **Accessibilité d'abord** : toute animation encapsulée derrière la garde `reduceMotion`.
- **Performance** : `gsap.quickTo` pour le suivi souris, listeners `{ passive: true }` sur scroll, pas de recréation d'animations dans les handlers.
- Un seul fichier `animations.js` structuré en sections numérotées commentées.

## 5. HTML

- Sémantique : `<nav>`, `<header>`, `<section id>`, `<article>`, `<footer>`, `<blockquote>`.
- Accessibilité de base : `alt` sur toutes les images, `aria-hidden="true"` sur le décoratif (intro, marquee, mot fantôme), `aria-label` sur le burger.
- Données réelles uniquement ; placeholder = commentaire « EXEMPLE À REMPLACER ».

## 6. SQL

- Fichier unique `database/schema.sql`, commenté en français par groupes numérotés.
- Tables en `snake_case` pluriel, colonnes `snake_case`, FKs nommées `fk_<table>_<ref>`, index `idx_<table>_<colonnes>`.
- `ENGINE=InnoDB`, `utf8mb4_unicode_ci`, `created_at` partout, `updated_at` sur les tables mutables, `deleted_at` pour le soft delete.
- ENUM pour les machines à états courtes ; commentaires sur chaque colonne non évidente.

## 7. Git

- Convention de message cible : `type(scope): message` (voir `docs/DEVELOPMENT_WORKFLOW.md`).
- Jamais de secret ni de mot de passe dans un commit (leçon de `install.php`, voir AUDIT).
