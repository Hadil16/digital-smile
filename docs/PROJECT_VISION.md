# PROJECT_VISION.md — Vision du projet Digital Smile

> Document généré à partir du codebase réel (audit du 2026-07-03).
> Toute information absente du code est marquée **TODO**.

---

## 1. L'entreprise

| Élément | Valeur (source : codebase) |
|---|---|
| Nom | **Digital Smile** — agence de branding & communication |
| Slogan | « Digital Like Never Before » (`lang/fr.php`, `index.html`) |
| Fondation | **2022** (stat `data-count="2022"` dans `index.html`) |
| Registre de commerce | **RC 5146243 A 22** (footer `index.html`) |
| Adresse | Cité 1200 Logts, Bt S, Local N° 17, **Bab Ezzouar, Alger** |
| Contact | +213 (0) 549 56 22 05 — arezki69@gmail.com (footer) |
| Propriétaire | **Yahiaoui Arezki** (`install.php`, `CLAUDE.md`) |
| Effectif | **Moins de 10 employés** (`CLAUDE.md`) |
| Clients de référence | SONATRACH, GRANITEX, BONAPRO, EPTP ALGER, ARIZONA, RAHA DZ (trust bar `index.html`) |
| Expertises | 12 départements métiers (seed `database/schema.sql`) |

## 2. Le problème

L'agence gère aujourd'hui ses commandes, projets, fichiers et factures **sans outil
centralisé**. Le parcours client (demande → validation → production → livraison →
facturation) n'est pas tracé numériquement.

> **TODO** : le codebase ne contient pas de description formelle du processus manuel
> actuel (volume de commandes/mois, temps de traitement). À collecter auprès du
> propriétaire pour quantifier le gain.

## 3. La vision

Une **mini-plateforme web de gestion** (pas un simple site vitrine) avec **3 espaces** :

1. **Espace client** — demander un service, suivre l'avancement, télécharger les livrables et factures.
2. **Espace employé** — recevoir les projets assignés automatiquement, mettre à jour la progression, déposer les fichiers finaux.
3. **Espace admin** — valider/refuser les commandes, superviser, consulter les statistiques.

Le workflow est **volontairement simple** (6 étapes, une seule approbation) car
l'équipe compte moins de 10 personnes — décision explicite du propriétaire (`CLAUDE.md`).

## 4. Objectifs mesurables

> **TODO** : aucun objectif chiffré (KPI) n'est défini dans le codebase.
> Recommandation : fixer avec le propriétaire, par exemple —
> délai moyen de validation d'une commande, % de commandes suivies dans l'outil,
> délai d'émission de facture après livraison. À documenter ici une fois validés.

Objectifs qualitatifs déductibles du code :
- **Traçabilité totale** : table `activity_logs` (qui a fait quoi, quand, depuis quelle IP).
- **Numérotation professionnelle** : commandes `DS-2026-0001`, factures `FAC-2026-0001`.
- **Image de marque** : page d'accueil au standard international (typo géante, GSAP), « le digital algérien mérite un standard international » (section Believe, `index.html`).
- **Trilinguisme** : FR / AR (avec RTL) / EN.

## 5. Hors périmètre v1 (décisions explicites, `CLAUDE.md`)

Reportés à la croissance de l'entreprise — **ne pas implémenter sans nouvelle décision** :
- QA séparée / double approbation
- Versioning de fichiers
- Cycle de révision complexe
- Suivi de SLA

## 6. État d'avancement (constaté dans le code)

- ✅ Fondations : base de données (17 tables), connexion PDO, config, multilingue (fichiers), sécurité de base.
- ✅ Page d'accueil v2 statique (`public/index.html` + CSS + GSAP).
- ⏳ À venir : pages À propos/Services/Contact, authentification, les 3 espaces, workflow des commandes, dashboard, factures (voir `docs/REQUIREMENTS.md` et `docs/ROADMAP.md`).
