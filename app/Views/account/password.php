<?php
/**
 * app/Views/account/password.php
 * -----------------------------------------------------------------
 * « Changer mon mot de passe » — commun aux 3 rôles. On réutilise la
 * coquille d'espace correspondant au rôle connecté (sidebar admin,
 * employé ou client). Variables : $error, $flash.
 * PRÉSENTATION UNIQUEMENT — aucune logique métier ici.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// On choisit la barre latérale selon le rôle et on marque « Sécurité » actif.
$role         = $_SESSION['role'] ?? 'client';
$pageTitle    = 'Sécurité';
$pageSubtitle = 'Changer mon mot de passe';

if ($role === 'admin') {
    $adminActive = 'securite';
    $sidebar     = 'admin-sidebar.php';
} elseif ($role === 'employee') {
    $employeeActive = 'securite';
    $sidebar        = 'employee-sidebar.php';
} else {
    $clientActive = 'securite';
    $sidebar      = 'client-sidebar.php';
}
require ROOT_PATH . '/app/Views/partials/' . $sidebar;
?>
        <?php if (!empty($flash)): ?>
            <p class="adm-flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <p class="adm-error" role="alert"><?= e($error) ?></p>
        <?php endif; ?>

        <div class="adm-card" style="max-width:520px">
            <form method="post" action="<?= e(BASE_URL) ?>/compte/mot-de-passe" novalidate>
                <?= csrf_field() ?>

                <div class="adm-field">
                    <label class="adm-label" for="current">Mot de passe actuel</label>
                    <input class="adm-input" type="password" id="current" name="current"
                           autocomplete="current-password" required>
                </div>

                <div class="adm-field">
                    <label class="adm-label" for="new">Nouveau mot de passe (8 caractères min.)</label>
                    <input class="adm-input" type="password" id="new" name="new"
                           autocomplete="new-password" minlength="8" required>
                </div>

                <div class="adm-field">
                    <label class="adm-label" for="confirm">Confirmer le nouveau mot de passe</label>
                    <input class="adm-input" type="password" id="confirm" name="confirm"
                           autocomplete="new-password" minlength="8" required>
                </div>

                <button class="adm-btn adm-btn--primary" type="submit">Mettre à jour le mot de passe</button>
            </form>
        </div>
    </main>
</div>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
