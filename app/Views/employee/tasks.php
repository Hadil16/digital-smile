<?php
/**
 * app/Views/employee/tasks.php
 * -----------------------------------------------------------------
 * Espace employé — coquille + entête de profil + cartes KPI + liste
 * des tâches (progression + dépôt du livrable).
 * Variables : $projects, $flash, $profile, $stats.
 * Les formulaires (progression / livrer) restent STRICTEMENT inchangés.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// Libellés FR + tonalité de la pastille, par statut de commande.
$statusMeta = [
    'pending'     => ['En attente', 'violet'],
    'approved'    => ['Acceptée',   'violet'],
    'in_progress' => ['En cours',   'amber'],
    'delivered'   => ['Livrée',     'green'],
    'completed'   => ['Terminée',   'green'],
    'rejected'    => ['Refusée',    'red'],
    'cancelled'   => ['Annulée',    'muted'],
];
$fmtDate = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';

// Données de profil (dégradation propre si fiche/colonnes absentes).
$photo       = $profile['photo'] ?? null;
$dept        = $profile['department_name'] ?? '';
$expYears    = $profile['experience_years'] ?? null;
$empFullName = $profile['full_name'] ?? ($_SESSION['name'] ?? 'Employé');
$pp = array_values(array_filter(preg_split('/\s+/', trim($empFullName))));
$initials = strtoupper(mb_substr($pp[0] ?? 'E', 0, 1, 'UTF-8')
          . (count($pp) > 1 ? mb_substr((string) end($pp), 0, 1, 'UTF-8') : ''));

// Chips de l'entête (uniquement des chiffres réels).
$delivered = (int) ($stats['delivered'] ?? 0);
$active    = (int) ($stats['active'] ?? 0);
$chips = [];
if ($expYears !== null && $expYears !== '') {
    $chips[] = ((int) $expYears) . ' an' . (((int) $expYears) > 1 ? 's' : '') . ' d\'expérience';
}
$chips[] = $delivered . ' projet' . ($delivered > 1 ? 's' : '') . ' livré' . ($delivered > 1 ? 's' : '');
$chips[] = $active . ' en cours';

// Cartes KPI (réelles) : "en retard" seulement si une échéance existe.
$empKpis = [
    ['Tâches actives',  $active,                                   '🚧', 'amber'],
    ['Livrées ce mois', (int) ($stats['delivered_this_month'] ?? 0),'🏁', 'green'],
];
if (!empty($stats['has_deadline'])) {
    $overdue = (int) ($stats['overdue'] ?? 0);
    $empKpis[] = ['En retard', $overdue, '⏰', ($overdue > 0 ? 'red' : 'violet')];
}

// Coquille employé commune (sidebar + entête).
$employeeActive = 'taches';
$pageTitle      = 'Mes tâches';
$pageSubtitle   = 'Suivez vos projets et déposez vos livrables';
require ROOT_PATH . '/app/Views/partials/employee-sidebar.php';
?>
        <?php if (!empty($flash)): ?>
            <p class="adm-flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <!-- Entête de profil -->
        <section class="emp-hero">
            <span class="emp-hero__ava" aria-hidden="true">
                <?php if (!empty($photo)): ?>
                    <img src="<?= e(BASE_URL) ?>/<?= e($photo) ?>" alt="">
                <?php else: ?>
                    <?= e($initials) ?>
                <?php endif; ?>
            </span>
            <div class="emp-hero__id">
                <h2 class="emp-hero__name"><?= e($empFullName) ?></h2>
                <?php if ($dept !== ''): ?>
                    <p class="emp-hero__dept"><?= e($dept) ?></p>
                <?php endif; ?>
                <div class="emp-chips">
                    <?php foreach ($chips as $chip): ?>
                        <span class="emp-chip"><?= e($chip) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Cartes KPI (données réelles) -->
        <section class="adm__kpis" aria-label="Indicateurs de mon activité">
            <?php foreach ($empKpis as [$lbl, $val, $ico, $tone]): ?>
                <article class="adm-kpi adm-kpi--<?= $tone ?>">
                    <div class="adm-kpi__top">
                        <span class="adm-kpi__label"><?= e($lbl) ?></span>
                        <span class="adm-kpi__ico" aria-hidden="true"><?= $ico ?></span>
                    </div>
                    <p class="adm-kpi__num"><?= (int) $val ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- Liste des tâches -->
        <h2 class="adm-section">Projets assignés</h2>

        <?php if (empty($projects)): ?>
            <p class="adm-empty">Aucune tâche assignée pour le moment.</p>
        <?php else: ?>
            <?php foreach ($projects as $p): ?>
                <?php
                    $prog = (int) $p['progress'];
                    [$lbl, $tone] = $statusMeta[$p['status']] ?? [$p['status'], 'muted'];
                ?>
                <article class="emp-task">
                    <div class="emp-task__head">
                        <span class="emp-task__code"><?= e($p['order_number']) ?></span>
                        <span class="adm-pill adm-pill--<?= $tone ?>"><?= e($lbl) ?></span>
                    </div>

                    <div class="emp-task__meta">
                        <div><span>Projet</span><?= e($p['project_name']) ?></div>
                        <div><span>Service</span><?= e($p['service_name']) ?></div>
                        <div><span>Client</span><?= e($p['client_name']) ?></div>
                        <div><span>Échéance</span><?= e($fmtDate($p['deadline'])) ?></div>
                    </div>

                    <div class="emp-bar" role="progressbar" aria-valuenow="<?= $prog ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="emp-bar__fill" style="width: <?= $prog ?>%;"></div>
                    </div>
                    <p class="emp-bar__label">Progression : <?= $prog ?>%</p>

                    <div class="emp-task__forms">
                        <!-- Mise à jour de la progression -->
                        <form method="post" action="<?= e(BASE_URL) ?>/employe/taches/progression">
                            <?= csrf_field() ?>
                            <input type="hidden" name="project_id" value="<?= (int) $p['id'] ?>">
                            <div class="field">
                                <label for="prog-<?= (int) $p['id'] ?>">Progression (%)</label>
                                <input class="input num" type="number" id="prog-<?= (int) $p['id'] ?>"
                                       name="progress" min="0" max="100" step="5"
                                       value="<?= $prog ?>" required>
                            </div>
                            <button class="btn btn--primary" type="submit">Mettre à jour</button>
                        </form>

                        <!-- Dépôt du livrable -->
                        <form method="post" action="<?= e(BASE_URL) ?>/employe/taches/livrer"
                              enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <input type="hidden" name="project_id" value="<?= (int) $p['id'] ?>">
                            <div class="field">
                                <label for="file-<?= (int) $p['id'] ?>">Fichier final (PDF, JPG, PNG, ZIP — 10 Mo max)</label>
                                <input class="input" type="file" id="file-<?= (int) $p['id'] ?>"
                                       name="file" accept=".pdf,.jpg,.jpeg,.png,.zip" required>
                            </div>
                            <button class="btn btn--deliver" type="submit">Livrer le fichier</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<style>
    /* --- Entête de profil (auto-portée, tokens clair/sombre). --- */
    .emp-hero { display: flex; align-items: center; gap: 20px; background: var(--color-surface);
        border: 1px solid var(--color-border); border-radius: 20px; padding: 22px 24px; margin: 0 0 22px; }
    .emp-hero__ava { display: inline-grid; place-items: center; width: 76px; height: 76px; border-radius: 20px;
        flex: 0 0 auto; overflow: hidden; font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif;
        font-weight: 800; font-size: 26px; color: #1a1730; background: linear-gradient(135deg, #8BC63F, #4A3F9E); }
    .emp-hero__ava img { width: 100%; height: 100%; object-fit: cover; }
    .emp-hero__name { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 22px;
        color: var(--color-text); margin: 0; }
    .emp-hero__dept { font-size: 14px; font-weight: 600; color: var(--color-accent-dark); margin: 3px 0 0; }
    .emp-chips { display: flex; flex-wrap: wrap; gap: 8px; margin: 12px 0 0; }
    .emp-chip { font-size: 12px; font-weight: 600; color: var(--color-muted);
        background: var(--color-surface-alt); border: 1px solid var(--color-border); padding: 5px 12px; border-radius: 999px; }

    /* --- Carte tâche --- */
    .emp-task { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 16px;
        padding: 22px 24px; margin: 0 0 16px; }
    .emp-task__head { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin: 0 0 14px; }
    .emp-task__code { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 700; color: var(--color-primary-light); }
    .emp-task__meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 8px 20px; margin: 0 0 16px; font-size: 14px; }
    .emp-task__meta div { color: var(--color-text); }
    .emp-task__meta span { display: block; color: var(--color-muted); font-size: 12px; }

    /* Barre de progression lime -> violet. */
    .emp-bar { background: var(--color-surface-alt); border: 1px solid var(--color-border);
        border-radius: 999px; height: 12px; overflow: hidden; margin: 0 0 6px; }
    .emp-bar__fill { background: linear-gradient(90deg, #8BC63F, #4A3F9E); height: 100%; border-radius: 999px; transition: width var(--transition); }
    .emp-bar__label { font-size: 12px; color: var(--color-muted); margin: 0 0 18px; }

    /* Zone des formulaires (conservés à l'identique) restylée A2. */
    .emp-task__forms { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;
        border-top: 1px solid var(--color-border); padding-top: 18px; }
    .emp-task__forms form { display: flex; align-items: flex-end; gap: 8px; margin: 0; }
    .field { display: flex; flex-direction: column; gap: 5px; }
    .field label { font-size: 12px; font-weight: 600; color: var(--color-muted); }
    .input { padding: 10px 12px; border: 1px solid var(--color-border); border-radius: 12px; font-size: 14px;
        font-family: inherit; background: var(--color-surface); color: var(--color-text); }
    .input:focus { outline: 2px solid var(--color-primary-light); outline-offset: 1px; border-color: var(--color-primary-light); }
    .num { width: 90px; }
    .btn { border: 0; cursor: pointer; text-decoration: none; font-weight: 600; font-size: 14px;
        padding: 11px 18px; border-radius: 999px; font-family: inherit; transition: background var(--transition), filter var(--transition); }
    .btn--primary { background: linear-gradient(135deg, #4A3F9E, #6b5fd4); color: #fff; }
    .btn--primary:hover { filter: brightness(1.08); }
    .btn--deliver { background: var(--color-accent); color: #1f3d07; }
    .btn--deliver:hover { background: var(--color-accent-dark); color: #fff; }

    @media (max-width: 560px) {
        .emp-hero { flex-direction: column; align-items: flex-start; text-align: left; }
    }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
