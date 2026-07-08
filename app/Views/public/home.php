<?php
/** app/Views/public/home.php — vue de l'accueil : sections centrales uniquement (héros → CTA). */
require __DIR__ . '/../partials/header.php';
?>

<!-- ============ ② HERO — carte premium sombre (SMILE + blobs + halo souris) ====== -->
<header class="hero" id="accueil">
    <div class="hero__media"><!-- conservé pour l'animation GSAP (masqué en CSS) --></div>
    <span class="hero__blob hero__blob--a" aria-hidden="true"></span>
    <span class="hero__blob hero__blob--b" aria-hidden="true"></span>
    <p class="hero__ghost" aria-hidden="true">SMILE</p>

    <div class="hero__content">
        <span class="hero__eyebrow will-reveal">Digital like never before</span>
        <h1 class="hero__title">
            <span class="line"><span>Votre marque,</span></span>
            <span class="line"><span class="accent">réinventée.</span></span>
        </h1>
        <p class="hero__sub will-reveal">
            Agence de branding à Alger. Du logo au digital, nous donnons vie
            à votre identité avec créativité et précision.
        </p>
        <div class="hero__btns will-reveal">
            <a href="<?= e(BASE_URL) ?>/register" class="hero__cta hero__cta--primary">Demander un devis</a>
            <a href="#services" class="hero__cta hero__cta--ghost">Explorer nos services →</a>
        </div>
    </div>
</header>

<!-- Effet souris du héros : met à jour --mx/--my (halo qui suit le curseur).
     Désactivé si l'utilisateur préfère moins d'animations. Vanilla JS, léger. -->
<script>
(function () {
    var hero = document.getElementById('accueil');
    if (!hero) return;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) return;
    hero.addEventListener('mousemove', function (e) {
        var r = hero.getBoundingClientRect();
        hero.style.setProperty('--mx', ((e.clientX - r.left) / r.width  * 100) + '%');
        hero.style.setProperty('--my', ((e.clientY - r.top)  / r.height * 100) + '%');
    });
})();
</script>

<!-- ============ ②b TRUST BAR — clients réels (factures 14-16) ===== -->
<section class="trust">
    <p class="trust__label">Ils nous font confiance</p>
    <ul class="trust__list">
        <li>SONATRACH</li>
        <li>GRANITEX</li>
        <li>BONAPRO</li>
        <li>EPTP ALGER</li>
        <li>ARIZONA</li>
        <li>RAHA DZ</li>
    </ul>
</section>

<!-- ============ ③ MARQUEE défilant ============ -->
<div class="marquee" aria-hidden="true">
    <div class="marquee__track">
        <!-- Contenu dupliqué : nécessaire pour une boucle sans couture -->
        <span>BRANDING</span><span class="dot">•</span>
        <span>IMPRESSION</span><span class="dot">•</span>
        <span>WEB</span><span class="dot">•</span>
        <span>QR CODES</span><span class="dot">•</span>
        <span>AUDIOVISUEL</span><span class="dot">•</span>
        <span>BRANDING</span><span class="dot">•</span>
        <span>IMPRESSION</span><span class="dot">•</span>
        <span>WEB</span><span class="dot">•</span>
        <span>QR CODES</span><span class="dot">•</span>
        <span>AUDIOVISUEL</span><span class="dot">•</span>
    </div>
</div>

<!-- ============ ④ SERVICES — grille de cartes premium ============ -->
<section class="services" id="services">
    <div class="services__head">
        <h2 class="services__title reveal">Ce que nous faisons</h2>
        <p class="services__intro reveal">Trois expertises pour donner vie à votre marque.</p>
    </div>
    <div class="services__grid">
        <article class="svc reveal">
            <div class="svc__icon" aria-hidden="true">🎨</div>
            <h3 class="svc__title">Création graphique</h3>
            <p class="svc__desc">Logos, chartes et identités visuelles qui marquent les esprits.</p>
        </article>
        <article class="svc reveal">
            <div class="svc__icon" aria-hidden="true">🌐</div>
            <h3 class="svc__title">Web &amp; Digital</h3>
            <p class="svc__desc">Sites vitrines, e-commerce et présence en ligne sur mesure.</p>
        </article>
        <article class="svc reveal">
            <div class="svc__icon" aria-hidden="true">🎬</div>
            <h3 class="svc__title">Audiovisuel</h3>
            <p class="svc__desc">Shooting photo, vidéo et drone pour valoriser vos projets.</p>
        </article>
    </div>
</section>

<!-- ============ ④b RÉALISATIONS — projets RÉELS (factures 14-16) ⭐
     NOTE : les visuels sont des illustrations à remplacer par les
     photos réelles de chaque projet (dossiers Drive : Affiches,
     Packaging, Habillage, Logos, Qr Codes...). Les projets et
     clients, eux, sont authentiques. ============ -->
<section class="works" id="realisations">
    <h2 class="sec-title">Réalisations</h2>

    <div class="works__grid">
        <article class="work will-reveal">
            <div class="work__media"><img src="assets/img/branding.jpg" alt="Charte graphique Bonapro"></div>
            <p class="work__cat">Identité visuelle</p>
            <h3 class="work__title">Charte graphique — Eurl Bonapro</h3>
        </article>
        <article class="work will-reveal">
            <div class="work__media"><img src="assets/img/print.jpg" alt="Impression grand format Sonatrach"></div>
            <p class="work__cat">Impression grand format</p>
            <h3 class="work__title">Bâches &amp; signalétique — Sonatrach</h3>
        </article>
        <article class="work will-reveal">
            <div class="work__media"><img src="assets/img/qrcode.jpg" alt="QR Code dynamique Granitex"></div>
            <p class="work__cat">QR Codes</p>
            <h3 class="work__title">QR Code dynamique — Coop Granitex</h3>
        </article>
        <article class="work will-reveal">
            <div class="work__media"><img src="assets/img/web.jpg" alt="Site web twinshamis.com"></div>
            <p class="work__cat">Web</p>
            <h3 class="work__title">Site vitrine — twinshamis.com</h3>
        </article>
        <article class="work will-reveal">
            <div class="work__media"><img src="assets/img/charte.jpg" alt="Réseaux sociaux Bonapro"></div>
            <p class="work__cat">Digital marketing</p>
            <h3 class="work__title">Gestion réseaux sociaux — Bonapro</h3>
        </article>
        <article class="work will-reveal">
            <div class="work__media"><img src="assets/img/audiovisuel.jpg" alt="Shooting photo et drone"></div>
            <p class="work__cat">Audiovisuel</p>
            <h3 class="work__title">Shooting photos &amp; drone — Bonapro</h3>
        </article>
    </div>
</section>

<!-- ============ ④c PROCESSUS — 4 étapes (miroir du futur portail
     client de la plateforme : Brief → Création → Validation →
     Livraison) ============ -->
<section class="process" id="processus">
    <h2 class="sec-title">Comment nous travaillons</h2>

    <div class="process__grid">
        <div class="step will-reveal">
            <p class="step__num">01</p>
            <h3 class="step__title">Brief &amp; devis</h3>
            <p class="step__desc">Vous nous expliquez votre besoin, nous cadrons le projet et le budget — sous 48h.</p>
        </div>
        <div class="step will-reveal">
            <p class="step__num">02</p>
            <h3 class="step__title">Création</h3>
            <p class="step__desc">Nos designers conçoivent, vous suivez l'avancement en toute transparence.</p>
        </div>
        <div class="step will-reveal">
            <p class="step__num">03</p>
            <h3 class="step__title">Validation</h3>
            <p class="step__desc">Vous validez ou demandez des ajustements — rien ne part sans votre accord.</p>
        </div>
        <div class="step will-reveal">
            <p class="step__num">04</p>
            <h3 class="step__title">Livraison &amp; suivi</h3>
            <p class="step__desc">Fichiers finaux, impression, mise en ligne — et un suivi qui ne s'arrête pas là.</p>
        </div>
    </div>
</section>

<!-- ============ ⑤ WE BELIEVE — storytelling ============ -->
<section class="believe" id="croyances">
    <h2 class="sec-title">Nous croyons</h2>

    <p class="believe__line">
        Qu'une marque forte ne <span class="muted">se décrit pas</span> —
        elle <span class="accent">se ressent.</span>
    </p>
    <p class="believe__line">
        Que chaque détail compte : <span class="accent">du pixel</span>
        à la <span class="accent">bâche grand format.</span>
    </p>
    <p class="believe__line">
        Que le digital algérien mérite un standard
        <span class="accent">international.</span>
    </p>
</section>

<!-- ============ ⑥ CHIFFRES — panneau dégradé + compteurs ============ -->
<section class="stats" id="chiffres">
    <div class="stats__panel reveal">
        <div>
            <p class="stat__value stat__value--lime"><span class="count" data-to="30" data-suffix="+">0+</span></p>
            <p class="stat__label">clients satisfaits</p>
        </div>
        <div>
            <p class="stat__value">2022</p>
            <p class="stat__label">fondée à Alger</p>
        </div>
        <div>
            <p class="stat__value stat__value--lime"><span class="count" data-to="100" data-suffix="%">0%</span></p>
            <p class="stat__label">sur mesure</p>
        </div>
    </div>
</section>

<!-- ============ ⑥b TÉMOIGNAGE
     ⚠️ EXEMPLE À REMPLACER : citation fictive en attendant un vrai
     retour client (WhatsApp / email). Ne jamais publier sans
     l'accord écrit du client cité. ============ -->
<section class="quote">
    <blockquote class="quote__text will-reveal">
        « Une équipe réactive qui a su traduire notre image en une
        identité <span class="accent">professionnelle et cohérente</span>,
        du print au digital. »
    </blockquote>
    <p class="quote__author will-reveal">— Direction marketing, client accompagné depuis 2023 <em>(exemple)</em></p>
</section>

<!-- ============ ⑦ CTA géant ============ -->
<section class="cta" id="contact">
    <h2 class="cta__title will-reveal">Discutons<span class="accent">.</span></h2>
    <p class="will-reveal">Un projet, une idée, une marque à réinventer ?</p>
    <a href="mailto:arezki69@gmail.com" class="btn btn--primary will-reveal">arezki69@gmail.com</a>
</section>

<!-- ============ FOOTER ============ -->
<?php require __DIR__ . '/../partials/footer.php'; ?>
