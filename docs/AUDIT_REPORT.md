# AUDIT_REPORT.md — Audit du codebase (constats uniquement)

> Audit complet en lecture seule du 2026-07-03, sur l'intégralité du dépôt
> (commit `d7f303f` « Initial commit »). **Aucun correctif appliqué** — les
> remédiations priorisées sont dans `docs/ROADMAP.md`.
> Classification : 🔴 critique · 🟠 sécurité · 🟡 dette technique · ✅ points forts.

---

## 🔴 Critique

### C1 — Le front controller `public/index.php` n'existe pas
`public/.htaccess` réécrit **toute URL ne correspondant pas à un fichier** vers
`index.php?url=$1` — or ce fichier est absent. Toute URL « propre »
(ex. `/digital-smile/login`) renvoie une erreur 404 Apache brute. L'accueil ne
fonctionne que parce qu'Apache sert `index.html` en DirectoryIndex. Bloquant pour
la Phase 5 ; c'est la première brique à poser.

### C2 — `install.php` : identifiants admin en clair, exposés au web et dans Git
`public/install.php:17-19` contient l'email et le **mot de passe admin en clair**
(`Admin@2026`), les affiche à l'écran après création, et le fichier est accessible
publiquement tant qu'il n'est pas supprimé. De plus, ce mot de passe est **déjà
dans l'historique Git** (commit initial) : même après suppression du fichier, il
reste lisible via `git log`. Le compte devra être considéré comme compromis dès
que le dépôt est partagé → changement de mot de passe obligatoire à la première
connexion, et idéalement le script devrait demander le mot de passe au lieu de
l'embarquer.

### C3 — Vidéo héros manquante : `public/assets/video/cubes-logo.mp4`
Référencée par `index.html:49`, le dossier `assets/video/` n'existe même pas.
Le poster `hero.jpg` masque le problème visuellement, mais chaque visite génère
une requête 404 et l'expérience « vidéo » vendue par le design n'existe pas.
(`CLAUDE.md` note « à placer manuellement » — toujours pas fait.)

---

## 🟠 Sécurité

### S1 — Aucun en-tête de sécurité HTTP
Aucun des `.htaccess` n'envoie `Content-Security-Policy`,
`X-Content-Type-Options: nosniff`, `X-Frame-Options`/`frame-ancestors`,
`Referrer-Policy` ou `Permissions-Policy`. Le site peut être iframé (clickjacking)
et rien ne limite les sources de scripts.

### S2 — Gestionnaire `onerror` inline sur la balise vidéo (`index.html:48`)
`onerror="this.outerHTML='<img ...>'"` : (a) tout script inline devra être
autorisé par la future CSP (`unsafe-inline`), ce qui l'affaiblit ; (b) **le
fallback ne se déclenche probablement jamais** — l'échec de chargement d'une
`<source>` émet l'événement `error` sur l'élément `<source>`, pas sur `<video>`.
Le filet réel est le `poster`. À remplacer par une gestion dans `animations.js`.

### S3 — `health.php` public : divulgation d'informations
Version PHP, état de la connexion MySQL, nom de la base, nombre de tables,
message d'erreur PDO brut (`health.php:78`) — utile en dev, précieux pour un
attaquant. À supprimer en production (le fichier le dit lui-même) ou à protéger.

### S4 — Protection uploads dépendante de mod_php
`public/uploads/.htaccess` utilise `php_flag engine off` : efficace **uniquement
si PHP tourne en module Apache** (cas XAMPP). Sous PHP-FPM/CGI (fréquent chez les
hébergeurs), cette directive est ignorée — voire provoque une erreur 500. Prévoir
en plus un blocage par type (`<FilesMatch "\.ph(p[0-9]?|tml)$"> Require all denied`)
et, pour les livrables privés, un stockage **hors webroot** : `files.stored_path`
pointe vers `/public/uploads`, donc tout fichier livré serait téléchargeable par
quiconque devine l'URL, sans contrôle de droits.

### S5 — `Model::insert()` interpole les noms de colonnes
`app/Core/Model.php:43-57` : les **clés** du tableau `$data` sont injectées
telles quelles dans le SQL (`INSERT INTO table ($fields)`). Les valeurs sont
liées (sûres), mais si un futur contrôleur passe `$_POST` brut, les noms de
colonnes deviennent un vecteur d'injection. Constat : aucune validation/liste
blanche dans la classe. Règle consignée dans `AI_RULES.md` §4.1 en attendant un
garde-fou dans le code.

### S6 — Configuration versionnée et environnement `dev`
`config/config.php` est commité avec ses identifiants (root/vide — défaut XAMPP,
tolérable en local seulement) et `APP_ENV = 'dev'` (erreurs affichées). Il n'y a
**aucun mécanisme de config locale non versionnée** : le passage en prod exigera
de modifier un fichier suivi par Git, avec risque d'écrasement.

### S7 — Dépendance CDN sans intégrité
GSAP et ScrollTrigger (`index.html:293-294`) sont chargés depuis cdnjs **sans
attribut `integrity` (SRI) ni `crossorigin`**. Une compromission du CDN =
exécution de script arbitraire sur le site. Version épinglée (3.12.5) : ✅.

---

## 🟡 Dette technique

### D1 — « 15 tables » annoncées, **17 réelles**
`database/schema.sql` crée 17 tables (comptage des `CREATE TABLE`), mais son
en-tête, `CLAUDE.md`, `README.md` et `health.php:36` (« 15 attendues », test
`>= 15`) disent 15. Incohérence documentaire pure — la base est correcte.

### D2 — Soft delete incomplet
Règle 6 de `CLAUDE.md` : « on ne supprime jamais vraiment ». Or `deleted_at`
n'existe que sur `users` et `suppliers`. Manquant sur `orders`, `projects`,
`clients`, `employees`, `services`, `invoices` — précisément les données métier
à tracer.

### D3 — i18n non câblée
`lang/fr|ar|en.php` existent (11 clés chacun, cohérents) mais **aucun code ne
les charge** ; `index.html` est en français codé en dur ; aucun support RTL en
CSS alors que l'arabe est requis. Les 11 clés ne couvrent d'ailleurs que la
navigation, pas le contenu des sections.

### D4 — `index.html` statique vs futures vues PHP : risque de duplication
La page d'accueil vit dans un fichier statique. En Phase 5, la nav (« Connexion »)
et le contenu devront devenir dynamiques (session, langue) : sans conversion en
vue PHP, on maintiendra **deux versions divergentes** de la même page. Prévoir la
migration `index.html → app/Views/home.php` dès la création du front controller.

### D5 — Menu burger : styles injectés en JavaScript
`animations.js:219-228` construit le menu mobile en **styles inline**
(`Object.assign(liens.style, ...)`) : casse la séparation CSS/JS, valeurs en dur
(couleur `#fff`, `top: 64px`), impossible à thémer, et le menu ne se referme pas
après clic sur un lien. Devrait être une classe CSS (`.nav__links.is-open`).

### D6 — Accessibilité du burger et des lignes services
- Burger : pas d'`aria-expanded`, pas d'`aria-controls`, état non annoncé aux lecteurs d'écran.
- `.service` (`index.html:112-131`) : `<li>` stylés `cursor:pointer` mais **sans lien ni gestion clavier** — un utilisateur clavier ne peut pas les activer, et ils ne mènent d'ailleurs nulle part (pas de page service).
- Contraste faible : `.trust__list li` à 30 % d'opacité (~4.5:1 non atteint) — tolérable car décoratif, à surveiller.

### D7 — Images non optimisées
8 JPG pour **~3,9 Mo** (6 fichiers entre 458 et 663 Ko). Aucun WebP/AVIF, aucun
`width`/`height` sur les `<img>` (décalages de mise en page — CLS), aucun
`loading="lazy"` sur les images sous la ligne de flottaison (réalisations),
aucun `srcset` responsive.

### D8 — Pas de `.gitignore`
Risque à court terme : commit du contenu de `public/uploads/`, du futur
`vendor/` Composer, des fichiers locaux (`.vscode/`, dumps).

### D9 — Divers
- `messages.order_id` sans contrainte FK (seul « lien » du schéma sans FK).
- `README.md` décrit des dossiers inexistants (`Controllers/`, `Models/`, `Views/`, `Middleware/`, `index.php`) sans préciser qu'ils sont à venir.
- Pas de favicon ; pas de balises Open Graph/canonical ; pas de `robots.txt`/`sitemap.xml` ; pas de données structurées LocalBusiness — SEO de base incomplet pour un site vitrine.
- Coordonnées incohérentes entre `install.php` (`admin@digitalsmile.dz`, `+213542054123`) et le footer (`arezki69@gmail.com`, `+213549562205`) — probablement volontaire (compte système vs contact public), à confirmer.
- Témoignage fictif en ligne (marqué « EXEMPLE À REMPLACER » — bien) : à remplacer avant toute mise en ligne réelle.
- Aucun test automatisé, aucune CI.

---

## ✅ Points forts

1. **Couche PDO exemplaire** : singleton, `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, **émulation de requêtes préparées désactivée**, messages d'erreur différenciés dev/prod (`Database.php`).
2. **Schéma de base soigné** : utf8mb4 partout, InnoDB, FKs nommées, index pertinents (statuts, jointures), ENUM pour les machines à états, seed réaliste, commentaires pédagogiques.
3. **password_hash (bcrypt)** dès l'installation ; le seed SQL refuse volontairement de créer l'admin en SQL — bonne pratique expliquée dans le fichier même.
4. **Code et config hors webroot** + réécriture racine vers `public/` + uploads non exécutables : la topologie de sécurité est la bonne dès le départ.
5. **Accessibilité mouvement** : `prefers-reduced-motion` respecté à la fois en CSS (`motion.css`) et en JS (garde globale) — rare à ce niveau de soin.
6. **Progressive enhancement réel** : classe `html.js`, rien n'est masqué sans JavaScript ; scripts en fin de body ; `preconnect` fonts ; scroll listener passif ; `gsap.quickTo` pour les suivis souris.
7. **Commentaires français pédagogiques systématiques** dans 100 % des fichiers PHP/CSS/JS/SQL — l'objectif « défendre chaque ligne » est tenu.
8. **Design system cohérent** : tokens CSS centralisés, BEM-light homogène, `clamp()` fluide, découpage CSS par responsabilité.
9. **Honnêteté des données** : clients réels, RC réel, chiffres réels ; le seul contenu fictif (témoignage) est explicitement marqué.
