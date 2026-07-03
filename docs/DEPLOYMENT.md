# DEPLOYMENT.md — Installation et déploiement

> Procédure locale constatée (README + fichiers) et exigences de production (TODO).

---

## 1. Installation locale (XAMPP) — procédure vérifiée

1. **Copier** le dossier dans `C:\xampp\htdocs\digital-smile` (le `BASE_URL` de `config/config.php` suppose ce chemin : `/digital-smile/public`).
2. **Démarrer** Apache + MySQL (XAMPP Control Panel).
3. **Créer la base** : phpMyAdmin → Importer → `database/schema.sql`.
   - Crée `digital_smile` (utf8mb4) + **17 tables** + seed (3 rôles, 12 départements, 7 catégories).
   - ⚠️ Le script commence par `DROP TABLE IF EXISTS` : le ré-importer **efface toutes les données**.
4. **Vérifier** : `http://localhost/digital-smile/public/health.php` → tout doit être ✅ (PHP 8+, PDO MySQL, tables, uploads inscriptible, langues).
5. **Créer l'admin** : ouvrir **une seule fois** `http://localhost/digital-smile/public/install.php`.
   - Compte créé : `admin@digitalsmile.dz` / mot de passe défini dans le fichier (bcrypt en base). Idempotent (détecte un admin existant).
6. **⚠️ SUPPRIMER ensuite `public/install.php` ET `public/health.php`.**
   - `install.php` contient le mot de passe admin **en clair dans le source** et l'affiche à l'écran : tant qu'il existe, n'importe qui y accédant connaît les identifiants.
   - `health.php` divulgue des informations d'infrastructure (versions, état de la base).
   - Les deux fichiers le rappellent eux-mêmes dans leurs commentaires.
7. Config XAMPP requise : `mod_rewrite` actif et `AllowOverride All` sur `htdocs` (les 3 `.htaccess` en dépendent). C'est le défaut XAMPP.

## 2. Pré-requis techniques (constatés dans le code)

- PHP **8.0+** (contrôlé par `health.php`), extension `pdo_mysql`.
- MySQL/MariaDB avec support `utf8mb4`.
- Apache avec `mod_rewrite` (front controller) et, si mod_php n'est pas utilisé, une **alternative au `php_flag engine off`** du dossier uploads (voir AUDIT 🟠).

## 3. Mise en production — TODO (rien n'est fait, checklist obligatoire)

### Serveur & Apache
- [ ] **`DocumentRoot` pointé directement sur `public/`** — c'est la protection principale de `config/`, `app/`, `database/` (le `.htaccess` racine n'est qu'un filet local).
- [ ] **HTTPS obligatoire** (certificat, redirection 80→443, HSTS).
- [ ] En-têtes de sécurité (CSP, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`) — absents aujourd'hui (AUDIT 🟠).
- [ ] Adapter `BASE_URL` (`config/config.php`) au domaine réel.

### Configuration applicative
- [ ] `APP_ENV = 'prod'` (masque les erreurs — actuellement `'dev'`).
- [ ] **Utilisateur MySQL dédié** avec mot de passe fort et droits limités à `digital_smile` (actuellement `root` sans mot de passe = défaut XAMPP, inacceptable en prod).
- [ ] Externaliser les identifiants hors du dépôt (fichier local non commité) — `config.php` est aujourd'hui versionné avec ses valeurs.
- [ ] Supprimer `install.php` + `health.php` et **changer le mot de passe admin** (il figure dans l'historique Git — voir AUDIT 🔴).

### Données
- [ ] **Sauvegardes automatiques** de la base (mysqldump quotidien + rétention) et du dossier `uploads/`.
- [ ] Stratégie de restauration testée.
- [ ] Ne **jamais** rejouer `schema.sql` en production (DROP TABLE) ; prévoir des migrations additives.

### Contenus
- [ ] Déposer la vidéo héros `public/assets/video/cubes-logo.mp4` (référencée mais absente).
- [ ] Remplacer le témoignage fictif (« EXEMPLE À REMPLACER ») et les visuels d'illustration des réalisations par les vrais.
- [ ] Optimiser les images (~3,9 Mo actuellement — voir ROADMAP phase D).

## 4. Environnements

| | Local (actuel) | Production (cible) |
|---|---|---|
| Serveur | XAMPP (Apache, `htdocs/digital-smile`) | **TODO** : hébergeur non choisi dans le codebase |
| URL | `http://localhost/digital-smile/public` | TODO (HTTPS) |
| Webroot | `htdocs` + `.htaccess` de redirection | `public/` en DocumentRoot |
| DB | `root` sans mot de passe | utilisateur dédié, mot de passe fort |
| Erreurs | affichées (`dev`) | masquées (`prod`) + journalisation |
| install/health.php | présents | supprimés |
