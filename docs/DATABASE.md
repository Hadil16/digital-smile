# DATABASE.md — Base de données `digital_smile`

> Généré depuis `database/schema.sql` (2026-07-03).
> Moteur : MySQL/MariaDB (InnoDB) · Encodage : `utf8mb4_unicode_ci` (FR/AR/EN + emojis).

---

## ⚠️ Comptage réel : 17 tables (et non 15)

L'en-tête de `schema.sql`, `README.md`, `CLAUDE.md` et `health.php` annoncent
**15 tables**, mais le script crée réellement **17 tables** (comptage des
`CREATE TABLE`). Le test de `health.php` (`>= 15`) passe quand même, mais son
libellé « 15 attendues » est trompeur. → Corriger les mentions « 15 » en « 17 »
(voir AUDIT 🟡).

## 1. Les 17 tables par groupe

| Groupe | Tables | Rôle |
|---|---|---|
| 1. Identité & sécurité | `roles`, `users` | RBAC : 3 rôles seedés (admin/employee/client), une seule table users |
| 2. Organisation | `departments`, `clients`, `employees`, `suppliers` | 12 départements seedés ; profils client/employé liés à `users` (1-1) |
| 3. Catalogue | `service_categories`, `services` | 7 catégories seedées ; chaque service pointe vers UN département (clé du routage auto) |
| 4. Workflow | `orders`, `projects`, `tasks` | Commande client → projet assigné (progress 0-100) → tâches optionnelles |
| 5. Fichiers | `files` | Bidirectionnel : `kind` = 'reference' (client) ou 'deliverable' (employé) |
| 6. Finances | `invoices`, `payments` | TVA défaut **19 %** (Algérie), paiements multiples par facture |
| 7. Communication & traçabilité | `messages`, `notifications`, `activity_logs` | Messages liés aux commandes ; notifications ciblées ; journal d'audit (user, action, entité, IP) |

## 2. Données seedées par `schema.sql`

- **3 rôles** : admin, employee, client (avec `label_fr`).
- **12 départements** : Logo Designer, Brand Identity Designer, UI/UX Designer, Web Developer, Mobile Developer, Graphic Designer, Content Writer, Marketing Team, Customer Support, Accountant, Project Manager, Audiovisual Production.
- **7 catégories de services** : Conception, Impression, Audiovisuel, Web, Marketing, QR Codes, Formation.
- **Aucun utilisateur** : le compte admin est créé par `public/install.php` (voir règle 4 ci-dessous).

## 3. Règles métier inscrites dans le schéma

1. **Requêtes préparées obligatoires** : la couche d'accès (`app/Core/`) n'offre que du PDO préparé, émulation désactivée.
2. **Soft delete** : `deleted_at TIMESTAMP NULL` — présent sur `users` et `suppliers`. ⚠️ **Incohérence** : absent d'`orders`, `projects`, `clients`, `employees`, `services`, `invoices` alors que `CLAUDE.md` (règle 6) l'exige partout. À harmoniser (AUDIT 🟡).
3. **Numérotation lisible** : `orders.code` (ex. `DS-2026-0001`) et `invoices.code` (ex. `FAC-2026-0001`), tous deux `VARCHAR(20) UNIQUE`. La **logique de génération n'existe pas encore** (à écrire en Phase 6, avec verrou/transaction contre les doublons).
4. **Mot de passe jamais en SQL** : le seed ne crée volontairement pas d'admin ; `install.php` le crée via `password_hash()` (bcrypt) — commentaire explicite en fin de `schema.sql`.
5. **Machines à états (ENUM)** :
   - `orders.status` : pending → approved/rejected → in_progress → delivered → completed (+ cancelled)
   - `projects.status` : assigned → in_progress → review → done
   - `invoices.status` : unpaid → partial → paid
   - `orders.priority` : low / medium / high / urgent
6. **Routage automatique** : `services.department_id` + `employees.department_id` = base de l'assignation auto d'un projet à un employé du bon département (F8).
7. **Intégrité référentielle** : FKs partout ; `ON DELETE CASCADE` uniquement là où le parent emporte l'enfant (clients/employees ← users, tasks/files ← projects, notifications ← users).
8. **Index** : email + rôle (users), statut + client (orders), employé (projects), projet (tasks/files), statut (invoices), facture (payments), commande (messages), destinataire+lu (notifications), user et entité (activity_logs).

## 4. Points de vigilance (détail en AUDIT_REPORT)

- `files.stored_path` pointe vers `/public/uploads` : les livrables seront **publiquement téléchargeables par URL** — prévoir un stockage hors webroot + téléchargement contrôlé par PHP (droits) avant la Phase 6.
- `schema.sql` commence par `DROP TABLE IF EXISTS ...` : **ré-importer efface tout**. Acceptable en dev, interdit en production sans sauvegarde.
- `messages.order_id` n'a pas de contrainte FK vers `orders` (seule colonne « lien » sans FK du schéma) — à ajouter ou justifier.

## 5. Procédure d'installation (constatée dans README/health/install)

1. Importer `database/schema.sql` dans phpMyAdmin → crée la base + 17 tables + seed.
2. Vérifier via `http://localhost/digital-smile/public/health.php`.
3. Exécuter **une fois** `public/install.php` → crée l'admin (bcrypt).
4. **Supprimer `install.php` et `health.php`** (rappelé par les deux fichiers eux-mêmes).
