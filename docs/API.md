# API.md — Interfaces HTTP

> État : **Authentification implémentée** (register / login / logout — 2026-07-07).
> Le reste des routes métier reste planifié (§2).

---

## 1. État réel

L'application est (et restera en v1) une application **rendue côté serveur** :
PHP génère le HTML, les formulaires postent vers des routes PHP classiques.
Il n'existe **aucun endpoint REST/JSON** dans le codebase, et aucun n'est requis
par le périmètre v1.

Seules pages HTTP existantes aujourd'hui :

| URL | Méthode | Rôle | Statut |
|---|---|---|---|
| `/` (racine du site) | GET | Accueil, servi par le front controller (`HomeController` → `index.html`) | ✅ Depuis le 05/07/2026 |
| `/public/index.html` | GET | Page d'accueil statique (accès direct) | ✅ Existe |
| `/public/health.php` | GET | Bilan de santé (dev) | ✅ Existe — à supprimer en prod |
| `/public/install.php` | GET+POST | Création du compte admin — **formulaire + jeton CSRF** (plus de mot de passe en dur) | ✅ Refondu le 05/07/2026 — à supprimer après usage |
| `/register` | GET+POST | Inscription client. GET = formulaire ; POST (`csrf`, `name`, `email`, `password`≥8) = crée le user (rôle `client`, `password_hash` BCRYPT), ouvre la session, redirige `/client` | ✅ Depuis le 07/07/2026 |
| `/login` | GET+POST | Connexion tous rôles. GET = formulaire ; POST (`csrf`, `email`, `password`) = `password_verify` → session + `session_regenerate_id`, redirige selon le rôle (`admin`→`/admin`, `employee`→`/employe`, `client`→`/client`). Échec = message générique | ✅ Depuis le 07/07/2026 |
| `/logout` | GET | Détruit la session + le cookie, redirige `/` | ✅ Depuis le 07/07/2026 |
| `/client` | GET | Tableau de bord client : bouton « nouvelle demande » + tableau de ses commandes (numéro, service, statut, budget, échéance, date). Garde `require_role('client')` — non connecté → `/login`, autre rôle → **403** | ✅ Depuis le 07/07/2026 |
| `/client/nouvelle-demande` | GET+POST | Demande de projet. GET = formulaire (service, description, budget optionnel, échéance) ; POST (`csrf`, `service_id`, `description`, `budget`, `deadline`) = valide puis crée la commande (`code` DS-AAAA-NNNN, statut `pending`) → redirige `/client`. Garde `require_role('client')` | ✅ Depuis le 07/07/2026 |
| `/client/commande/{numero}` | GET | Détail d'une commande (infos + frise de statut). **Propriété** vérifiée par le numéro ; sinon 404. Garde `require_role('client')` | ✅ Depuis le 08/07/2026 |
| `/client/commande/{numero}/telecharger` | GET | Télécharge le livrable **en flux via PHP** (Content-Disposition, nom d'origine ; chemin réel jamais exposé). Propriété vérifiée. Garde `require_role('client')` | ✅ Depuis le 08/07/2026 |
| `/client/commande/{numero}/confirmer` | POST | `csrf` + propriété + statut `delivered` → statut `completed` → redirige au détail avec flash. Garde `require_role('client')` | ✅ Depuis le 08/07/2026 |
| `/employe` | GET | Tableau de bord employé : bouton « mes tâches » + nombre de tâches assignées. Garde `require_role('employee')` | ✅ Depuis le 07/07/2026 |
| `/employe/taches` | GET | Tâches assignées (cartes : infos, barre de progression, formulaires). Garde `require_role('employee')` | ✅ Depuis le 08/07/2026 |
| `/employe/taches/progression` | POST | `csrf` + `project_id` + `progress` (0-100). Contrôle de propriété ; borne 0..100 ; à 100 % la commande passe `delivered`, sinon `in_progress`. Garde `require_role('employee')` | ✅ Depuis le 08/07/2026 |
| `/employe/taches/livrer` | POST (multipart) | `csrf` + `project_id` + `file`. Contrôle de propriété + fichier (PDF/JPG/PNG/ZIP, 10 Mo max, extension+MIME, nom aléatoire) → ligne `files` + commande `delivered`. Garde `require_role('employee')` | ✅ Depuis le 08/07/2026 |
| `/admin` | GET | Tableau de bord admin : boutons « gérer les demandes » + « gérer l'équipe » + nombre de demandes en attente. Garde `require_role('admin')` | ✅ Depuis le 07/07/2026 |
| `/admin/commandes` | GET | Revue des demandes : demandes en attente (avec actions) + vue d'ensemble + liste des employés. Garde `require_role('admin')` | ✅ Depuis le 07/07/2026 |
| `/admin/employes` | GET+POST | Gestion de l'équipe. GET = formulaire + liste des employés. POST (`csrf`, `name`, `email`, `password`≥8, `department_id`) = crée un compte employé (rôle `employee`, `password_hash` BCRYPT, fiche `employees` liée) → redirige avec flash. Garde `require_role('admin')` | ✅ Depuis le 07/07/2026 |
| `/admin/commandes/approuver` | POST | `csrf` + `order_id` → statut `approved`. Garde `require_role('admin')` | ✅ Depuis le 07/07/2026 |
| `/admin/commandes/refuser` | POST | `csrf` + `order_id` → statut `rejected`. Garde `require_role('admin')` | ✅ Depuis le 07/07/2026 |
| `/admin/commandes/affecter` | POST | `csrf` + `order_id` + `employee_id` → statut `in_progress` + crée/maj le `projects` (employé, statut `assigned`). Garde `require_role('admin')` | ✅ Depuis le 07/07/2026 |
| `/admin/factures` | GET | Facturation : commandes terminées à facturer + factures émises. Garde `require_role('admin')` | ✅ Depuis le 08/07/2026 |
| `/admin/factures/generer` | POST | `csrf` + `order_id` → crée `FAC-AAAA-NNNN` (HT = budget, TVA 19 %, TTC) si la commande est `completed` et pas déjà facturée. Garde `require_role('admin')` | ✅ Depuis le 08/07/2026 |
| `/admin/factures/{numero}` | GET | Détail d'une facture (société + client + commande + montants). Numéro extrait du chemin. Garde `require_role('admin')` | ✅ Depuis le 08/07/2026 |
| `/notifications` | GET | Notifications de l'utilisateur (cloche + liste). Ouvrir la page marque tout comme lu. Garde `require_login` (tous rôles connectés) — sinon → `/login` | ✅ Depuis le 08/07/2026 |
| toute autre URL | * | Réécrite vers `index.php?url=...` → **page 404 propre** (`app/Views/errors/404.php`, code HTTP 404) | ✅ Depuis le 05/07/2026 |

## 2. Routes prévues (Phase 5-6) — plan indicatif, non implémenté

Ces routes serviront le workflow des commandes via le front controller
(`public/index.php?url=...`). Elles rendront du HTML (pas du JSON), sauf mention.

> `/register`, `/login`, `/logout` sont **implémentés** (voir §1).
> `/client/orders` et `/client/orders/new` sont **implémentés** sous les URLs
> `/client` et `/client/nouvelle-demande` (voir §1).
> `/admin/orders` (+ approve/reject) est **implémenté** sous `/admin/commandes`
> (+ `/approuver`, `/refuser`) et l'**affectation** à un employé via
> `/admin/commandes/affecter` (voir §1). L'affectation ici est **manuelle**
> (l'assignation automatique par département reste à faire).
> `/employee/projects` (+ progress/deliver) est **implémenté** sous
> `/employe/taches` (+ `/progression`, `/livrer`) (voir §1).
> `/client/orders/{code}` (+ download) est **implémenté** sous
> `/client/commande/{numero}` (+ `/telecharger`, `/confirmer`) (voir §1).
> Le Router faisant une correspondance exacte, le numéro est extrait du
> chemin dans `public/index.php` (regex `[A-Za-z0-9\-]+`).

| Route (cible) | Méthode | Rôle métier | Accès (RBAC) |
|---|---|---|---|
| `/admin/invoices/{code}` | GET | **Implémenté** (admin) sous `/admin/factures/{numero}` (voir §1). Export PDF (Dompdf) + accès client concerné : encore à faire | admin / client concerné |

## 3. Règles non négociables pour toute future route (cf. `AI_RULES.md`)

1. **Toute mutation = POST** (jamais GET) **+ jeton CSRF** vérifié côté serveur.
2. Contrôle de rôle (middleware RBAC) **avant** la logique métier.
3. Transitions de statut validées côté serveur (machine à états d'`orders.status`) — jamais confiance au client.
4. Paramètres liés PDO ; sortie échappée `htmlspecialchars()`.
5. Uploads : extension/MIME whitelistés, nom de stockage régénéré, taille limitée, stockage idéalement **hors webroot** (voir AUDIT).
6. Toute action sensible journalisée dans `activity_logs`.

> **TODO** : ce tableau est un plan déduit du schéma de base et de `CLAUDE.md`.
> Le figer (noms d'URL définitifs) au démarrage de la Phase 5.
