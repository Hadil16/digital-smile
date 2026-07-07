<?php
/**
 * app/Views/admin/employees.php
 * -----------------------------------------------------------------
 * Gestion de l'équipe (admin) : formulaire de création d'un employé
 * + liste des employés existants.
 * Variables : $employees, $departments, $error, $old, $flash.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';
?>
<style>
    /* Styles auto-portés de la gestion d'équipe (couleurs de marque). */
    .adm { --violet: #4A3F9E; --lime: #8BC63F;
        min-height: 70vh; padding: 96px 24px 64px; font-family: 'Inter', system-ui, sans-serif; }
    .intro { display: none !important; } /* pas d'intro flash hors accueil */
    .adm__wrap { max-width: 860px; margin: 0 auto; }
    .adm__role { display: inline-block; background: var(--violet); color: #fff; font-size: 13px;
        font-weight: 600; padding: 6px 14px; border-radius: 999px; margin: 0 0 10px; }
    .adm__title { font-family: 'Poppins', system-ui, sans-serif; font-weight: 800;
        font-size: clamp(24px, 4vw, 34px); color: var(--violet); margin: 0 0 8px; }
    .adm__back { display: inline-block; color: var(--violet); font-size: 14px; font-weight: 600;
        text-decoration: none; margin: 0 0 24px; }
    .adm__h2 { font-family: 'Poppins', system-ui, sans-serif; font-size: 20px; color: #222;
        margin: 34px 0 16px; }
    .flash { background: #eef7e0; color: #3B6D11; border: 1px solid #cfe6a8;
        border-radius: 12px; padding: 12px 16px; margin: 0 0 22px; font-size: 14px; }
    .error { background: #fdecec; color: #b3261e; border-radius: 10px;
        padding: 12px 14px; margin: 0 0 18px; font-size: 14px; }
    .empty { background: #faf9ff; border: 1px dashed #d7d2f0; color: #666;
        border-radius: 14px; padding: 36px; text-align: center; font-size: 15px; }

    /* Carte formulaire */
    .card { background: #fff; border: 1px solid #eee; border-radius: 16px; padding: 28px 28px 8px;
        box-shadow: 0 12px 40px rgba(74, 63, 158, .06); }
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0 20px; }
    .field { margin: 0 0 18px; }
    .label { display: block; font-weight: 600; font-size: 14px; color: #333; margin: 0 0 6px; }
    .input { width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #d5d5db;
        border-radius: 10px; font-size: 15px; font-family: inherit; background: #fff; }
    .input:focus { outline: 2px solid var(--violet); outline-offset: 1px; border-color: var(--violet); }
    .btn { border: 0; cursor: pointer; background: var(--violet); color: #fff; font-weight: 600;
        font-size: 15px; padding: 13px 30px; border-radius: 999px; font-family: inherit;
        transition: background .2s; margin: 0 0 20px; }
    .btn:hover { background: var(--lime); }

    /* Tableau des employés */
    .table__scroll { overflow-x: auto; border: 1px solid #eee; border-radius: 14px; }
    .table { width: 100%; border-collapse: collapse; font-size: 14px; min-width: 520px; }
    .table th, .table td { text-align: left; padding: 13px 16px; border-bottom: 1px solid #f0f0f0; }
    .table th { background: #faf9ff; color: #555; font-weight: 600; white-space: nowrap; }
    .table tr:last-child td { border-bottom: 0; }
    .table td:first-child { font-weight: 600; color: #222; }
</style>

<main class="adm">
    <div class="adm__wrap">
        <p class="adm__role">Espace administrateur</p>
        <h1 class="adm__title">Gestion de l'équipe</h1>
        <a class="adm__back" href="<?= e(BASE_URL) ?>/admin">← Retour au tableau de bord</a>

        <?php if (!empty($flash)): ?>
            <p class="flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <!-- ============ Création d'un employé ============ -->
        <h2 class="adm__h2">Ajouter un employé</h2>

        <div class="card">
            <?php if (!empty($error)): ?>
                <p class="error" role="alert"><?= e($error) ?></p>
            <?php endif; ?>

            <form method="post" action="<?= e(BASE_URL) ?>/admin/employes" novalidate>
                <?= csrf_field() ?>
                <div class="grid">
                    <div class="field">
                        <label class="label" for="name">Nom complet</label>
                        <input class="input" type="text" id="name" name="name"
                               autocomplete="name" required value="<?= e($old['name']) ?>">
                    </div>
                    <div class="field">
                        <label class="label" for="email">Email</label>
                        <input class="input" type="email" id="email" name="email"
                               autocomplete="email" required value="<?= e($old['email']) ?>">
                    </div>
                    <div class="field">
                        <label class="label" for="password">Mot de passe (8 caractères min.)</label>
                        <input class="input" type="password" id="password" name="password"
                               autocomplete="new-password" minlength="8" required>
                    </div>
                    <div class="field">
                        <label class="label" for="department_id">Département</label>
                        <select class="input" id="department_id" name="department_id" required>
                            <option value="">— Choisir —</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= (int) $d['id'] ?>"
                                    <?= ((string) $d['id'] === $old['department_id']) ? 'selected' : '' ?>>
                                    <?= e($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button class="btn" type="submit">Créer l'employé</button>
            </form>
        </div>

        <!-- ============ Liste des employés ============ -->
        <h2 class="adm__h2">Employés</h2>

        <?php if (empty($employees)): ?>
            <p class="empty">Aucun employé pour le moment.</p>
        <?php else: ?>
            <div class="table__scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Département</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td><?= e($emp['full_name']) ?></td>
                                <td><?= e($emp['email']) ?></td>
                                <td><?= e($emp['department_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
