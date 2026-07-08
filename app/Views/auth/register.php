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
    /* Scène d'authentification premium sombre (accent de design, fixe dans les 2 thèmes).
       Styles auto-portés — mêmes classes .auth__* que login.php. */
    .auth {
        position: relative; isolation: isolate; overflow: hidden;
        min-height: 100vh; display: flex; align-items: center; justify-content: center;
        padding: 120px 24px 80px; color: #fff;
        font-family: 'Poppins', system-ui, sans-serif;
        background: linear-gradient(165deg, #1a1636, #14122b 60%, #0f0d1f);
    }
    .intro { display: none !important; } /* pas d'intro flash hors accueil */
    /* Blobs lumineux flous derrière la carte. */
    .auth__blob { position: absolute; z-index: 0; width: 46vw; max-width: 520px; aspect-ratio: 1;
        border-radius: 50%; filter: blur(70px); opacity: .5; pointer-events: none; }
    .auth__blob--v { top: -12%; left: -8%; background: radial-gradient(circle, rgba(74, 63, 158, .6), transparent 65%); }
    .auth__blob--l { bottom: -14%; right: -10%; background: radial-gradient(circle, rgba(139, 198, 63, .45), transparent 65%); }

    .auth__card {
        position: relative; z-index: 1; width: 100%; max-width: 420px;
        background: rgba(255, 255, 255, .04); border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 26px; padding: 38px;
        -webkit-backdrop-filter: blur(16px); backdrop-filter: blur(16px);
        box-shadow: 0 30px 80px rgba(0, 0, 0, .4);
    }
    .auth__brand { display: flex; align-items: center; gap: 10px; margin: 0 0 22px; }
    .auth__brand img { height: 34px; width: auto; border-radius: 8px; }
    .auth__brand span { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 20px; }
    .auth__title { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 800;
        font-size: clamp(26px, 4vw, 34px); color: #fff; margin: 0 0 24px; }
    .auth__error { background: rgba(179, 38, 30, .18); border: 1px solid rgba(229, 114, 107, .4);
        color: #ffb3ad; border-radius: 12px; padding: 12px 14px; margin: 0 0 20px; font-size: 14px; }
    .auth__label { display: block; font-weight: 600; font-size: 14px; color: rgba(255, 255, 255, .8); margin: 0 0 6px; }
    .auth__input { width: 100%; box-sizing: border-box; padding: 13px 15px; margin: 0 0 18px;
        background: rgba(255, 255, 255, .05); border: 1px solid rgba(255, 255, 255, .14);
        border-radius: 12px; font-size: 15px; font-family: inherit; color: #fff; }
    .auth__input::placeholder { color: rgba(255, 255, 255, .4); }
    .auth__input:focus { outline: none; border-color: #8BC63F; box-shadow: 0 0 0 3px rgba(139, 198, 63, .28); }
    .auth__hint { font-size: 12px; color: rgba(255, 255, 255, .5); margin: -12px 0 18px; }
    .auth__btn { width: 100%; border: 0; cursor: pointer; margin-top: 6px;
        font-family: 'Poppins', system-ui, sans-serif; font-weight: 700; font-size: 15px;
        background: #8BC63F; color: #1a1730; padding: 15px; border-radius: 100px;
        box-shadow: 0 14px 34px rgba(139, 198, 63, .3); transition: transform .2s ease, box-shadow .2s ease; }
    .auth__btn:hover { transform: translateY(-2px); box-shadow: 0 18px 40px rgba(139, 198, 63, .42); }
    .auth__alt { text-align: center; font-size: 14px; color: rgba(255, 255, 255, .65); margin: 22px 0 0; }
    .auth__alt a { color: #8BC63F; font-weight: 600; }
</style>

<main class="auth">
    <span class="auth__blob auth__blob--v" aria-hidden="true"></span>
    <span class="auth__blob auth__blob--l" aria-hidden="true"></span>
    <section class="auth__card">
        <div class="auth__brand">
            <img src="assets/img/logo.jpg" alt="">
            <span>Digital Smile</span>
        </div>
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
