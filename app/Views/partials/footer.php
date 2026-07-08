<?php /** app/Views/partials/footer.php — bas de page commun : footer + scripts (GSAP CDN, animations). */ ?><footer class="footer">
    <div class="footer__grid">
        <div>
            <p><strong>Digital Smile</strong></p>
            <p>Agence de branding &amp; communication</p>
            <p>Digital like never before.</p>
        </div>
        <div>
            <p><strong>Contact</strong></p>
            <p><a href="tel:+213549562205">+213 (0) 549 56 22 05</a></p>
            <p><a href="mailto:arezki69@gmail.com">arezki69@gmail.com</a></p>
        </div>
        <div>
            <p><strong>Adresse</strong></p>
            <p>Cité 1200 Logts, Bt S, Local N° 17</p>
            <p>Bab Ezzouar, Alger</p>
        </div>
    </div>
    <div class="footer__bottom">
        <span>© 2026 Digital Smile. Tous droits réservés.</span>
        <span>RC 5146243 A 22</span>
    </div>
</footer>

<!-- ============ SCRIPTS ============ -->
<!-- GSAP + ScrollTrigger via CDN (décision validée par l'architecte) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="assets/js/animations.js"></script>

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

</body>
</html>
