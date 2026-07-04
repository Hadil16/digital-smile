# ARCHITECTURE.md — Architecture technique

> Généré depuis le codebase réel (2026-07-03).

---

## 1. Structure réelle du dépôt (constatée, pas théorique)

```
digital-smile/
├── .htaccess                  # Redirige TOUT le trafic racine vers public/
├── CLAUDE.md                  # Instructions projet pour Claude Code
├── AI_RULES.md                # Règles permanentes pour les assistants IA
├── README.md                  # Guide d'installation (5 étapes XAMPP)
├── app/
│   ├── Controllers/
│   │   └── HomeController.php # Sert l'accueil (readfile de index.html, cf. B3)
│   ├── Core/
│   │   ├── Database.php       # Singleton PDO (ERRMODE_EXCEPTION, FETCH_ASSOC, no emulate)
│   │   ├── Model.php          # Classe mère abstraite : find / all / insert préparés
│   │   └── Router.php         # Routeur minimal : table chemin => callable, 404 sinon
│   └── Views/
│       └── errors/
│           └── 404.php        # Page 404 autonome (styles intégrés)
├── config/
│   └── config.php             # Constantes DB, BASE_URL, LANGUAGES, APP_ENV
├── database/
│   └── schema.sql             # 17 tables + seed (rôles, 12 départements, 7 catégories)
├── docs/                      # Base de connaissances (ce dossier)
├── lang/
│   ├── fr.php  ├── ar.php  └── en.php   # 11 clés chacun — PAS ENCORE CHARGÉS par du code
└── public/                    # SEUL dossier destiné à être servi
    ├── .htaccess              # Front controller : tout → index.php?url=$1
    ├── index.php              # FRONT CONTROLLER : config + routes + dispatch
    ├── index.html             # Page d'accueil v2 statique (Huge x Digital Smile)
    ├── health.php             # Bilan de santé (dev uniquement, à supprimer en prod)
    ├── install.php            # Création admin — À SUPPRIMER après usage
    ├── assets/
    │   ├── css/ base.css · layout.css · sections.css · motion.css
    │   ├── img/ 8 JPG (logo, hero, 6 visuels services/réalisations)
    │   └── js/  animations.js (GSAP 3 + ScrollTrigger via CDN)
    └── uploads/
        └── .htaccess          # php_flag engine off (PHP désactivé)
```

**Dossiers annoncés (README) mais pas encore créés** : `app/Models/`,
`app/Middleware/` (viendront avec la Phase 5 — auth + RBAC).

## 2. Décisions d'architecture (ADR courts)

### ADR-1 — PHP pur, sans framework
- **Décision** : PHP 8 + MySQL sans Laravel/Symfony (`CLAUDE.md`).
- **Raison** : le propriétaire veut comprendre et défendre chaque ligne.
- **Conséquence** : le socle (routing, auth, CSRF) est écrit maison — d'où les règles strictes de `AI_RULES.md`.

### ADR-2 — PDO uniquement, requêtes préparées, émulation désactivée
- **Décision** : `Database.php` impose `PDO::ATTR_EMULATE_PREPARES => false` ; toute requête passe par `prepare()`/paramètres liés.
- **Raison** : vraies requêtes préparées côté MySQL = protection injection SQL maximale.
- **Statut** : implémenté (`app/Core/Database.php`, `app/Core/Model.php`).
- **Vigilance** : `Model::insert()` interpole les **noms de colonnes** depuis les clés du tableau — n'appeler qu'avec des clés codées en dur, jamais `$_POST` brut (voir AUDIT 🟠).

### ADR-3 — Front controller unique (`public/index.php`)
- **Décision** : toutes les URL passent par un point d'entrée unique ; `public/.htaccess` réécrit déjà `(.*)` → `index.php?url=$1`.
- **Statut** : **implémenté** (2026-07-05, item A1 roadmap) — `public/index.php` charge config + classes, déclare les routes et dispatch via `app/Core/Router.php` (table `chemin => callable`, 404 sinon). Volontairement minimal : pas de paramètres dynamiques tant qu'aucune phase n'en a besoin.

### ADR-4 — RBAC en base (1 table `users` + 1 table `roles`)
- **Décision** : un seul `users` pour admin/employé/client, le rôle décide des droits. Jamais de `if email == admin` en dur.
- **Statut** : schéma + seed implémentés ; middleware d'autorisation à écrire.

### ADR-5 — Soft delete
- **Décision** : colonne `deleted_at`, jamais de `DELETE` réel (traçabilité).
- **Statut** : **partiellement appliqué** — seulement `users` et `suppliers` ont `deleted_at` (voir AUDIT 🟡).

### ADR-6 — GSAP + ScrollTrigger via CDN (cloudflare)
- **Décision** : seule bibliothèque front autorisée, chargée en fin de `<body>` (non bloquante). Pas de framework JS.
- **Raison** : animations professionnelles (validées par l'architecte) sans complexifier la stack.
- **Conséquence** : dépendance réseau externe (voir AUDIT — SRI/version à figer) et progressive enhancement obligatoire (implémenté via `html.js`).

### ADR-7 — Config et code hors de `public/`
- **Décision** : `config/`, `app/`, `lang/`, `database/` inaccessibles par le web ; le `.htaccess` racine réécrit tout vers `public/`.
- **Statut** : implémenté. En production, la cible reste `DocumentRoot = public/` (voir `docs/DEPLOYMENT.md`).

## 3. État réel vs cible

| Brique | Cible (CLAUDE.md) | État réel constaté |
|---|---|---|
| Front controller | `public/index.php` reçoit toutes les requêtes | ✅ Implémenté : `index.php` + `Router` + page 404 |
| Controllers / Models / Views | MVC simplifié complet | ⚠️ Amorcé : `HomeController`, `Views/errors/404.php` ; `Models/` et `Middleware/` restent à créer (Phase 5) |
| Base de données | 15 tables annoncées | ✅ Schéma complet — **17 tables réelles** |
| Connexion DB | PDO sécurisé singleton | ✅ `Database.php` |
| Authentification + RBAC | Login + 3 espaces | ❌ Uniquement les tables |
| Multilingue FR/AR/EN + RTL | Textes servis depuis `lang/` | ⚠️ Fichiers présents, **non câblés** ; page d'accueil en dur en FR ; pas de RTL |
| Page d'accueil | Vitrine "Huge x Digital Smile" | ✅ `index.html` statique complet (⚠️ devra être converti en vue PHP en Phase 5) |
| Workflow commandes | 6 étapes, machine à états | ❌ ENUM en base uniquement |
| Fichiers / uploads | Dépôt sécurisé | ⚠️ Dossier + `.htaccess` prêts ; aucune logique d'upload |
| Facturation | FAC-AAAA-NNNN, TVA 19 % | ❌ Tables seules |
| Vidéo héros | `assets/video/cubes-logo.mp4` | ❌ Fichier absent — la balise `<source>` a été neutralisée (plus de 404) ; `hero.jpg` affiché en attendant, bloc `<video>` prêt en commentaire dans `index.html` |
| Bibliothèques Composer | Chart.js, Dompdf, PhpSpreadsheet si besoin | ❌ Composer non initialisé (aucun `composer.json`) — normal à ce stade |

## 4. Flux d'une requête (cible Phase 5)

```
Navigateur → Apache → public/.htaccess → public/index.php (front controller)
    → Router (parse ?url=)  → Middleware (session, rôle, CSRF)
    → Controller            → Model (PDO préparé)      → MySQL
    → View (échappement htmlspecialchars) → HTML
```

État au 2026-07-05 : le trajet `Navigateur → .htaccess → index.php → Router
→ HomeController → index.html` (ou → `Views/errors/404.php`) fonctionne.
Les étapes Middleware et Model arriveront avec la Phase 5.
