<?php /** app/Views/partials/footer.php — bas de page commun premium (4 colonnes, tokens) + scripts. */ ?><footer class="foot">
    <div class="foot__grid">
        <div class="foot__brand">
            <div class="foot__logo">
                <img src="<?= e(BASE_URL) ?>/assets/img/logo.jpg" alt="">
                <span>Digital Smile</span>
            </div>
            <p class="foot__tag">Digital Like Never Before</p>
            <p class="foot__pitch">Agence de branding à Alger. Nous donnons vie à votre identité, du logo au digital.</p>
        </div>
        <div class="foot__col">
            <h4 class="foot__h">Services</h4>
            <ul>
                <li>Branding</li>
                <li>Web &amp; Digital</li>
                <li>Community</li>
                <li>Impression</li>
                <li>Audiovisuel</li>
            </ul>
        </div>
        <div class="foot__col">
            <h4 class="foot__h">Agence</h4>
            <ul>
                <li><a href="#realisations">Réalisations</a></li>
                <li><a href="#temoignages">Témoignages</a></li>
                <li><a href="#faq">FAQ</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </div>
        <div class="foot__col">
            <h4 class="foot__h">Contact</h4>
            <ul>
                <li>Bab Ezzouar, Alger</li>
                <li><a href="mailto:arezki69@gmail.com">arezki69@gmail.com</a></li>
            </ul>
        </div>
    </div>
    <div class="foot__bottom">
        <span>© 2026 Digital Smile — Tous droits réservés</span>
    </div>
</footer>

<!-- ============ SCRIPTS ============ -->
<!-- GSAP + ScrollTrigger via CDN (décision validée par l'architecte) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="<?= e(BASE_URL) ?>/assets/js/animations.js"></script>

<!-- Graphiques Chart.js : chargés UNIQUEMENT si la page fournit des données
     (window.DS_CHARTS), donc jamais sur les pages publiques. Version épinglée. -->
<script>
(function () {
    if (!window.DS_CHARTS) return; // pas de graphiques sur cette page

    var s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
    s.onload = function () { renderCharts(window.DS_CHARTS); };
    document.body.appendChild(s);

    function renderCharts(d) {
        if (typeof Chart === 'undefined') return;
        var violet = '#4A3F9E', lime = '#8BC63F';
        // Palette de marque + quelques tons complémentaires doux (6 statuts).
        var palette = ['#f0a500', '#4A3F9E', '#1e6fd9', '#0d9488', '#8BC63F', '#b3261e'];

        var m = document.getElementById('chartMonthly');
        if (m && d.monthly) new Chart(m, {
            type: 'line',
            data: { labels: d.monthly.labels, datasets: [{
                label: 'Commandes', data: d.monthly.values, borderColor: violet,
                backgroundColor: 'rgba(74,63,158,.12)', fill: true, tension: 0.3,
                pointBackgroundColor: violet, pointRadius: 4
            }] },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        var st = document.getElementById('chartStatus');
        if (st && d.status) new Chart(st, {
            type: 'doughnut',
            data: { labels: d.status.labels, datasets: [{
                data: d.status.values, backgroundColor: palette, borderColor: '#fff', borderWidth: 2
            }] },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 14 } } } }
        });

        var sv = document.getElementById('chartServices');
        if (sv && d.services) new Chart(sv, {
            type: 'bar',
            data: { labels: d.services.labels, datasets: [{
                label: 'Demandes', data: d.services.values, backgroundColor: lime, borderRadius: 6
            }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }
})();
</script>

<!-- Bascule de thème : clic → clair/sombre, mémorisé dans localStorage.
     (La restauration au chargement est faite tôt dans le <head> pour éviter le flash.) -->
<script>
(function () {
    var root = document.documentElement;
    var btn  = document.getElementById('themeToggle');

    function apply(theme) {
        root.setAttribute('data-theme', theme);
        if (btn) {
            var dark = (theme === 'dark');
            // ☀️ si on est en sombre (clic = revenir clair), 🌙 sinon.
            btn.querySelector('span').innerHTML = dark ? '☀️' : '🌙';
            btn.setAttribute('aria-label', dark ? 'Passer en thème clair' : 'Passer en thème sombre');
        }
    }

    // État courant (posé par le script du <head>, "light" par défaut).
    apply(root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light');

    if (btn) btn.addEventListener('click', function () {
        var next = (root.getAttribute('data-theme') === 'dark') ? 'light' : 'dark';
        try { localStorage.setItem('ds-theme', next); } catch (e) {}
        apply(next);
    });
})();
</script>

</body>
</html>
