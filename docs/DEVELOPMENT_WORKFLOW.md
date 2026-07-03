# DEVELOPMENT_WORKFLOW.md — Organisation du développement

> État constaté + processus cible (2026-07-03).

---

## 1. Les rôles (humains + IA)

| Rôle | Acteur | Responsabilité |
|---|---|---|
| **Propriétaire / décideur** | Yahiaoui Arezki | Valide les choix, doit pouvoir comprendre et défendre chaque ligne |
| **Architecte** | claude.ai (conversations de conception) | Analyse métier, décisions d'architecture, phases |
| **Développeur** | **Claude Code** (dans VS Code / terminal) | Implémente dans le dépôt, en suivant les règles |
| **Pont architecte ↔ développeur** | `CLAUDE.md` + `AI_RULES.md` | `CLAUDE.md` = contexte et état du projet ; `AI_RULES.md` = règles permanentes non négociables. Toute décision de l'architecte est reportée dans ces fichiers pour que le développeur la respecte |

Règle : si une instruction de session contredit `AI_RULES.md`, **signaler avant d'agir**.

## 2. Cycle de développement local (XAMPP)

1. **Démarrer** Apache + MySQL via le XAMPP Control Panel.
2. Le projet vit dans `C:\xampp\htdocs\digital-smile` (BASE_URL = `/digital-smile/public`).
3. **Base de données** : import de `database/schema.sql` via phpMyAdmin (⚠️ le script fait `DROP TABLE` — il réinitialise tout).
4. **Vérifier** : `http://localhost/digital-smile/public/health.php` (PHP 8+, PDO MySQL, 17 tables, uploads inscriptible, langues).
5. **Développer** : modifier le code, recharger le navigateur (pas de build front — CSS/JS vanilla, GSAP via CDN).
6. `APP_ENV = 'dev'` dans `config/config.php` : erreurs affichées. Passer à `'prod'` masque tout.

## 3. Convention Git

- **Messages de commit** : `type(scope): message` — types usuels : `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `chore`, `db`.
  - Exemples : `feat(auth): page de connexion avec sessions durcies` · `db(orders): génération du code DS-AAAA-NNNN` · `docs: mise à jour du roadmap`.
- **État réel** : le dépôt ne contient qu'un seul commit (« Initial commit ») — la convention s'applique à partir de maintenant.
- **Interdits** : commandes destructives (`push --force`, réécriture d'historique) sans décision explicite ; commit de secrets ; commit du contenu de `public/uploads/`.
- **TODO** : créer un `.gitignore` (uploads, `vendor/`, fichiers locaux) — voir AUDIT.

## 4. Cycle d'une évolution (processus cible)

1. **Décision** : discutée avec l'architecte (claude.ai), consignée dans `CLAUDE.md` (phases) ou `docs/`.
2. **Mission Claude Code** : une mission = un périmètre clair, **en une passe** (économie de crédits, cf. `AI_RULES.md`) ; le propriétaire relit les diffs lui-même.
3. **Implémentation** : respecter `docs/CODING_STANDARDS.md` + `AI_RULES.md`.
4. **Vérification** : `health.php` + test manuel du parcours dans le navigateur.
5. **Commit** : `type(scope): message`, uniquement les fichiers concernés.
6. **Documentation vivante** : si l'architecture, la base ou les règles changent, mettre à jour le fichier `docs/` correspondant **dans le même commit**.

## 5. Ordre des phases (rappel `CLAUDE.md`)

Phase 4b (pages À propos/Services/Contact) → Phase 5 (auth + 3 espaces, dont le
front controller `public/index.php`) → Phase 6 (workflow commandes) → Phase 7
(dashboard admin) → Phase 8 (factures, notifications, tests).
Le détail priorisé est dans `docs/ROADMAP.md`.
