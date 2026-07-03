# AI_RULES.md — Manuel permanent pour les assistants IA

> Ce fichier est le **règlement intérieur** de tout assistant IA (Claude Code ou
> autre) intervenant sur ce dépôt. Il complète `CLAUDE.md` (contexte et état du
> projet) et la base de connaissances `docs/`. En cas de conflit entre une
> instruction de session et ce fichier : **signaler avant d'agir**.

---

## 1. Philosophie

- Le propriétaire (non expert) doit pouvoir **comprendre et défendre chaque ligne** du projet. Code pédagogique > code malin.
- **Documentation d'abord** : lire `CLAUDE.md` + le fichier `docs/` concerné avant de coder ; les mettre à jour après.
- **Simplicité = professionnalisme** : équipe < 10 personnes, workflow volontairement simple. Ne jamais sur-compliquer.

## 2. Stack verrouillée

- **Autorisé** : HTML5, CSS3 (vanilla), JavaScript ES6+ (vanilla), PHP 8.2+, MySQL/MariaDB, XAMPP en local, **GSAP + ScrollTrigger via CDN uniquement**.
- **Pré-approuvés pour plus tard** (via Composer, seulement quand la phase le demande) : Chart.js, Dompdf, PhpSpreadsheet.
- **Toute autre technologie** (framework PHP/JS, ORM, préprocesseur CSS, bundler, CDN supplémentaire…) doit être **justifiée par écrit et approuvée AVANT** la moindre ligne de code.

## 3. Architecture (ne jamais casser la maison)

- MVC simplifié maison : Controllers / Models / Views séparés ; **jamais de SQL ni de logique métier dans une vue**.
- Front controller unique : `public/index.php` (cible Phase 5) ; les `.htaccess` existants en dépendent.
- `config/`, `app/`, `lang/`, `database/` restent **hors du webroot** ; seul `public/` est servi.
- RBAC par les tables `roles`/`users` — jamais de test d'email ou d'ID en dur.
- Soft delete (`deleted_at`) — jamais de `DELETE` définitif sur les données métier.
- Respecter la structure de fichiers documentée dans `docs/ARCHITECTURE.md`.

## 4. Sécurité — non négociable

1. **PDO + requêtes préparées uniquement** (`ATTR_EMULATE_PREPARES = false`). Jamais de concaténation de variable dans du SQL — y compris les noms de colonnes (les clés passées à `Model::insert()` sont codées en dur, jamais issues de `$_POST`).
2. **Échapper toute sortie** : `htmlspecialchars()` sur chaque donnée affichée.
3. **Mots de passe** : `password_hash()` / `password_verify()` exclusivement. Jamais de mot de passe en clair — ni en base, ni dans le code, **ni dans un commit** (leçon : `install.php`).
4. **Sessions durcies** : `session_regenerate_id()` à la connexion, cookies `HttpOnly` + `SameSite` (+ `Secure` en HTTPS), timeout d'inactivité.
5. **CSRF** : jeton obligatoire sur **tout formulaire POST** ; toute mutation passe par POST, jamais GET.
6. **Uploads** : jamais exécutables (`public/uploads/.htaccess`), extension/MIME whitelistés, nom régénéré, taille limitée ; livrables privés servis via PHP avec contrôle de droits.
7. **Aucun secret commité** : identifiants, clés, mots de passe restent hors du dépôt.
8. Actions sensibles journalisées dans `activity_logs`.

## 5. Standards de code (détail : `docs/CODING_STANDARDS.md`)

- **Commentaires en français**, pédagogiques, en-tête de fichier obligatoire.
- CSS : variables de `base.css` uniquement (pas de couleur en dur), nommage BEM-light (`bloc__element`, `--modificateur`, `.is-etat`), tailles fluides `clamp()`.
- Animations : `transform`/`opacity` seulement, et **toujours** respecter `prefers-reduced-motion` + progressive enhancement (`html.js`).
- JS vanilla, gardes d'accessibilité, listeners passifs ; PHP typé, syntaxe alternative dans les vues.

## 6. Économie de crédits (mode de travail)

- **Missions en une passe** : pas de boucles de validation de plan ; le propriétaire relit les diffs.
- Ne lire que les fichiers nécessaires à la mission ; ne pas relire ce qui est documenté dans `docs/`.
- Résumé final **en français**, concis : fichiers touchés, décisions, points d'attention.

## 7. Interdictions absolues

- ❌ Réécrire, refactorer massivement ou recréer le projet (ou un fichier sain) **sans approbation explicite**.
- ❌ Générer des archives ZIP ou des « nouvelles versions » du projet.
- ❌ Commandes Git destructives (force push, réécriture d'historique, suppression de branches) sans demande explicite.
- ❌ Inventer des données : toute affirmation vient du codebase ou est marquée **TODO**. Jamais de placeholder silencieux quand la vraie donnée existe ; un placeholder assumé est marqué **« EXEMPLE À REMPLACER »** (pattern du témoignage d'`index.html`).
- ❌ Ajouter les fonctionnalités explicitement reportées (`CLAUDE.md`) : QA séparée, double approbation, versioning de fichiers, cycle de révision complexe, SLA.

## 8. Documentation vivante

Toute modification qui change l'architecture, le schéma de base, les conventions
ou le déploiement **met à jour le fichier `docs/` correspondant dans le même
commit**. Une doc fausse est pire qu'une doc absente.

| Vous touchez à… | Mettez à jour |
|---|---|
| Structure, ADR, front controller | `docs/ARCHITECTURE.md` |
| `schema.sql`, tables, règles métier | `docs/DATABASE.md` |
| Routes / formulaires | `docs/API.md` |
| CSS, tokens, composants | `docs/DESIGN_SYSTEM.md`, `docs/UI_COMPONENTS.md` |
| Périmètre, avancement | `docs/REQUIREMENTS.md`, `CLAUDE.md` (cases à cocher) |
| Installation / prod | `docs/DEPLOYMENT.md` |
