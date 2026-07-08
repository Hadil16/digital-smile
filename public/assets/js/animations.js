/* =====================================================================
   animations.js — Toutes les animations GSAP de Digital Smile
   Librairie : GSAP 3 + ScrollTrigger (chargés via CDN dans index.html)
   Chaque bloc est commenté : je dois pouvoir défendre chaque ligne.
   ===================================================================== */

/* Marqueur "JS actif" : permet au CSS de cacher les éléments à animer.
   Sans JS, rien n'est caché → le site reste utilisable (progressive
   enhancement). */
document.documentElement.classList.add('js');

/* On active le plugin ScrollTrigger (animations liées au scroll). */
gsap.registerPlugin(ScrollTrigger);

/* Respect de l'accessibilité : si l'utilisateur préfère moins de
   mouvement, on n'exécute AUCUNE animation JS. Le CSS (motion.css)
   remet déjà tout visible dans ce cas. */
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ================================================================
   ① INTRO FLASH — compteur 0→100 % puis mots qui flashent
   Jouée UNE fois par session (sessionStorage), passable au clic.
   ================================================================ */
const intro = document.querySelector('.intro');

function lancerHero() {
    /* Timeline du héros : chaque élément entre l'un après l'autre
       (effet "stagger"), comme un rideau qui se lève. */
    const tl = gsap.timeline({ defaults: { ease: 'power4.out' } });

    tl.to('.hero__eyebrow', { opacity: 1, y: 0, duration: .6 })
      /* Les lignes du titre glissent de bas en haut, décalées de 0.12s */
      .to('.hero__title .line span', {
          y: 0, duration: 1, stagger: .12
      }, '-=.3')
      .to('.hero__sub',  { opacity: 1, y: 0, duration: .7 }, '-=.5')
      .to('.hero__btns', { opacity: 1, y: 0, duration: .7 }, '-=.5')
      /* La vidéo arrive en fondu avec une très légère rotation 3D */
      .from('.hero__media', {
          opacity: 0, scale: 1.06, duration: 1.4, ease: 'power2.out'
      }, 0);
}

function fermerIntro() {
    if (!intro) return lancerHero();
    /* L'overlay remonte comme un rideau, puis on lance le héros. */
    gsap.to(intro, {
        yPercent: -100, duration: .8, ease: 'power3.inOut',
        onComplete: () => { intro.remove(); lancerHero(); }
    });
}

if (intro && !reduceMotion && !sessionStorage.getItem('introVue')) {
    sessionStorage.setItem('introVue', '1');

    const compteur = intro.querySelector('.intro__counter');
    const mot      = intro.querySelector('.intro__word');
    const mots     = ['Bonjour.', 'Nous sommes', 'Digital <span class="accent">Smile</span>'];

    /* Compteur : un objet {v:0} animé de 0 à 100, affiché à chaque frame */
    const obj = { v: 0 };
    const tl = gsap.timeline();

    tl.to(obj, {
        v: 100, duration: 1.1, ease: 'power2.inOut',
        onUpdate: () => compteur.textContent = Math.round(obj.v) + '%'
    });

    /* Les mots flashent l'un après l'autre (apparition rapide) */
    mots.forEach((m) => {
        tl.set(mot, { innerHTML: m })
          .fromTo(mot, { opacity: 0, scale: .96 },
                       { opacity: 1, scale: 1, duration: .18 })
          .to(mot, { opacity: 1, duration: .32 });   // temps de lecture
    });

    tl.add(fermerIntro, '+=.1');

    /* Clic n'importe où = passer l'intro immédiatement */
    intro.addEventListener('click', () => { tl.kill(); fermerIntro(); });
} else {
    /* Pas d'intro (déjà vue, ou reduced-motion) → héros direct */
    if (intro) intro.remove();
    if (!reduceMotion) lancerHero();
}

if (!reduceMotion) {

    /* ================================================================
       ② HERO — parallax du mot fantôme "SMILE"
       Le mot bouge plus lentement que le scroll → sensation de
       profondeur (comme les sites Apple).
       ================================================================ */
    gsap.to('.hero__ghost', {
        yPercent: -30,
        ease: 'none',
        scrollTrigger: {
            trigger: '.hero',
            start: 'top top',
            end: 'bottom top',
            scrub: true            /* lié au scroll, pas au temps */
        }
    });

    /* ================================================================
       ③ SERVICES — image flottante qui suit la souris ⭐
       Au survol d'une ligne : l'image du service apparaît et suit
       le curseur avec un léger retard élastique (effet "Huge").
       ================================================================ */
    const revealBox = document.querySelector('.services__reveal');
    const revealImg = revealBox ? revealBox.querySelector('img') : null;

    if (revealBox && window.matchMedia('(hover: hover)').matches) {
        /* quickTo = fonction GSAP ultra-performante pour suivre la
           souris sans recréer d'animation à chaque mouvement. */
        const suivreX = gsap.quickTo(revealBox, 'x', { duration: .45, ease: 'power3' });
        const suivreY = gsap.quickTo(revealBox, 'y', { duration: .45, ease: 'power3' });

        window.addEventListener('mousemove', (e) => {
            suivreX(e.clientX + 24);       /* décalé du curseur */
            suivreY(e.clientY - 120);
        });

        document.querySelectorAll('.service').forEach((ligne) => {
            ligne.addEventListener('mouseenter', () => {
                revealImg.src = ligne.dataset.img;          /* image du service */
                gsap.to(revealBox, { opacity: 1, scale: 1, duration: .35, ease: 'power3.out' });
            });
            ligne.addEventListener('mouseleave', () => {
                gsap.to(revealBox, { opacity: 0, scale: .85, duration: .3 });
            });
        });
    }

    /* Entrée des lignes de services au scroll (uniquement si l'ancienne
       liste existe encore — la home premium utilise désormais .reveal). */
    if (document.querySelector('.service')) {
        gsap.from('.service', {
            opacity: 0, y: 40, duration: .8, stagger: .1, ease: 'power3.out',
            scrollTrigger: { trigger: '.services__list', start: 'top 80%' }
        });
    }

    /* ================================================================
       ④ WE BELIEVE — les phrases se révèlent en scrollant
       ================================================================ */
    gsap.utils.toArray('.believe__line').forEach((ligne) => {
        gsap.fromTo(ligne,
            { opacity: 0, y: 50 },
            {
                opacity: 1, y: 0, duration: 1, ease: 'power3.out',
                scrollTrigger: { trigger: ligne, start: 'top 85%' }
            });
    });

    /* ================================================================
       ⑤ CHIFFRES — les nombres comptent de 0 à leur valeur
       ================================================================ */
    gsap.utils.toArray('.stat__value[data-count]').forEach((el) => {
        const cible  = parseInt(el.dataset.count, 10);
        const suffixe = el.dataset.suffix || '';
        const obj = { v: 0 };
        gsap.to(obj, {
            v: cible, duration: 1.6, ease: 'power2.out',
            scrollTrigger: { trigger: el, start: 'top 85%' },
            onUpdate: () => el.firstChild.textContent = Math.round(obj.v) + suffixe
        });
    });

    /* ================================================================
       ⑥ Sections génériques : tout .will-reveal apparaît en douceur
       ================================================================ */
    gsap.utils.toArray('.will-reveal').forEach((el) => {
        /* fromTo : on définit départ ET arrivée → vrai mouvement
           d'entrée (glissement + fondu), pas un simple fondu plat. */
        gsap.fromTo(el,
            { opacity: 0, y: 44 },
            {
                opacity: 1, y: 0, duration: 1, ease: 'power3.out',
                scrollTrigger: { trigger: el, start: 'top 88%' }
            });
    });

    /* Grilles (réalisations, processus) : entrée en cascade —
       chaque carte arrive 0.1s après la précédente. */
    [['.works__grid', '.work'], ['.process__grid', '.step']].forEach(([grille, carte]) => {
        gsap.fromTo(carte,
            { opacity: 0, y: 50 },
            {
                opacity: 1, y: 0, duration: .9, stagger: .12, ease: 'power3.out',
                scrollTrigger: { trigger: grille, start: 'top 82%' }
            });
    });

    /* ================================================================
       ⑦ Boutons "magnétiques" : le bouton suit légèrement la souris
       ================================================================ */
    document.querySelectorAll('.btn').forEach((btn) => {
        const bx = gsap.quickTo(btn, 'x', { duration: .3, ease: 'power3' });
        const by = gsap.quickTo(btn, 'y', { duration: .3, ease: 'power3' });
        btn.addEventListener('mousemove', (e) => {
            const r = btn.getBoundingClientRect();
            /* Distance du curseur au centre × petite force (0.25) */
            bx((e.clientX - r.left - r.width  / 2) * .25);
            by((e.clientY - r.top  - r.height / 2) * .25);
        });
        btn.addEventListener('mouseleave', () => { bx(0); by(0); });
    });
}

/* ================================================================
   ⑧ Navigation compacte au scroll (léger, sans GSAP)
   ================================================================ */
const nav = document.querySelector('.nav');
window.addEventListener('scroll', () => {
    nav.classList.toggle('is-compact', window.scrollY > 60);
}, { passive: true });

/* ================================================================
   ⑨ Menu burger mobile
   ================================================================ */
const burger = document.querySelector('.nav__burger');
const liens  = document.querySelector('.nav__links');
if (burger) burger.addEventListener('click', () => {
    const ouvert = liens.style.display === 'flex';
    Object.assign(liens.style, ouvert ? { display: 'none' } : {
        display: 'flex', flexDirection: 'column', position: 'absolute',
        top: '64px', right: '20px', background: '#fff', padding: '22px',
        borderRadius: '14px', boxShadow: '0 14px 44px rgba(0,0,0,.12)'
    });
});

/* ================================================================
   ⑩ Révélation au scroll + compteurs (vanilla, IntersectionObserver)
   .reveal = sections premium qui apparaissent ; .count = chiffres qui
   comptent (data-to + data-suffix). Respecte prefers-reduced-motion.
   ================================================================ */
(function () {
    const reveals  = document.querySelectorAll('.reveal');
    const counters = document.querySelectorAll('.count');

    /* Affiche directement la valeur finale (mouvement réduit / repli). */
    const remplir = (el) =>
        el.textContent = (parseInt(el.dataset.to, 10) || 0) + (el.dataset.suffix || '');

    /* Comptage animé 0 → valeur cible. */
    function compter(el) {
        const cible = parseInt(el.dataset.to, 10) || 0;
        const suffixe = el.dataset.suffix || '';
        const duree = 1400, debut = performance.now();
        (function frame(t) {
            const p = Math.min((t - debut) / duree, 1);
            el.textContent = Math.round(p * cible) + suffixe;
            if (p < 1) requestAnimationFrame(frame);
        })(debut);
    }

    /* Mouvement réduit ou pas d'IntersectionObserver → tout visible d'emblée. */
    if (reduceMotion || !('IntersectionObserver' in window)) {
        reveals.forEach((el) => el.classList.add('is-visible'));
        counters.forEach(remplir);
        return;
    }

    const io = new IntersectionObserver((entries, obs) => {
        entries.forEach((en) => {
            if (!en.isIntersecting) return;
            en.target.classList.add('is-visible');
            if (en.target.classList.contains('count')) compter(en.target);
            obs.unobserve(en.target);
        });
    }, { threshold: 0.2 });

    reveals.forEach((el) => io.observe(el));
    counters.forEach((el) => io.observe(el));
})();
