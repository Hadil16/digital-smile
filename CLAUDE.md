> 📚 Règles permanentes pour les assistants IA : [AI_RULES.md](AI_RULES.md) · Base de connaissances complète : [docs/](docs/) — à consulter avant toute intervention.

# CLAUDE.md — Instructions du projet Digital Smile

> Ce fichier est lu automatiquement par Claude Code à l'ouverture du projet.
> Il contient TOUT le contexte décidé avec l'architecte. Lis-le en entier
> avant de proposer ou modifier quoi que ce soit.

---

## 🎯 C'est quoi ce projet ?

Plateforme web de gestion pour l'agence de branding **Digital Smile**
(Bab Ezzouar, Alger). Ce n'est PAS un simple site vitrine : c'est une
mini-plateforme avec 3 espaces (client, employé, admin).

**Propriétaire :** Yahiaoui Arezki. Équipe : **moins de 10 employés**.

---

## 🧱 Stack technique (NE PAS changer sans demander)

- **PHP pur + MySQL** (PAS de framework : ni Laravel, ni Symfony).
  Raison : le propriétaire veut comprendre et défendre chaque ligne.
- **XAMPP** (Apache + PHP + MySQL) en local.
- Bibliothèques autorisées, uniquement si besoin : Chart.js, Dompdf,
  PhpSpreadsheet, via Composer. Rien d'autre sans validation.

---

## 📐 Règles d'architecture (STRICTES)

1. **MVC simplifié** : Controllers / Models / Views séparés. Jamais de
   SQL ou de logique métier dans une vue.
2. **Front Controller** : tout passe par `public/index.php`.
3. **PDO + requêtes préparées UNIQUEMENT**. Jamais de concaténation de
   variable dans une requête SQL (risque d'injection).
4. **RBAC** : une seule table `users` + table `roles` (admin/employee/client).
   Jamais de "if email == admin" en dur.
5. **Mots de passe** : toujours `password_hash()`. Jamais en clair.
6. **Soft delete** : on met `deleted_at`, on ne supprime jamais vraiment.
7. Config et code hors du dossier `public/` (sécurité).

---

## 🗺️ Le workflow (VOLONTAIREMENT SIMPLE)

Le propriétaire a explicitement demandé de NE PAS compliquer. Équipe < 10.
Le parcours d'une commande, en 6 étapes :

1. Le client demande un service et remplit le formulaire de projet.
2. L'admin valide (accepte / refuse) — une seule approbation, simple.
3. Le système assigne automatiquement à un employé du bon département.
4. L'employé exécute et met à jour le % de progression.
5. L'employé dépose le fichier final.
6. Le client récupère + télécharge + la facture est générée.

**NE PAS ajouter** (reporté à plus tard, quand l'entreprise grandira) :
QA séparée, double approbation, versioning de fichiers, cycle de révision
complexe, SLA tracking. Le garder SIMPLE.

Statuts d'une commande : `pending` → `approved` → `in_progress`
→ `delivered` → `completed`. (+ `rejected`, `cancelled`).

---

## 🗄️ Base de données

15 tables, définies dans `database/schema.sql`. Groupes :
- Identité : `roles`, `users`
- Organisation : `departments`, `clients`, `employees`, `suppliers`
- Catalogue : `service_categories`, `services`
- Workflow : `orders`, `projects`, `tasks`
- Fichiers : `files`
- Finances : `invoices`, `payments`
- Communication : `messages`, `notifications`, `activity_logs`

**Identifiants auto** : commandes = `DS-2026-0001`, factures = `FAC-2026-0001`.

---

## 🌍 Multilingue

3 langues : **FR (défaut), AR, EN**. Fichiers dans `lang/`.
L'arabe nécessite le support **RTL** (right-to-left) dans le CSS.

---

## 🎨 Identité visuelle

- Violet/indigo : `#4A3F9E` (approximatif)
- Vert lime : `#8BC63F` (approximatif)
- Blanc / gris pour les fonds.
- Logo : `public/assets/img/logo.jpg`
- Slogan : "Digital Like Never Before"
- Style visuel souhaité : épuré, moderne, beaucoup d'espace (inspiration Apple).

---

## ✅ État d'avancement

- [x] Phase 1 : Analyse métier
- [x] Phase 2 : Architecture + base de données
- [x] Phase 3 : Fondations (Database, Model, config, sécurité, multilingue)
- [x] Phase 4 : Page d'accueil v1 (Apple White Reveal) — remplacée par v2
- [x] Phase 4 v2 : Refonte "Huge x Digital Smile" (typo géante, intro flash,
      services 01-05 avec image au survol, believe scroll, chiffres, GSAP)
      -> index.html + css/{base,layout,sections,motion}.css + js/animations.js
      -> Vidéo héros : assets/video/cubes-logo.mp4 (à placer manuellement)
      -> Chiffres réels : 2022 (RC ...A 22), 30+ clients, 12 expertises
- [ ] Phase 4b : Pages À propos, Services, Contact (formulaire)
- [ ] Phase 5 : Authentification + les 3 espaces
- [ ] Phase 6 : Workflow des commandes
- [ ] Phase 7 : Dashboard admin + statistiques
- [ ] Phase 8 : Factures, notifications, tests

---

## ⚠️ Rappels importants pour Claude Code

- Toujours EXPLIQUER une décision avant de coder.
- Toujours proposer un plan et attendre l'accord pour les grosses étapes.
- Qualité production : maintenable, sécurisé, lisible.
- Le propriétaire n'est pas expert : commenter le code en français, simplement.
- Ne PAS sur-compliquer. Simplicité = professionnalisme ici.
