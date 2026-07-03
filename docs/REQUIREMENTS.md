# REQUIREMENTS.md — Exigences fonctionnelles et non fonctionnelles

> Généré depuis le codebase réel (2026-07-03). Statuts : **FAIT** = code présent et
> fonctionnel · **PARTIEL** = amorce présente · **À FAIRE** = uniquement préparé en
> base de données ou planifié dans `CLAUDE.md`.

---

## 1. Exigences fonctionnelles

| ID | Exigence | Statut | Preuve dans le code |
|----|----------|--------|---------------------|
| F1 | Page d'accueil vitrine (héros vidéo, services, réalisations, processus, chiffres, CTA) | **FAIT** | `public/index.html`, `assets/css/*`, `assets/js/animations.js` |
| F2 | Pages À propos, Services (détail), Contact avec formulaire | **À FAIRE** | Phase 4b de `CLAUDE.md` ; liens de nav pointent vers des ancres internes |
| F3 | Multilingue FR / AR / EN avec RTL pour l'arabe | **PARTIEL** | `lang/fr.php`, `lang/ar.php`, `lang/en.php` existent (11 clés chacun) mais **aucun code ne les charge** ; `index.html` est en français codé en dur ; aucun CSS RTL |
| F4 | Authentification (connexion / inscription) | **À FAIRE** | Table `users` + `password_hash` prêtes ; aucun contrôleur/vue de login |
| F5 | RBAC 3 rôles (admin / employee / client) | **PARTIEL** | Tables `roles` + `users.role_id` créées et seedées ; aucun middleware d'autorisation |
| F6 | Espace client : formulaire de demande de projet (nom, marque, couleurs, style, deadline, budget, priorité) | **À FAIRE** | Colonnes correspondantes dans `orders` (schema.sql) |
| F7 | Validation admin d'une commande (accepter / refuser) | **À FAIRE** | Statuts `pending/approved/rejected` dans l'ENUM `orders.status` |
| F8 | Assignation automatique à un employé du bon département | **À FAIRE** | Routage prêt : `services.department_id` + `employees.department_id` |
| F9 | Suivi de progression par l'employé (0–100 %) | **À FAIRE** | `projects.progress TINYINT` + statuts projet |
| F10 | Dépôt de fichiers (références client / livrables employé) | **À FAIRE** | Table `files` (kind ENUM), dossier `public/uploads/` non exécutable |
| F11 | Téléchargement du livrable par le client | **À FAIRE** | `files.stored_path` prévu |
| F12 | Facturation automatique (TVA 19 %, code FAC-AAAA-NNNN) | **À FAIRE** | Tables `invoices` (tax_rate défaut 19.00) + `payments` |
| F13 | Messagerie liée aux commandes | **À FAIRE** | Table `messages` |
| F14 | Notifications internes | **À FAIRE** | Table `notifications` (title, body, link, is_read) |
| F15 | Journal d'activité (audit trail) | **À FAIRE** | Table `activity_logs` |
| F16 | Numérotation lisible auto : `DS-2026-0001` / `FAC-2026-0001` | **À FAIRE** | Colonnes `orders.code` / `invoices.code` UNIQUE ; logique de génération absente |
| F17 | Page de bilan de santé du système | **FAIT** | `public/health.php` (PHP, PDO, tables, uploads, langues) |
| F18 | Création sécurisée du compte admin | **FAIT** | `public/install.php` (idempotent, password_hash) — ⚠️ à supprimer après usage |
| F19 | Dashboard admin + statistiques | **À FAIRE** | Phase 7 `CLAUDE.md` ; Chart.js pré-approuvé |
| F20 | Export PDF des factures | **À FAIRE** | Dompdf pré-approuvé (`CLAUDE.md`), non installé |

### Machine à états d'une commande (définie dans `schema.sql`)

```
pending → approved → in_progress → delivered → completed
        ↘ rejected                 (+ cancelled à tout moment)
```
Statuts projet : `assigned → in_progress → review → done`.
Statuts facture : `unpaid → partial → paid`.

## 2. Exigences non fonctionnelles

| ID | Exigence | Statut | Preuve / Écart |
|----|----------|--------|----------------|
| NF1 | **Sécurité — injections SQL** : PDO + requêtes préparées uniquement, `ATTR_EMULATE_PREPARES = false` | **FAIT** (socle) | `app/Core/Database.php`, `app/Core/Model.php` |
| NF2 | **Sécurité — mots de passe** : `password_hash()` (bcrypt), jamais en clair en base | **FAIT** | `install.php` ; ⚠️ mot de passe par défaut en clair dans le code source (voir AUDIT) |
| NF3 | **Sécurité — config hors webroot** | **FAIT** | `config/config.php` hors de `public/` + rewrite racine vers `public/` |
| NF4 | **Sécurité — uploads non exécutables** | **FAIT** (avec réserve) | `public/uploads/.htaccess` (`php_flag engine off` — ne vaut que sous mod_php, voir AUDIT) |
| NF5 | **Sécurité — CSRF sur tous les POST** | **À FAIRE** | Aucun formulaire POST n'existe encore ; règle actée dans `AI_RULES.md` |
| NF6 | **Sécurité — en-têtes HTTP** (CSP, X-Frame-Options, etc.) | **À FAIRE** | Absents des `.htaccess` |
| NF7 | **Soft delete** : `deleted_at`, jamais de suppression réelle | **PARTIEL** | Présent sur `users` et `suppliers` uniquement ; absent d'`orders`, `projects`, `clients`, `employees`, `services`, `invoices` (voir AUDIT) |
| NF8 | **Accessibilité — mouvement réduit** | **FAIT** | `@media (prefers-reduced-motion)` dans `motion.css` + garde JS dans `animations.js` |
| NF9 | **Accessibilité — clavier / ARIA** | **PARTIEL** | `aria-hidden` et `aria-label` posés ; menu burger et lignes services non accessibles clavier (voir AUDIT) |
| NF10 | **Performance — animations GPU** (transform/opacity uniquement) | **FAIT** | `motion.css`, `animations.js` (quickTo, will-change ciblé) |
| NF11 | **Performance — images** (WebP, dimensions, lazy loading) | **À FAIRE** | JPG de 450–660 Ko, aucun `width/height`, aucun `loading="lazy"` |
| NF12 | **Progressive enhancement** : site lisible sans JavaScript | **FAIT** | Classe `html.js` posée en JS ; sans JS rien n'est masqué (`motion.css`) |
| NF13 | **Maintenabilité** : commentaires en français, code défendable ligne par ligne | **FAIT** | Tous les fichiers PHP/CSS/JS sont commentés en français |
| NF14 | **Simplicité** : pas de framework, pas de sur-ingénierie | **FAIT** | PHP pur, 2 classes Core, CSS vanilla, GSAP seul ajout |
| NF15 | **Encodage** : utf8mb4 partout (support arabe + emojis) | **FAIT** | `schema.sql`, `config.php` (DB_CHARSET) |

## 3. Liste TODO consolidée (manques constatés)

1. **`public/index.php` (front controller) n'existe pas** alors que `public/.htaccess` redirige déjà tout vers lui — toute URL propre renvoie actuellement une erreur 404.
2. **Système i18n non câblé** : aucun loader des fichiers `lang/*.php`, pas de sélecteur de langue, pas de CSS RTL.
3. **Dossiers `app/Controllers/`, `app/Models/`, `app/Views/`, `app/Middleware/` absents** (annoncés dans `README.md`).
4. **Logique de génération des codes** `DS-AAAA-NNNN` / `FAC-AAAA-NNNN` à écrire.
5. **Aucun `.gitignore`** (risque : commit de `uploads/`, de `vendor/` futur, de configs locales).
6. **Vidéo héros `public/assets/video/cubes-logo.mp4` absente** (référencée par `index.html`).
7. **Incohérence documentaire** : « 15 tables » annoncées partout, **17 réellement créées** (voir `docs/DATABASE.md`).
8. **Aucun test automatisé** (aucun framework de test présent).
