# EXECUTIVE_REPORT.md — Rapport exécutif

> Synthèse pour le propriétaire, dérivée de l'audit complet du 2026-07-03
> (`docs/AUDIT_REPORT.md`). Le projet est en **fin de fondations** (Phase 3-4
> sur 8) : les notes mesurent l'existant, pas le potentiel.

---

## Notes de maturité (/10)

| Dimension | Note | Justification (un constat d'audit chacune) |
|---|---|---|
| **Architecture** | 6/10 | Topologie exemplaire (code hors webroot, couche PDO propre, schéma 17 tables soigné) mais le front controller — pièce centrale du MVC — n'existe pas encore (AUDIT C1). |
| **Documentation** | 8/10 | CLAUDE.md + commentaires français systématiques dans 100 % des fichiers, désormais complétés par la base `docs/` ; pénalisée par l'incohérence « 15 vs 17 tables » et un README décrivant des dossiers inexistants (D1, D9). |
| **Sécurité** | 5/10 | Excellents réflexes (PDO préparé sans émulation, bcrypt, uploads non exécutables) mais mot de passe admin en clair dans le code et l'historique Git, aucun en-tête HTTP de sécurité, livrables futurs publiquement téléchargeables (C2, S1, S4). |
| **Maintenabilité** | 8/10 | Conventions homogènes (BEM-light, tokens CSS, en-têtes de fichiers), découpage par responsabilité — seule ombre : styles du burger injectés en JS (D5). |
| **Évolutivité** | 6/10 | Le schéma (RBAC, départements, machine à états) absorbera la croissance prévue ; mais i18n non câblée et page d'accueil statique créeront de la double maintenance dès la Phase 5 (D3, D4). |
| **UX** | 7/10 | Page d'accueil au standard international (intro, reveals, image suiveuse, reduced-motion respecté) ; mais liens de nav en placeholders, lignes services sans destination et menu mobile fragile (D5, D6). |
| **Performance** | 5/10 | Bonnes bases (animations GPU, scripts non bloquants, preconnect) plombées par ~3,9 Mo d'images sans WebP, ni dimensions, ni lazy loading, et une requête vidéo 404 à chaque visite (D7, C3). |
| **Prêt pour la production** | 3/10 | Rien de la checklist prod n'existe : APP_ENV=dev, root MySQL sans mot de passe, install.php/health.php exposés, aucune sauvegarde, pas de HTTPS (AUDIT S3, S6 ; DEPLOYMENT.md §3). Normal à ce stade, mais à dire clairement. |

**Moyenne : 6/10** — fondations saines et inhabituellement bien commentées pour
un projet de cette taille ; les faiblesses sont concentrées, connues et peu
coûteuses à corriger (phase A du roadmap ≈ 1 jour).

## Top 3 des actions recommandées

1. **Neutraliser le risque identifiants** (AUDIT C2) : réécrire `install.php` pour
   saisir le mot de passe au lieu de l'embarquer, changer le mot de passe admin,
   puis supprimer install.php/health.php de tout environnement accessible.
   *Effort : < 1 h. C'est l'unique faille exploitable aujourd'hui.*

2. **Créer le front controller `public/index.php` + Router minimal** (AUDIT C1) :
   tout le reste (auth, espaces, workflow) s'appuie dessus, et les `.htaccess`
   l'attendent déjà. En profiter pour convertir l'accueil en vue PHP et brancher
   l'i18n (évite la double maintenance identifiée en D3/D4).
   *Effort : ~1 jour. Débloque les Phases 5 à 8.*

3. **Régime images + en-têtes de sécurité** (AUDIT D7, S1) : conversion WebP
   (< 150 Ko/image), `width/height`, `loading="lazy"`, et 4 lignes d'en-têtes
   HTTP dans le `.htaccess`. *Effort : ~2 h. Gain immédiat de vitesse — argument
   commercial pour une agence qui vend du digital — et de sécurité.*

## Prochaine étape suggérée

Exécuter la **Phase A du roadmap** (5 items, ~1 jour) puis enchaîner sur la
Phase 5 du projet (authentification + 3 espaces) avec `AI_RULES.md` comme
garde-fou. Aucune refonte n'est nécessaire : le socle mérite d'être poursuivi
tel quel.
