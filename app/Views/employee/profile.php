<?php
/**
 * app/Views/employee/profile.php
 * -----------------------------------------------------------------
 * Mon profil (employé) : photo, années d'expérience, biographie.
 * Variables : $profile, $canEdit, $flash, $error.
 * Dégradation propre : si la migration n'est pas appliquée ($canEdit=false),
 * on l'explique et l'enregistrement est neutralisé côté contrôleur.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// Valeurs affichées : ce qui a été saisi (POST en erreur) sinon la fiche.
$photo   = $profile['photo'] ?? null;
$dept    = $profile['department_name'] ?? '';
$vExp    = $_POST['experience_years'] ?? ($profile['experience_years'] ?? '');
$vBio    = $_POST['bio'] ?? ($profile['bio'] ?? '');
$empName = $profile['full_name'] ?? ($_SESSION['name'] ?? 'Employé');
$pp = array_values(array_filter(preg_split('/\s+/', trim($empName))));
$initials = strtoupper(mb_substr($pp[0] ?? 'E', 0, 1, 'UTF-8')
          . (count($pp) > 1 ? mb_substr((string) end($pp), 0, 1, 'UTF-8') : ''));

// Coquille employé commune (sidebar + entête).
$employeeActive = 'profil';
$pageTitle      = 'Mon profil';
$pageSubtitle   = 'Votre photo et votre expérience';
require ROOT_PATH . '/app/Views/partials/employee-sidebar.php';
?>
        <?php if (!empty($flash)): ?>
            <p class="adm-flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <p class="adm-error" role="alert"><?= e($error) ?></p>
        <?php endif; ?>
        <?php if (empty($canEdit)): ?>
            <p class="adm-error" role="status">
                La migration du profil n'a pas encore été appliquée : l'enregistrement sera possible
                une fois le fichier <code>database/migrations/2026_07_employee_profile.sql</code> exécuté.
            </p>
        <?php endif; ?>

        <div class="adm-card">
            <form method="post" action="<?= e(BASE_URL) ?>/employe/profil" enctype="multipart/form-data" novalidate>
                <?= csrf_field() ?>

                <!-- Photo actuelle + dépôt d'une nouvelle -->
                <div class="emp-prof__photo">
                    <span class="emp-prof__ava" aria-hidden="true">
                        <?php if (!empty($photo)): ?>
                            <img src="<?= e(BASE_URL) ?>/<?= e($photo) ?>" alt="">
                        <?php else: ?>
                            <?= e($initials) ?>
                        <?php endif; ?>
                    </span>
                    <div class="emp-prof__photo-field">
                        <div class="adm-field">
                            <label class="adm-label" for="photo">Photo de profil</label>
                            <input class="adm-input" type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png">
                            <p class="emp-prof__hint"><?= e($empName) ?><?= $dept !== '' ? ' — ' . e($dept) : '' ?> · JPG ou PNG, 2 Mo max.</p>
                        </div>
                    </div>
                </div>

                <div class="adm-grid">
                    <div class="adm-field">
                        <label class="adm-label" for="experience_years">Années d'expérience (0 à 50)</label>
                        <input class="adm-input" type="number" id="experience_years" name="experience_years"
                               min="0" max="50" step="1" value="<?= e((string) $vExp) ?>">
                    </div>
                </div>

                <div class="adm-field">
                    <label class="adm-label" for="bio">Biographie (500 caractères max.)</label>
                    <textarea class="adm-textarea" id="bio" name="bio" maxlength="500"
                              placeholder="Quelques mots sur votre parcours…"><?= e((string) $vBio) ?></textarea>
                </div>

                <button class="adm-btn adm-btn--primary" type="submit">Enregistrer</button>
            </form>
        </div>
    </main>
</div>

<style>
    /* Bloc photo du profil (auto-porté, tokens clair/sombre). */
    .emp-prof__photo { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; margin: 0 0 8px; }
    .emp-prof__ava { display: inline-grid; place-items: center; width: 76px; height: 76px; border-radius: 20px;
        flex: 0 0 auto; overflow: hidden; font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif;
        font-weight: 800; font-size: 26px; color: #1a1730; background: linear-gradient(135deg, #8BC63F, #4A3F9E); }
    .emp-prof__ava img { width: 100%; height: 100%; object-fit: cover; }
    .emp-prof__photo-field { flex: 1; min-width: 220px; }
    .emp-prof__photo-field .adm-field { margin: 0; }
    .emp-prof__hint { font-size: 12px; color: var(--color-muted); margin: 8px 0 0; }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
