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

// Cartes KPI (comptes réels : effectif + charge de travail).
$empKpis = [
    ['Employés',         count($employees),                                                          '🧑‍💼', 'violet', 'Équipe active'],
    ['Projets en cours', (int) ($statusCounts['in_progress'] ?? 0),                                  '🚧', 'amber',  'Commandes en cours'],
    ['Projets livrés',   (int) ($statusCounts['delivered'] ?? 0) + (int) ($statusCounts['completed'] ?? 0), '🏁', 'green',  'Livrés et terminés'],
];

// Initiales à partir d'un nom (avatar des cartes employé).
$initialsOf = function (string $name): string {
    $parts = array_values(array_filter(preg_split('/\s+/', trim($name))));
    return strtoupper(mb_substr($parts[0] ?? '?', 0, 1, 'UTF-8')
        . (count($parts) > 1 ? mb_substr((string) end($parts), 0, 1, 'UTF-8') : ''));
};
?>
        <?php if (!empty($flash)): ?>
            <p class="adm-flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <!-- Cartes KPI (données réelles) -->
        <section class="adm__kpis" aria-label="Indicateurs équipe">
            <?php foreach ($empKpis as [$lbl, $val, $ico, $tone, $cap]): ?>
                <article class="adm-kpi adm-kpi--<?= $tone ?>">
                    <div class="adm-kpi__top">
                        <span class="adm-kpi__label"><?= e($lbl) ?></span>
                        <span class="adm-kpi__ico" aria-hidden="true"><?= $ico ?></span>
                    </div>
                    <p class="adm-kpi__num"><?= (int) $val ?></p>
                    <p class="adm-kpi__cap"><?= e($cap) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

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

        <!-- ============ Liste des employés (cartes) ============ -->
        <h2 class="adm-section">Employés</h2>

        <?php if (empty($employees)): ?>
            <p class="adm-empty">Aucun employé pour le moment.</p>
        <?php else: ?>
            <div class="adm-team">
                <?php foreach ($employees as $emp): ?>
                    <article class="adm-emp">
                        <span class="adm-emp__ava" aria-hidden="true"><?= e($initialsOf($emp['full_name'])) ?></span>
                        <div class="adm-emp__id">
                            <p class="adm-emp__name"><?= e($emp['full_name']) ?></p>
                            <p class="adm-emp__dept"><?= e($emp['department_name']) ?></p>
                            <p class="adm-emp__mail"><?= e($emp['email']) ?></p>
                        </div>
                        <span class="adm-emp__proj" title="Projets en cours">
                            <strong><?= (int) ($emp['active_projects'] ?? 0) ?></strong> en cours
                        </span>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<style>
    /* Grille de cartes employé (auto-portée, tokens clair/sombre). */
    .adm-team { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; }
    .adm-emp { display: flex; align-items: center; gap: 14px; background: var(--color-surface);
        border: 1px solid var(--color-border); border-radius: 16px; padding: 16px 18px;
        transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition); }
    .adm-emp:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); border-color: rgba(139, 198, 63, .5); }
    .adm-emp__ava { display: inline-grid; place-items: center; width: 46px; height: 46px; border-radius: 999px;
        flex: 0 0 auto; font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 15px;
        color: #1a1730; background: linear-gradient(135deg, #8BC63F, #6BA02C); }
    .adm-emp__id { flex: 1; min-width: 0; line-height: 1.35; }
    .adm-emp__name { margin: 0; font-weight: 600; color: var(--color-text); }
    .adm-emp__dept { margin: 2px 0 0; font-size: 13px; color: var(--color-primary-light); font-weight: 600; }
    .adm-emp__mail { margin: 2px 0 0; font-size: 12px; color: var(--color-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .adm-emp__proj { flex: 0 0 auto; font-size: 12px; color: var(--color-muted); text-align: center; }
    .adm-emp__proj strong { display: block; font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-size: 20px; color: var(--color-accent-dark); }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
