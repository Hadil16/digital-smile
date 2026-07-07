<?php
/**
 * app/Views/admin/dashboard.php
 * -----------------------------------------------------------------
 * Tableau de bord administrateur — vide pour l'instant (Phase 6).
 * Réutilise les partials communs header/footer. $_SESSION fourni.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';
?>
<style>
    /* Styles auto-portés du tableau de bord (couleurs de marque). */
    .dash { --violet: #4A3F9E; --lime: #8BC63F;
        min-height: 70vh; display: flex; align-items: center; justify-content: center;
        padding: 96px 24px 64px; font-family: 'Inter', system-ui, sans-serif; }
    /* L'intro flash de l'accueil n'a pas lieu d'être ici. */
    .intro { display: none !important; }
    .dash__card { width: 100%; max-width: 560px; background: #fff; border: 1px solid #eee;
        border-radius: 18px; padding: 48px 40px; text-align: center;
        box-shadow: 0 20px 60px rgba(74, 63, 158, .08); }
    .dash__role { display: inline-block; background: var(--violet); color: #fff;
        font-size: 13px; font-weight: 600; padding: 6px 14px; border-radius: 999px; margin: 0 0 18px; }
    .dash__title { font-family: 'Poppins', system-ui, sans-serif; font-weight: 800;
        font-size: clamp(26px, 4vw, 36px); color: var(--violet); margin: 0 0 12px; }
    .dash__note { color: #666; font-size: 15px; margin: 0 0 8px; }
    .dash__count { display: inline-block; background: #fff7e6; color: #b8860b;
        font-size: 13px; font-weight: 600; padding: 6px 14px; border-radius: 999px; margin: 0 0 26px; }
    .dash__actions { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
    .dash__btn { display: inline-block; background: var(--violet); color: #fff;
        text-decoration: none; font-weight: 600; padding: 12px 26px; border-radius: 999px;
        transition: background .2s; }
    .dash__btn:hover { background: var(--lime); }
    .dash__btn--ghost { background: transparent; color: #444; border: 1px solid #d5d5db; }
    .dash__btn--ghost:hover { background: transparent; border-color: var(--violet); color: var(--violet); }
</style>

<main class="dash">
    <section class="dash__card">
        <p class="dash__role">Espace administrateur</p>
        <h1 class="dash__title">Bienvenue, <?= e($_SESSION['name'] ?? '') ?> 👋</h1>
        <p class="dash__note">Suivez et validez les demandes des clients.</p>
        <p class="dash__count"><?= (int) ($pendingCount ?? 0) ?> demande<?= ($pendingCount ?? 0) > 1 ? 's' : '' ?> en attente</p>

        <div class="dash__actions">
            <a class="dash__btn" href="<?= e(BASE_URL) ?>/admin/commandes">Gérer les demandes</a>
            <a class="dash__btn dash__btn--ghost" href="<?= e(BASE_URL) ?>/logout">Déconnexion</a>
        </div>
    </section>
</main>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
