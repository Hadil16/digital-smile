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
| toute autre URL | * | Réécrite vers `index.php?url=...` → **page 404 propre** (`app/Views/errors/404.php`, code HTTP 404) | ✅ Depuis le 05/07/2026 |

## 2. Routes prévues (Phase 5-6) — plan indicatif, non implémenté

Ces routes serviront le workflow des commandes via le front controller
(`public/index.php?url=...`). Elles rendront du HTML (pas du JSON), sauf mention.

> `/register`, `/login`, `/logout` sont désormais **implémentés** (voir §1).

| Route (cible) | Méthode | Rôle métier | Accès (RBAC) |
|---|---|---|---|
| `/client/orders` | GET | Mes commandes | client |
| `/client/orders/new` | GET+POST | Formulaire de demande de projet (F6) → statut `pending` | client |
| `/client/orders/{code}` | GET | Détail, progression, livrables, facture | client propriétaire |
| `/admin/orders` | GET | File des demandes en attente | admin |
| `/admin/orders/{code}/approve` | POST | Valider → `approved` + assignation auto (département du service) | admin |
| `/admin/orders/{code}/reject` | POST | Refuser → `rejected` | admin |
| `/employee/projects` | GET | Mes projets assignés | employé |
| `/employee/projects/{id}/progress` | POST | Mettre à jour le % (0-100) | employé assigné |
| `/employee/projects/{id}/deliver` | POST (multipart) | Déposer le livrable → `delivered` | employé assigné |
| `/client/orders/{code}/download/{fileId}` | GET | Télécharger un livrable **via PHP avec contrôle de droits** | client propriétaire |
| `/admin/invoices/{code}` | GET | Facture (HTML, PDF via Dompdf en Phase 8) | admin / client concerné |

## 3. Règles non négociables pour toute future route (cf. `AI_RULES.md`)

1. **Toute mutation = POST** (jamais GET) **+ jeton CSRF** vérifié côté serveur.
2. Contrôle de rôle (middleware RBAC) **avant** la logique métier.
3. Transitions de statut validées côté serveur (machine à états d'`orders.status`) — jamais confiance au client.
4. Paramètres liés PDO ; sortie échappée `htmlspecialchars()`.
5. Uploads : extension/MIME whitelistés, nom de stockage régénéré, taille limitée, stockage idéalement **hors webroot** (voir AUDIT).
6. Toute action sensible journalisée dans `activity_logs`.

> **TODO** : ce tableau est un plan déduit du schéma de base et de `CLAUDE.md`.
> Le figer (noms d'URL définitifs) au démarrage de la Phase 5.
