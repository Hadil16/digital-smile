# DESIGN_SYSTEM.md — Système de design Digital Smile

> Extrait du code réel : `public/assets/css/base.css`, `layout.css`, `sections.css`,
> `motion.css` et `index.html` (2026-07-03). Style directeur : typographie géante
> « inspiration Huge » sur fond blanc, épuré (référence Apple dans `CLAUDE.md`).

---

## 1. Tokens (variables CSS de `base.css :root`)

### Couleurs
| Token | Valeur | Usage constaté |
|---|---|---|
| `--purple` | `#4A3F9E` | Couleur de marque : chiffres, hovers, CTA nav |
| `--purple-dark` | `#26215C` | Footer, section Processus, texte sur bouton vert |
| `--green` | `#8BC63F` | Accent : `.accent`, tiret des titres, soulignés, boutons primaires |
| `--green-dark` | `#6BA02C` | Eyebrow héros, catégories de réalisations (meilleur contraste) |
| `--black` | `#14142b` | Texte principal (noir bleuté, pas #000) |
| `--gray` | `#6b6b7b` | Texte secondaire, labels |
| `--gray-light` | `#f5f5f7` | Fonds de sections (stats), hover des lignes services |
| `--white` | `#ffffff` | Fond général |

Règle : **aucune couleur en dur dans les CSS de sections** — tout passe par ces variables.

### Typographie
| Token | Valeur | Usage |
|---|---|---|
| `--font-display` | `'Poppins', system-ui, sans-serif` (graisses 600/700/800) | Titres, boutons, chiffres, marquee |
| `--font-body` | `'Inter', system-ui, sans-serif` (graisses 400/500/600) | Corps de texte |

Chargées via Google Fonts avec `preconnect` (`index.html`).

### Mise en page
| Token | Valeur | Usage |
|---|---|---|
| `--maxw` | `1280px` | Largeur max des contenus |
| `--pad` | `clamp(20px, 5vw, 60px)` | Marge latérale fluide, utilisée partout |

## 2. Échelle typographique (fluide, via `clamp()`)

| Élément | Taille | Particularités |
|---|---|---|
| Titre héros `.hero__title` | `clamp(52px, 11vw, 160px)` | Poppins 800, `line-height .98`, `letter-spacing -0.03em` — « le titre EST le design » |
| CTA final `.cta__title` | `clamp(60px, 14vw, 200px)` | Le plus grand du site |
| Mot fantôme `.hero__ghost` | `24vw` | Décoratif, violet à 5 % d'opacité, parallax |
| Believe `.believe__line` | `clamp(26px, 4.5vw, 56px)` | Storytelling, mots `.accent` (vert) et `.muted` (25 % opacité) |
| Nom de service `.service__name` | `clamp(26px, 5vw, 64px)` | Poppins 700 |
| Chiffres `.stat__value` | `clamp(54px, 9vw, 120px)` | Violet, Poppins 800 |
| Titres de section `.sec-title` | `clamp(15px, 1.6vw, 20px)` | **Signature maison** : petits, suivis d'un tiret vert `::after { content: " —" }` |
| Corps | 15–19px | Inter, `line-height 1.6` |

Principe : les titres de section sont volontairement **petits** ; ce sont les
contenus (typo géante) qui portent la hiérarchie visuelle.

## 3. Composants de base

### Boutons (`.btn`)
- Forme : pilule (`border-radius: 100px`), padding `16px 34px`, Poppins 600.
- `.btn--primary` : fond vert, texte violet foncé ; hover = ombre verte portée.
- `.btn--dark` : fond noir, hover = violet.
- `.btn--marquee` : texte répété (« Explorer » ×6) défilant au survol (`@keyframes btn-scroll`).
- Comportement JS : effet « magnétique » (le bouton suit légèrement la souris, force 0.25, via `gsap.quickTo`).

### Cartes et médias
- Rayons : 14–16px (images, menus). Ratio images : `aspect-ratio: 4/3`.
- Hover images réalisations : `scale(1.06)` en 0.7s `cubic-bezier(.2,.8,.2,1)`.

## 4. Règles de mouvement (motion.css + animations.js)

1. **Le CSS pose l'état initial, GSAP anime vers l'état final.** `html.js .will-reveal { opacity: 0 }` — sans JavaScript, rien n'est masqué (progressive enhancement).
2. **Uniquement `transform` et `opacity`** (GPU) ; `will-change` réservé aux 3 éléments réellement animés en continu.
3. **`prefers-reduced-motion: reduce`** : aucune animation JS (garde `reduceMotion` en tête d'`animations.js`), tout visible en CSS, intro désactivée, marquees stoppés.
4. Vocabulaire GSAP du site : reveals au scroll (`ScrollTrigger`, départ `y: 40-50`, `power3.out`, ~1s), staggers 0.1–0.12s, parallax `scrub` sur le mot fantôme, compteurs animés (`data-count`/`data-suffix`), `quickTo` pour le suivi souris (image services, boutons magnétiques).
5. **Intro flash** : une fois par session (`sessionStorage.introVue`), passable au clic, compteur 0→100 % + 3 mots flashés, rideau `yPercent: -100`.
6. Micro-transitions CSS : 0.25–0.35s `ease` (nav, hovers, soulignés `scaleX`).

## 5. Organisation des fichiers CSS (découpage par responsabilité)

| Fichier | Responsabilité |
|---|---|
| `base.css` | Tokens `:root`, reset minimal, `.sec-title`, boutons |
| `layout.css` | Navigation (verre dépoli + état `.is-compact`) et footer |
| `sections.css` | Chaque section de la page, dans l'ordre du HTML (intro → CTA), responsive en fin de fichier |
| `motion.css` | États initiaux d'animation + accessibilité reduced-motion |

Breakpoint unique constaté : **860px** (nav burger, grilles en 1 colonne, masquage de l'image flottante et du mot fantôme).

## 6. TODO design system

- **RTL arabe** : aucun support (`dir="rtl"`, logical properties) — requis par `CLAUDE.md`.
- **Tokens espacements/gris intermédiaires** : quelques valeurs en dur subsistent (`#3d3d4e` dans `.hero__sub`, rgba divers) — à tokeniser si le système grandit.
- **Mode sombre** : non prévu (choix assumé, fond blanc = identité).
