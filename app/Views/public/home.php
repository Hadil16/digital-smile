<?php
/** app/Views/public/home.php — accueil premium : héros → services → chiffres → réalisations → process → croyance → contact. */
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

<!-- Effet souris du héros : met à jour --mx/--my (halo qui suit le curseur). Vanilla JS. -->
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

<!-- ============ ④b ILS NOUS FONT CONFIANCE — barre de clients (réf. A2) ===== -->
<section class="trustbar">
    <p class="trustbar__label reveal">Ils nous font confiance</p>
    <ul class="trustbar__list reveal" style="transition-delay: .08s">
        <li>Sonatrach</li>
        <li>Bonapro</li>
        <li>Twinshamis</li>
        <li>Granitex</li>
        <li>Djezzy</li>
    </ul>
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

<!-- ============ ⑦ MARQUEE — bandeau défilant plein écran ============ -->
<div class="strip" aria-hidden="true">
    <div class="strip__track">
        <!-- contenu dupliqué (×2) pour une boucle sans couture -->
        <span>BRANDING</span><span class="strip__dot">•</span>
        <span>WEB</span><span class="strip__dot">•</span>
        <span>QR CODES</span><span class="strip__dot">•</span>
        <span>AUDIOVISUEL</span><span class="strip__dot">•</span>
        <span>IMPRESSION</span><span class="strip__dot">•</span>
        <span>BRANDING</span><span class="strip__dot">•</span>
        <span>WEB</span><span class="strip__dot">•</span>
        <span>QR CODES</span><span class="strip__dot">•</span>
        <span>AUDIOVISUEL</span><span class="strip__dot">•</span>
        <span>IMPRESSION</span><span class="strip__dot">•</span>
    </div>
</div>

<!-- ============ ⑧ RÉALISATIONS — portfolio (cartes image + tilt + zoom) ============ -->
<section class="portfolio" id="realisations">
    <div class="portfolio__head">
        <h2 class="portfolio__title reveal">Nos réalisations</h2>
        <a href="#contact" class="portfolio__all reveal">Tout voir →</a>
    </div>
    <div class="portfolio__grid">
        <?php
        // Projets/clients authentiques ; visuels illustratifs (à remplacer).
        $works = [
            ['Identité visuelle',       'Charte graphique — Eurl Bonapro'],
            ['Impression grand format', 'Bâches &amp; signalétique — Sonatrach'],
            ['QR Codes',                'QR Code dynamique — Café Central'],
            ['Web',                     'Site vitrine — twinshamis.com'],
            ['Digital marketing',       'Gestion réseaux sociaux — Bonapro'],
            ['Audiovisuel',             'Shooting photos &amp; drone'],
        ];
        foreach ($works as $i => [$cat, $title]):
            $n = $i + 1;
            // Reveal décalé sur l'enveloppe ; le tilt (sur la carte) reste réactif.
            $delay = number_format($i * 0.06, 2, '.', '');
        ?>
            <article class="pf-item reveal" style="transition-delay: <?= $delay ?>s">
                <div class="pf" data-tilt>
                    <div class="pf__media">
                        <!-- onerror : image manquante → masquée, le dégradé de marque reste. -->
                        <img src="assets/images/portfolio/portfolio-<?= $n ?>.jpg" alt="<?= strip_tags($title) ?>"
                             loading="lazy" onerror="this.style.display='none'">
                    </div>
                    <div class="pf__body">
                        <p class="pf__cat"><?= $cat ?></p>
                        <h3 class="pf__title"><?= $title ?></h3>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============ ⑨ PROCESS — panneau dégradé (3 étapes) ============ -->
<section class="how" id="processus">
    <div class="how__panel reveal">
        <p class="how__label">Comment ça marche —</p>
        <div class="how__grid">
            <div class="how__step">
                <p class="how__num">01</p>
                <h3 class="how__title">Brief &amp; devis</h3>
                <p class="how__desc">Vous nous expliquez votre besoin, nous cadrons le projet et le budget — sous 48h.</p>
            </div>
            <div class="how__step">
                <p class="how__num">02</p>
                <h3 class="how__title">Création</h3>
                <p class="how__desc">Nos designers conçoivent, vous suivez l'avancement en toute transparence.</p>
            </div>
            <div class="how__step">
                <p class="how__num">03</p>
                <h3 class="how__title">Validation</h3>
                <p class="how__desc">Vous validez ou demandez des ajustements — rien ne part sans votre accord.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============ ⑩ CROYANCE — déclaration centrée ============ -->
<section class="belief" id="croyances">
    <p class="belief__label reveal">Nous croyons —</p>
    <h2 class="belief__line reveal">
        Qu'une marque forte ne se voit pas — elle <span class="belief__accent">se ressent.</span>
    </h2>
</section>

<!-- ============ ⑩b TÉMOIGNAGES — cartes citations (réf. A2) ============ -->
<section class="testi" id="temoignages">
    <div class="testi__head">
        <p class="testi__label reveal">Ils en parlent —</p>
        <h2 class="testi__title reveal">Témoignages</h2>
    </div>
    <div class="testi__grid">
        <?php
        $quotes = [
            ['Digital Smile a transformé notre image de marque. Le résultat a dépassé nos attentes.', 'Direction Communication', 'Eurl Bonapro'],
            ['Réactifs, créatifs et professionnels. Un vrai partenaire pour nos projets.',              'Service Marketing',       'Sonatrach'],
            ['Un accompagnement du début à la fin, avec une écoute rare. Je recommande.',               'Gérant',                  'Café Central'],
        ];
        foreach ($quotes as $i => [$text, $author, $role]):
            // Reveal décalé porté par l'enveloppe ; la carte garde son lift au survol.
            $delay = number_format($i * 0.08, 2, '.', '');
        ?>
            <div class="testi__item reveal" style="transition-delay: <?= $delay ?>s">
                <article class="testi__card">
                    <span class="testi__mark" aria-hidden="true">&ldquo;</span>
                    <p class="testi__text"><?= e($text) ?></p>
                    <p class="testi__author"><?= e($author) ?></p>
                    <p class="testi__role"><?= e($role) ?></p>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============ ⑩c FAQ — accordéon (réf. A2) ============ -->
<section class="faq" id="faq">
    <div class="faq__head">
        <p class="faq__label reveal">FAQ —</p>
        <h2 class="faq__title reveal">Questions fréquentes</h2>
    </div>
    <div class="faq__list">
        <?php
        $faqs = [
            ['Combien coûte un projet de branding ?', 'Chaque projet est sur-mesure. Après un premier échange, nous vous envoyons un devis détaillé sous 48h.'],
            ['Quels sont vos délais ?', 'Un logo prend 5 à 10 jours, une identité complète 2 à 4 semaines, selon vos retours.'],
            ['Travaillez-vous hors d\'Alger ?', 'Oui, nous accompagnons des clients dans toute l\'Algérie, à distance ou sur site.'],
            ['Puis-je suivre l\'avancement de mon projet ?', 'Bien sûr. Via votre espace client, vous suivez chaque étape et validez les livrables en ligne.'],
            ['Proposez-vous l\'impression ?', 'Oui : cartes, flyers, bâches et signalétique grand format.'],
        ];
        foreach ($faqs as $i => [$q, $a]):
            $qid = 'faq-q-' . $i; $aid = 'faq-a-' . $i;
            $delay = number_format($i * 0.05, 2, '.', '');
        ?>
            <div class="faq__item reveal" style="transition-delay: <?= $delay ?>s">
                <button type="button" class="faq__q" id="<?= $qid ?>"
                        aria-expanded="false" aria-controls="<?= $aid ?>">
                    <span><?= e($q) ?></span>
                    <span class="faq__icon" aria-hidden="true">+</span>
                </button>
                <div class="faq__a" id="<?= $aid ?>" role="region" aria-labelledby="<?= $qid ?>">
                    <p><?= e($a) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Accordéon FAQ : ouverture/fermeture indépendante, accessible. Vanilla JS. -->
<script>
(function () {
    document.querySelectorAll('.faq__q').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var item = btn.closest('.faq__item');
            var open = item.classList.toggle('is-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });
})();
</script>

<!-- ============ ⑪ CONTACT — Discutons + carte de contact ============ -->
<section class="contact" id="contact">
    <div class="contact__grid">
        <div class="contact__left reveal">
            <p class="contact__status"><span class="contact__dot" aria-hidden="true"></span> Disponibles pour vos projets</p>
            <h2 class="contact__title">Discutons<span class="contact__accent">.</span></h2>
            <p class="contact__sub">Un projet, une idée, une marque à réinventer ? Écrivez-nous — on vous répond sous 48h.</p>
        </div>
        <div class="contact__card reveal" data-tilt>
            <h3 class="contact__card-title">Démarrons la conversation</h3>
            <a href="mailto:arezki69@gmail.com" class="contact__mail">arezki69@gmail.com</a>
            <a href="<?= e(BASE_URL) ?>/register" class="hero__cta hero__cta--primary contact__btn">Demander un devis</a>
        </div>
    </div>
</section>

<!-- ============ FOOTER ============ -->
<?php require __DIR__ . '/../partials/footer.php'; ?>
