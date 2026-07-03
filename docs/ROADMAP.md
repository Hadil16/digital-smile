# ROADMAP.md — Feuille de route priorisée

> Dérivée de l'audit (`docs/AUDIT_REPORT.md`) et des phases de `CLAUDE.md`.
> Chaque item : **pourquoi / bénéfice attendu / priorité P1-P3 / complexité S-M-L**.
> P1 = bloquant ou risque réel · P2 = important · P3 = confort. S < 1h · M = ½-1 j · L > 1 j.

---

## Phase A — Critique (avant tout autre développement)

| Item | Pourquoi | Bénéfice | Prio | Cplx |
|---|---|---|---|---|
| A1. Créer le front controller `public/index.php` + Router minimal | Les `.htaccess` pointent vers un fichier absent (AUDIT C1) ; toute la Phase 5 en dépend | Fondation MVC opérationnelle, URLs propres | P1 | M |
| A2. Retirer le mot de passe en dur d'`install.php` (formulaire de saisie) + changer le mot de passe admin | Identifiants en clair dans le web et l'historique Git (AUDIT C2) | Compte admin non compromis | P1 | S |
| A3. Placer la vidéo `assets/video/cubes-logo.mp4` (compressée) ou retirer la balise `<source>` | 404 à chaque visite, promesse design non tenue (AUDIT C3) | Héros conforme au design validé | P1 | S |
| A4. Créer `.gitignore` (uploads/*, vendor/, fichiers locaux) | Aucun garde-fou Git (AUDIT D8) | Dépôt propre, pas de fuite de fichiers clients | P1 | S |
| A5. Corriger « 15 tables » → 17 (schema.sql, README, CLAUDE.md, health.php) | Incohérence documentaire (AUDIT D1) | Docs fiables, health-check juste | P1 | S |

## Phase B — Architecture (Phases 5-6 du projet)

| Item | Pourquoi | Bénéfice | Prio | Cplx |
|---|---|---|---|---|
| B1. Authentification par sessions durcies + middleware RBAC | Cœur des 3 espaces ; tables prêtes | Espaces sécurisés par rôle | P1 | L |
| B2. Infrastructure CSRF + helper d'échappement pour les vues | Non négociable avant le premier formulaire (`AI_RULES.md`) | Mutations protégées dès le jour 1 | P1 | M |
| B3. Convertir `index.html` en vue PHP (layout + partials nav/footer) | Éviter la double maintenance statique/dynamique (AUDIT D4) | Une seule source pour l'accueil, nav dynamique | P2 | M |
| B4. Câbler l'i18n : loader `lang/`, sélecteur, session `lang`, étendre les clés | Fichiers présents mais morts (AUDIT D3) ; requis FR/AR/EN | Site réellement trilingue | P2 | M |
| B5. Support RTL (attribut `dir`, propriétés logiques CSS) | L'arabe est illisible sans RTL | Marché arabophone servi correctement | P2 | M |
| B6. Workflow commandes : formulaire client → validation admin → assignation auto → progression → livraison | Raison d'être de la plateforme (F6-F11) | Le métier tourne dans l'outil | P1 | L |
| B7. Génération transactionnelle des codes `DS-`/`FAC-` | Colonnes UNIQUE sans logique (F16) ; risque de collision si naïf | Numérotation fiable | P1 | S |
| B8. Ajouter `deleted_at` aux tables métier + FK `messages.order_id` | Règle soft delete incomplète (AUDIT D2, D9) | Traçabilité promise tenue | P2 | S |
| B9. Uploads : validation MIME/extension/taille, noms régénérés, livrables hors webroot servis via PHP | `stored_path` public = livrables téléchargeables sans droits (AUDIT S4) | Fichiers clients réellement privés | P1 | M |

## Phase C — UX

| Item | Pourquoi | Bénéfice | Prio | Cplx |
|---|---|---|---|---|
| C1. Menu burger en classe CSS `.is-open` (+ fermeture au clic lien) | Styles inline JS, menu qui ne se referme pas (AUDIT D5) | Mobile propre et maintenable | P2 | S |
| C2. Pages À propos / Services / Contact avec formulaire (Phase 4b) | Liens de nav = ancres placeholder ; aucun canal de contact structuré | Parcours visiteur complet, génération de leads | P2 | M |
| C3. Relier les lignes services 01-05 à de vraies pages/ancres | `<li>` cliquables qui ne mènent nulle part (AUDIT D6) | Signature visuelle qui sert la conversion | P2 | S |
| C4. Remplacer témoignage fictif + visuels d'illustration par les vrais contenus | Marqués « EXEMPLE À REMPLACER » dans le HTML | Crédibilité, conformité (accord écrit client) | P2 | S |
| C5. États UX du futur portail : messages d'erreur/succès de formulaires, page 404 | Rien n'existe (pas même de 404 propre — cf. C1 AUDIT) | Plateforme perçue comme finie | P2 | M |

## Phase D — Performance

| Item | Pourquoi | Bénéfice | Prio | Cplx |
|---|---|---|---|---|
| D1. Compresser/convertir les 8 images en WebP (+ JPG fallback), viser < 150 Ko chacune | ~3,9 Mo d'images (AUDIT D7) | LCP divisé, data mobile DZ économisée | P1 | S |
| D2. Ajouter `width`/`height` sur toutes les `<img>` | Décalages de mise en page (CLS) | Score Core Web Vitals, confort visuel | P2 | S |
| D3. `loading="lazy"` sur les images sous la ligne de flottaison | Tout charge d'un coup | Premier rendu plus rapide | P2 | S |
| D4. Compresser la vidéo héros (H.264, ~1-2 Mo, courte boucle) quand elle arrivera | Une vidéo lourde ruinerait tout le reste | Héros fluide même en 4G | P2 | S |
| D5. Ajouter SRI (`integrity`) aux scripts GSAP CDN, ou auto-héberger | Dépendance CDN non vérifiée (AUDIT S7) | Sécurité + perf maîtrisées | P2 | S |

## Phase E — SEO

| Item | Pourquoi | Bénéfice | Prio | Cplx |
|---|---|---|---|---|
| E1. Favicon + balises Open Graph/Twitter + canonical | Absents (AUDIT D9) ; partages réseaux sociaux sans visuel | Marque pro dans les partages — vital pour une agence de branding | P2 | S |
| E2. Données structurées JSON-LD `LocalBusiness` (adresse Bab Ezzouar, RC, téléphone) | Rien n'existe | Fiche Google riche, SEO local Alger | P2 | S |
| E3. `robots.txt` + `sitemap.xml` (quand les pages existeront) | Absents | Indexation contrôlée (et exclusion de health/install !) | P3 | S |
| E4. Un seul `<h1>` conservé, hiérarchie `h2/h3` vérifiée à chaque nouvelle page | Bon actuellement, à ne pas casser | SEO on-page stable | P3 | S |

## Phase F — Accessibilité

| Item | Pourquoi | Bénéfice | Prio | Cplx |
|---|---|---|---|---|
| F1. Burger : `aria-expanded`, `aria-controls`, navigation clavier | Non annoncé aux lecteurs d'écran (AUDIT D6) | Menu utilisable par tous | P2 | S |
| F2. Lignes services accessibles clavier (liens réels, focus visible) | `<li>` souris-uniquement (AUDIT D6) | Conformité clavier | P2 | S |
| F3. Audit contrastes (trust bar 30 %, gris sur blanc) | Ratios limite | Lisibilité WCAG AA | P3 | S |
| F4. Skip-link « aller au contenu » + focus géré après fermeture de l'intro | Standard des sites à overlay | Navigation clavier fluide | P3 | S |

## Phase G — Tests

| Item | Pourquoi | Bénéfice | Prio | Cplx |
|---|---|---|---|---|
| G1. Tests unitaires PHP (PHPUnit via Composer — à faire approuver) sur Model, Router, génération DS-/FAC-, transitions de statut | Zéro test (AUDIT D9) ; la machine à états est le cœur métier | Régressions bloquées avant la prod | P2 | M |
| G2. Checklist de test manuel par rôle (client/employé/admin) documentée | Équipe non technique, pas de CI | Recette reproductible à chaque phase | P2 | S |
| G3. Test santé étendu (health.php protégé ou CLI) : uploads, langues, tables, mail | health.php existe déjà, base saine | Diagnostic 1-clic | P3 | S |

## Phase H — Déploiement

| Item | Pourquoi | Bénéfice | Prio | Cplx |
|---|---|---|---|---|
| H1. Checklist prod exécutée (voir `docs/DEPLOYMENT.md`) : DocumentRoot=public/, HTTPS, APP_ENV=prod, utilisateur MySQL dédié, suppression install/health | Rien n'est fait ; défauts XAMPP inacceptables en ligne | Mise en ligne sans faille évidente | P1 (au moment du déploiement) | M |
| H2. En-têtes de sécurité (CSP, nosniff, frame-ancestors, Referrer-Policy) | Absents (AUDIT S1) | Protection clickjacking/XSS renforcée | P1 | S |
| H3. Config locale non versionnée (`config.local.php` ignoré par Git) | Config commitée (AUDIT S6) | Secrets hors du dépôt | P2 | S |
| H4. Sauvegardes automatiques BDD + uploads, restauration testée | Aucune stratégie | Survie aux pannes/erreurs | P1 | M |
| H5. Choix de l'hébergeur (support PHP 8.2, MySQL, HTTPS) | Non décidé (TODO) | Décision éclairée avant la Phase 8 | P2 | — |

---

## Ordre d'exécution recommandé

**A (tout, ~1 jour)** → **B1-B2-B7-B9** → **B3-B4-B5** → **B6** → **C** → **D-E-F** (groupables) → **G** → **H** au moment de la mise en ligne. Les phases D1, E1 et H2 sont si peu coûteuses qu'elles peuvent s'intercaler à tout moment.
