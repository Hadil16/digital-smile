<?php
/**
 * app/Views/admin/employees.php
 * -----------------------------------------------------------------
 * Gestion de l'équipe (admin) : formulaire de création d'un employé
 * + liste des employés existants.
 * Variables : $employees, $departments, $error, $old, $flash.
 * PRÉSENTATION UNIQUEMENT — logique, formulaire et route inchangés.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// Coquille admin commune (sidebar + entête).
$adminActive  = 'employes';
$pageTitle    = "Gestion de l'équipe";
$pageSubtitle = 'Créez les comptes employés et consultez votre équipe';
require ROOT_PATH . '/app/Views/partials/admin-sidebar.php';
?>
        <?php if (!empty($flash)): ?>
            <p class="adm-flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <!-- ============ Création d'un employé ============ -->
        <h2 class="adm-section">Ajouter un employé</h2>

        <div class="adm-card">
            <?php if (!empty($error)): ?>
                <p class="adm-error" role="alert"><?= e($error) ?></p>
            <?php endif; ?>

            <form method="post" action="<?= e(BASE_URL) ?>/admin/employes" novalidate>
                <?= csrf_field() ?>
                <div class="adm-grid">
                    <div class="adm-field">
                        <label class="adm-label" for="name">Nom complet</label>
                        <input class="adm-input" type="text" id="name" name="name"
                               autocomplete="name" required value="<?= e($old['name']) ?>">
                    </div>
                    <div class="adm-field">
                        <label class="adm-label" for="email">Email</label>
                        <input class="adm-input" type="email" id="email" name="email"
                               autocomplete="email" required value="<?= e($old['email']) ?>">
                    </div>
                    <div class="adm-field">
                        <label class="adm-label" for="password">Mot de passe (8 caractères min.)</label>
                        <input class="adm-input" type="password" id="password" name="password"
                               autocomplete="new-password" minlength="8" required>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label" for="department_id">Département</label>
                        <select class="adm-input" id="department_id" name="department_id" required>
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
                <button class="adm-btn adm-btn--primary" type="submit">Créer l'employé</button>
            </form>
        </div>

        <!-- ============ Liste des employés ============ -->
        <h2 class="adm-section">Employés</h2>

        <?php if (empty($employees)): ?>
            <p class="adm-empty">Aucun employé pour le moment.</p>
        <?php else: ?>
            <div class="adm-card">
                <div class="adm-table__scroll">
                    <table class="adm-table">
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
                                    <td class="adm-table__num"><?= e($emp['full_name']) ?></td>
                                    <td><?= e($emp['email']) ?></td>
                                    <td><?= e($emp['department_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
