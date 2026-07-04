<?php
/** app/Views/public/home.php — vue de l'accueil : sections centrales uniquement (héros → CTA). */
require __DIR__ . '/../partials/header.php';
?>

<!-- ============ ② HERO — typo géante sur vidéo ============ -->
<header class="hero" id="accueil">
    <div class="hero__media">
        <!-- ⏳ VIDÉO EN ATTENTE : le fichier assets/video/cubes-logo.mp4
             n'a pas encore été fourni. En attendant, on affiche l'image
             hero.jpg directement (zéro requête 404, zéro erreur console).

             Quand la vidéo sera placée dans assets/video/, remplacez
             la balise <img> ci-dessous par ce bloc :

             <video autoplay muted loop playsinline preload="metadata"
                    poster="assets/img/hero.jpg">
                 <source src="assets/video/cubes-logo.mp4" type="video/mp4">
             </video>
        -->
        <img src="assets/img/hero.jpg" alt="">
    </div>

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
            <a href="#contact" class="btn btn--primary">Demander un devis</a>
            <a href="#services" class="btn btn--dark btn--marquee">
                <span class="btn__track">
                    <span>Explorer</span><span>Explorer</span><span>Explorer</span>
                    <span>Explorer</span><span>Explorer</span><span>Explorer</span>
                </span>
            </a>
        </div>
    </div>
</header>

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

<!-- ============ ④ SERVICES — liste + image au survol ⭐ ============ -->
<section class="services" id="services">
    <h2 class="sec-title">Ce que nous faisons</h2>

    <ul class="services__list">
        <li class="service" data-img="assets/img/branding.jpg">
            <span class="service__name">Création graphique</span>
            <span class="service__num">01</span>
        </li>
        <li class="service" data-img="assets/img/print.jpg">
            <span class="service__name">Impression</span>
            <span class="service__num">02</span>
        </li>
        <li class="service" data-img="assets/img/web.jpg">
            <span class="service__name">Web &amp; Digital</span>
            <span class="service__num">03</span>
        </li>
        <li class="service" data-img="assets/img/qrcode.jpg">
            <span class="service__name">QR Codes</span>
            <span class="service__num">04</span>
        </li>
        <li class="service" data-img="assets/img/audiovisuel.jpg">
            <span class="service__name">Audiovisuel</span>
            <span class="service__num">05</span>
        </li>
    </ul>

    <!-- L'image flottante qui suit la souris (remplie en JS) -->
    <div class="services__reveal"><img src="" alt=""></div>
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

<!-- ============ ⑥ CHIFFRES réels ============ -->
<section class="stats" id="chiffres">
    <div class="stats__grid">
        <div class="stat will-reveal">
            <p class="stat__value" data-count="30" data-suffix="+">0<span class="accent"></span></p>
            <p class="stat__label">Clients accompagnés — dont Sonatrach, Granitex, Bonapro</p>
        </div>
        <div class="stat will-reveal">
            <p class="stat__value" data-count="12" data-suffix="">0</p>
            <p class="stat__label">Expertises métiers, de la création à la livraison</p>
        </div>
        <div class="stat will-reveal">
            <p class="stat__value" data-count="2022" data-suffix="">0</p>
            <p class="stat__label">Année de naissance de Digital Smile à Alger</p>
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
