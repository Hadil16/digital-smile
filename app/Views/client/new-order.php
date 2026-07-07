<?php
/**
 * app/Views/client/new-order.php
 * -----------------------------------------------------------------
 * Formulaire de nouvelle demande de projet (client).
 * Variables fournies par ClientController : $services, $error, $old.
 * Réutilise les partials communs header/footer.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';
?>
<style>
    /* Styles auto-portés du formulaire (couleurs de marque). */
    .form { --violet: #4A3F9E; --lime: #8BC63F;
        min-height: 70vh; display: flex; align-items: flex-start; justify-content: center;
        padding: 96px 24px 64px; font-family: 'Inter', system-ui, sans-serif; }
    .intro { display: none !important; } /* pas d'intro flash hors accueil */
    .form__card { width: 100%; max-width: 560px; background: #fff; border: 1px solid #eee;
        border-radius: 18px; padding: 40px 36px; box-shadow: 0 20px 60px rgba(74, 63, 158, .08); }
    .form__back { display: inline-block; color: var(--violet); font-size: 14px;
        font-weight: 600; text-decoration: none; margin: 0 0 14px; }
    .form__title { font-family: 'Poppins', system-ui, sans-serif; font-weight: 800;
        font-size: clamp(24px, 4vw, 32px); color: var(--violet); margin: 0 0 22px; }
    .form__error { background: #fdecec; color: #b3261e; border-radius: 10px;
        padding: 12px 14px; margin: 0 0 20px; font-size: 14px; }
    .form__label { display: block; font-weight: 600; font-size: 14px; color: #333; margin: 0 0 6px; }
    .form__field { width: 100%; box-sizing: border-box; padding: 12px 14px; margin: 0 0 18px;
        border: 1px solid #d5d5db; border-radius: 10px; font-size: 15px;
        font-family: inherit; background: #fff; }
    .form__field:focus { outline: 2px solid var(--violet); outline-offset: 1px; border-color: var(--violet); }
    textarea.form__field { resize: vertical; min-height: 110px; }
    .form__hint { font-size: 12px; color: #888; margin: -12px 0 18px; }
    .form__btn { width: 100%; border: 0; cursor: pointer; margin-top: 6px; background: var(--violet);
        color: #fff; font-weight: 600; font-size: 15px; padding: 14px; border-radius: 999px;
        transition: background .2s; }
    .form__btn:hover { background: var(--lime); }
</style>

<main class="form">
    <section class="form__card">
        <a class="form__back" href="<?= e(BASE_URL) ?>/client">← Retour au tableau de bord</a>
        <h1 class="form__title">Nouvelle demande</h1>

        <?php if (!empty($error)): ?>
            <p class="form__error" role="alert"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post" action="<?= e(BASE_URL) ?>/client/nouvelle-demande" novalidate>
            <?= csrf_field() ?>

            <label class="form__label" for="service_id">Service souhaité</label>
            <select class="form__field" id="service_id" name="service_id" required>
                <option value="">— Choisir un service —</option>
                <?php foreach ($services as $s): ?>
                    <option value="<?= (int) $s['id'] ?>"
                        <?= ((string) $s['id'] === $old['service_id']) ? 'selected' : '' ?>>
                        <?= e($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="form__label" for="description">Description de votre besoin</label>
            <textarea class="form__field" id="description" name="description" rows="5"
                      required><?= e($old['description']) ?></textarea>

            <label class="form__label" for="budget">Budget indicatif (DZD)</label>
            <input class="form__field" type="number" id="budget" name="budget"
                   min="0" step="100" value="<?= e($old['budget']) ?>">
            <p class="form__hint">Optionnel — laissez vide si vous ne savez pas encore.</p>

            <label class="form__label" for="deadline">Échéance souhaitée</label>
            <input class="form__field" type="date" id="deadline" name="deadline"
                   required value="<?= e($old['deadline']) ?>">

            <button class="form__btn" type="submit">Envoyer la demande</button>
        </form>
    </section>
</main>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
