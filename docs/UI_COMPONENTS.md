# UI_COMPONENTS.md — Inventaire des composants réels

> Extrait de `public/index.html` + CSS/JS associés (2026-07-03). Chaque composant
> liste ses classes réelles et son comportement observable.

---

## Ordre d'apparition dans la page

### 1. Intro flash — `.intro`
- **Classes** : `.intro`, `.intro__word`, `.intro__counter` · `aria-hidden="true"`.
- **Comportement (JS)** : overlay blanc plein écran (z-index 2000). Compteur 0→100 %, 3 mots flashés (« Bonjour. » / « Nous sommes » / « Digital Smile »), puis rideau vers le haut. Jouée **une fois par session** (`sessionStorage.introVue`). Clic = passer. Supprimée du DOM ensuite. Désactivée si `prefers-reduced-motion`.

### 2. Navigation — `.nav`
- **Classes** : `.nav`, `.nav__logo`, `.nav__links`, `.nav__cta`, `.nav__burger` · état `.is-compact`.
- **Comportement** : fixe, verre dépoli (`backdrop-filter: blur(18px)`). Au scroll > 60px, JS ajoute `.is-compact` (padding et logo réduits, ombre). Liens avec soulignement vert animé `scaleX` gauche→droite. CTA « Connexion » = pilule violette (pointe vers `#contact` — placeholder en attendant l'auth).
- **Mobile (≤860px)** : liens cachés, burger `☰` affiché ; le menu déroulant est construit **en styles inline JS** (`animations.js` §⑨) — à migrer en classe CSS (voir AUDIT).

### 3. Héros — `.hero`
- **Classes** : `.hero`, `.hero__media`, `.hero__ghost`, `.hero__content`, `.hero__eyebrow`, `.hero__title` (avec `.line > span` et `.accent`), `.hero__sub`, `.hero__btns`.
- **Comportement** : vidéo de fond autoplay/muted/loop (`cubes-logo.mp4` — **fichier absent**, le poster `hero.jpg` prend le relais) sous un voile dégradé blanc. Titre géant révélé ligne par ligne (glissement `translateY(110%)` → 0, stagger 0.12s). Mot fantôme « SMILE » (24vw, violet 5 %) en parallax `scrub`. Masqué sur mobile.

### 4. Trust bar — `.trust`
- **Classes** : `.trust`, `.trust__label`, `.trust__list`.
- **Contenu réel** : SONATRACH, GRANITEX, BONAPRO, EPTP ALGER, ARIZONA, RAHA DZ (« factures 14-16 » d'après le commentaire HTML).
- **Comportement** : noms en typo discrète (30 % opacité), hover = violet + levée 3px.

### 5. Marquee — `.marquee`
- **Classes** : `.marquee`, `.marquee__track`, `.dot` · `aria-hidden="true"`.
- **Comportement** : bandeau noir, mots-clés services défilant en boucle infinie (contenu dupliqué + `translateX(-50%)`, 22s linéaire). Pause au survol. Stoppé si reduced-motion.

### 6. Services — `.services` ⭐ composant signature
- **Classes** : `.services`, `.services__list`, `.service` (avec `data-img`), `.service__name`, `.service__num`, `.services__reveal`.
- **Contenu** : 5 lignes numérotées 01–05 (Création graphique, Impression, Web & Digital, QR Codes, Audiovisuel).
- **Comportement** : au survol d'une ligne — fond gris clair, nom translaté de 18px et coloré violet, et **image flottante qui suit la souris** (`gsap.quickTo`, retard élastique 0.45s, image lue dans `data-img`). Entrée des lignes en cascade au scroll. Image désactivée sur mobile et sur appareils sans hover.
- ⚠️ Lignes `<li>` cliquables au style mais **sans lien ni accès clavier** (voir AUDIT).

### 7. Réalisations — `.works`
- **Classes** : `.works`, `.works__grid` (3 colonnes → 1 sur mobile), `.work`, `.work__media`, `.work__cat`, `.work__title`.
- **Contenu réel** : 6 projets authentiques (Bonapro ×3, Sonatrach, Granitex, twinshamis.com). Le commentaire HTML précise que les **visuels sont des illustrations à remplacer** par les photos réelles (les projets/clients, eux, sont authentiques).
- **Comportement** : zoom image 1.06 au survol, titre viré au violet, entrée en cascade (stagger 0.12s).

### 8. Processus — `.process`
- **Classes** : `.process`, `.process__grid` (4 colonnes → 2 sur mobile), `.step`, `.step__num`, `.step__title`, `.step__desc`.
- **Contenu** : 4 étapes (Brief & devis → Création → Validation → Livraison & suivi) — miroir marketing du futur portail client (commentaire HTML).
- **Style** : section violet foncé, numéros verts géants.

### 9. We believe — `.believe`
- **Classes** : `.believe`, `.believe__line` (+ `.accent`, `.muted`).
- **Comportement** : 3 phrases manifeste en typo géante, révélées une à une au scroll.

### 10. Chiffres — `.stats`
- **Classes** : `.stats`, `.stats__grid`, `.stat`, `.stat__value` (avec `data-count` / `data-suffix`), `.stat__label`.
- **Contenu réel** : 30+ clients, 12 expertises, 2022 (année de création).
- **Comportement** : compteurs animés de 0 à la valeur cible au scroll (1.6s).

### 11. Témoignage — `.quote`
- **Classes** : `.quote`, `.quote__text`, `.quote__author`.
- ⚠️ **Citation fictive**, explicitement marquée « EXEMPLE À REMPLACER » dans le HTML — ne jamais publier sans accord écrit d'un vrai client.

### 12. CTA final — `.cta`
- **Classes** : `.cta`, `.cta__title`.
- **Comportement** : « Discutons. » en typo maximale (jusqu'à 200px), bouton mailto `arezki69@gmail.com`. Ancre `#contact` (cible du CTA nav).

### 13. Footer — `.footer`
- **Classes** : `.footer`, `.footer__grid` (2fr 1fr 1fr → 1 colonne mobile), `.footer__bottom`.
- **Contenu réel** : identité + slogan, téléphone `+213 549 56 22 05`, email, adresse Bab Ezzouar, © 2026, **RC 5146243 A 22**.

## Composants transverses

| Composant | Classes / attributs | Comportement |
|---|---|---|
| Boutons | `.btn`, `.btn--primary`, `.btn--dark`, `.btn--marquee` (+ `.btn__track`) | Pilules ; magnétiques à la souris ; variante marquee au survol |
| Reveal générique | `.will-reveal` | Invisible si `html.js`, entre en `y:44→0` + fondu au scroll (start `top 88%`) |
| Accent | `.accent` | Colore un mot en vert marque, partout |
| Titre de section | `.sec-title` | Petit titre Poppins + tiret vert automatique `::after` |
