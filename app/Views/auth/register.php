<?php
/**
 * app/Views/auth/register.php
 * -----------------------------------------------------------------
 * Formulaire d'inscription client (nom, email, mot de passe).
 * Réutilise les partials communs header/footer (marque, polices, CSS).
 * La variable $error est fournie par AuthController (null si aucune).
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';
?>
<style>
    /* Styles auto-portés de la page d'authentification.
       Couleurs de marque (copie des tokens de base.css, cf. errors/404.php). */
    .auth { --violet: #4A3F9E; --lime: #8BC63F;
        min-height: 70vh; display: flex; align-items: center; justify-content: center;
        padding: 96px 24px 64px; font-family: 'Inter', system-ui, sans-serif; }
    /* L'intro flash de l'accueil n'a pas lieu d'être hors de la page d'accueil. */
    .intro { display: none !important; }
    .auth__card { width: 100%; max-width: 420px; background: #fff;
        border: 1px solid #eee; border-radius: 18px; padding: 40px 32px;
        box-shadow: 0 20px 60px rgba(74, 63, 158, .08); }
    .auth__title { font-family: 'Poppins', system-ui, sans-serif; font-weight: 800;
        font-size: clamp(24px, 4vw, 32px); color: var(--violet); margin: 0 0 24px; }
    .auth__error { background: #fdecec; color: #b3261e; border-radius: 10px;
        padding: 12px 14px; margin: 0 0 20px; font-size: 14px; }
    .auth__label { display: block; font-weight: 600; font-size: 14px;
        color: #333; margin: 0 0 6px; }
    .auth__input { width: 100%; box-sizing: border-box; padding: 12px 14px;
        margin: 0 0 18px; border: 1px solid #d5d5db; border-radius: 10px;
        font-size: 15px; font-family: inherit; }
    .auth__input:focus { outline: 2px solid var(--violet); outline-offset: 1px;
        border-color: var(--violet); }
    .auth__hint { font-size: 12px; color: #888; margin: -12px 0 18px; }
    .auth__btn { width: 100%; border: 0; cursor: pointer; margin-top: 6px;
        background: var(--violet); color: #fff; font-weight: 600; font-size: 15px;
        padding: 14px; border-radius: 999px; transition: background .2s; }
    .auth__btn:hover { background: var(--lime); }
    .auth__alt { text-align: center; font-size: 14px; color: #666; margin: 22px 0 0; }
    .auth__alt a { color: var(--violet); font-weight: 600; }
</style>

<main class="auth">
    <section class="auth__card">
        <h1 class="auth__title">Créer un compte</h1>

        <?php if (!empty($error)): ?>
            <p class="auth__error" role="alert"><?= e($error) ?></p>
        <?php endif; ?>

        <form class="auth__form" method="post" action="<?= e(BASE_URL) ?>/register" novalidate>
            <?= csrf_field() ?>

            <label class="auth__label" for="name">Nom complet</label>
            <input class="auth__input" type="text" id="name" name="name"
                   autocomplete="name" required>

            <label class="auth__label" for="email">Email</label>
            <input class="auth__input" type="email" id="email" name="email"
                   autocomplete="email" required>

            <label class="auth__label" for="password">Mot de passe</label>
            <input class="auth__input" type="password" id="password" name="password"
                   autocomplete="new-password" minlength="8" required>
            <p class="auth__hint">Au moins 8 caractères.</p>

            <button class="auth__btn" type="submit">Créer mon compte</button>
        </form>

        <p class="auth__alt">Déjà inscrit ?
            <a href="<?= e(BASE_URL) ?>/login">Se connecter</a>
        </p>
    </section>
</main>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
