<?php
/**
 * app/Views/employee/tasks.php
 * -----------------------------------------------------------------
 * Tâches assignées à l'employé : chaque projet en carte, avec barre
 * de progression, formulaire de mise à jour et dépôt du livrable.
 * Variables : $projects, $flash.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// Libellés FR des statuts de commande (le style vient de .badge--<statut>).
$statusLabels = [
    'pending'     => 'En attente',
    'approved'    => 'Acceptée',
    'rejected'    => 'Refusée',
    'in_progress' => 'En cours',
    'delivered'   => 'Livrée',
    'completed'   => 'Terminée',
    'cancelled'   => 'Annulée',
];
$fmtDate = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
?>
<style>
    /* Styles auto-portés des tâches (couleurs de marque). */
    .tsk { --violet: #4A3F9E; --lime: #8BC63F;
        min-height: 70vh; padding: 96px 24px 64px; font-family: 'Inter', system-ui, sans-serif; }
    .intro { display: none !important; } /* pas d'intro flash hors accueil */
    .tsk__wrap { max-width: 880px; margin: 0 auto; }
    .tsk__role { display: inline-block; background: var(--violet); color: #fff; font-size: 13px;
        font-weight: 600; padding: 6px 14px; border-radius: 999px; margin: 0 0 10px; }
    .tsk__title { font-family: 'Poppins', system-ui, sans-serif; font-weight: 800;
        font-size: clamp(24px, 4vw, 34px); color: var(--violet); margin: 0 0 8px; }
    .tsk__back { display: inline-block; color: var(--violet); font-size: 14px; font-weight: 600;
        text-decoration: none; margin: 0 0 24px; }
    .flash { background: #eef7e0; color: #3B6D11; border: 1px solid #cfe6a8;
        border-radius: 12px; padding: 12px 16px; margin: 0 0 22px; font-size: 14px; }
    .empty { background: #faf9ff; border: 1px dashed #d7d2f0; color: #666;
        border-radius: 14px; padding: 40px; text-align: center; font-size: 15px; }

    .task { background: #fff; border: 1px solid #eee; border-radius: 16px; padding: 24px 26px;
        margin: 0 0 18px; box-shadow: 0 12px 40px rgba(74, 63, 158, .06); }
    .task__head { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin: 0 0 14px; }
    .task__code { font-family: 'Poppins', system-ui, sans-serif; font-weight: 700; color: var(--violet); }
    .task__meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 8px 20px; margin: 0 0 16px; font-size: 14px; }
    .task__meta span { display: block; color: #999; font-size: 12px; }

    .bar { background: #eee; border-radius: 999px; height: 12px; overflow: hidden; margin: 0 0 6px; }
    .bar__fill { background: var(--lime); height: 100%; border-radius: 999px; }
    .bar__label { font-size: 12px; color: #666; margin: 0 0 18px; }

    .task__forms { display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end;
        border-top: 1px solid #f0f0f0; padding-top: 18px; }
    .task__forms form { display: flex; align-items: flex-end; gap: 8px; margin: 0; }
    .field { display: flex; flex-direction: column; gap: 5px; }
    .field label { font-size: 12px; font-weight: 600; color: #555; }
    .input { padding: 10px 12px; border: 1px solid #d5d5db; border-radius: 10px; font-size: 14px;
        font-family: inherit; background: #fff; }
    .input:focus { outline: 2px solid var(--violet); outline-offset: 1px; border-color: var(--violet); }
    .num { width: 90px; }
    .btn { border: 0; cursor: pointer; text-decoration: none; font-weight: 600; font-size: 14px;
        padding: 11px 18px; border-radius: 999px; font-family: inherit; transition: background .2s, color .2s; }
    .btn--primary { background: var(--violet); color: #fff; }
    .btn--primary:hover { background: #372f78; }
    .btn--deliver { background: var(--lime); color: #23400a; }
    .btn--deliver:hover { background: #7cb034; }

    .badge { display: inline-block; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 999px; }
    .badge--pending     { background: #fff7e6; color: #b8860b; }
    .badge--approved    { background: #ece9fb; color: #4A3F9E; }
    .badge--in_progress { background: #e6f0fc; color: #1e6fd9; }
    .badge--delivered   { background: #e3f7f4; color: #0d9488; }
    .badge--completed   { background: #eef7e0; color: #3B6D11; }
    .badge--rejected    { background: #fdecec; color: #b3261e; }
    .badge--cancelled   { background: #eee; color: #666; }
</style>

<main class="tsk">
    <div class="tsk__wrap">
        <p class="tsk__role">Espace employé</p>
        <h1 class="tsk__title">Mes tâches</h1>
        <a class="tsk__back" href="<?= e(BASE_URL) ?>/employe">← Retour au tableau de bord</a>

        <?php if (!empty($flash)): ?>
            <p class="flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <?php if (empty($projects)): ?>
            <p class="empty">Aucune tâche assignée.</p>
        <?php else: ?>
            <?php foreach ($projects as $p): ?>
                <?php $prog = (int) $p['progress']; ?>
                <article class="task">
                    <div class="task__head">
                        <span class="task__code"><?= e($p['order_number']) ?></span>
                        <span class="badge badge--<?= e($p['status']) ?>">
                            <?= e($statusLabels[$p['status']] ?? $p['status']) ?>
                        </span>
                    </div>

                    <div class="task__meta">
                        <div><span>Projet</span><?= e($p['project_name']) ?></div>
                        <div><span>Service</span><?= e($p['service_name']) ?></div>
                        <div><span>Client</span><?= e($p['client_name']) ?></div>
                        <div><span>Échéance</span><?= e($fmtDate($p['deadline'])) ?></div>
                    </div>

                    <div class="bar" role="progressbar" aria-valuenow="<?= $prog ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="bar__fill" style="width: <?= $prog ?>%;"></div>
                    </div>
                    <p class="bar__label">Progression : <?= $prog ?>%</p>

                    <div class="task__forms">
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
    </div>
</main>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
