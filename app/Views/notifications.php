<?php
/**
 * app/Views/notifications.php
 * -----------------------------------------------------------------
 * Liste des notifications de l'utilisateur connecté. Variable : $notifications.
 * (La page a déjà marqué le tout comme lu ; on met en avant celles qui
 *  étaient non lues à l'ouverture via is_read.)
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// Date relative en français (« il y a 5 minutes »).
$relTime = function (string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'à l\'instant';
    if ($diff < 3600)   { $n = (int) floor($diff / 60);    return "il y a $n minute" . ($n > 1 ? 's' : ''); }
    if ($diff < 86400)  { $n = (int) floor($diff / 3600);  return "il y a $n heure"  . ($n > 1 ? 's' : ''); }
    if ($diff < 604800) { $n = (int) floor($diff / 86400); return "il y a $n jour"   . ($n > 1 ? 's' : ''); }
    return date('d/m/Y', strtotime($datetime));
};
?>
<style>
    /* Styles auto-portés de la page notifications (couleurs de marque). */
    .notif { --violet: #4A3F9E; --lime: #8BC63F;
        min-height: 70vh; padding: 96px 24px 64px; font-family: 'Inter', system-ui, sans-serif; }
    .intro { display: none !important; }
    .notif__wrap { max-width: 680px; margin: 0 auto; }
    .notif__title { font-family: 'Poppins', system-ui, sans-serif; font-weight: 800;
        font-size: clamp(24px, 4vw, 32px); color: var(--violet); margin: 0 0 6px; }
    .notif__back { display: inline-block; color: var(--violet); font-size: 14px; font-weight: 600;
        text-decoration: none; margin: 0 0 22px; }
    .empty { background: #faf9ff; border: 1px dashed #d7d2f0; color: #666;
        border-radius: 14px; padding: 40px; text-align: center; font-size: 15px; }
    .list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
    .item { display: block; text-decoration: none; color: inherit; background: #fff; border: 1px solid #eee;
        border-radius: 14px; padding: 16px 18px; box-shadow: 0 12px 40px rgba(74, 63, 158, .05);
        display: flex; align-items: flex-start; gap: 12px; transition: border-color .2s; }
    a.item:hover { border-color: var(--violet); }
    .item--unread { border-left: 3px solid var(--lime); background: #fcfef7; }
    .item__dot { width: 9px; height: 9px; border-radius: 999px; margin-top: 6px; flex: 0 0 auto;
        background: var(--lime); }
    .item__dot.is-read { background: #dcdce4; }
    .item__body { flex: 1; }
    .item__msg { font-size: 15px; color: #222; margin: 0; font-weight: 500; }
    .item__time { display: block; font-size: 12px; color: #999; margin-top: 4px; }
</style>

<main class="notif">
    <div class="notif__wrap">
        <h1 class="notif__title">Notifications</h1>
        <a class="notif__back" href="<?= e(BASE_URL) ?>/<?= e($_SESSION['role'] ?? '') ?>">← Retour à mon espace</a>

        <?php if (empty($notifications)): ?>
            <p class="empty">Aucune notification pour le moment.</p>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($notifications as $n): ?>
                    <?php
                    $unread  = ((int) $n['is_read'] === 0);
                    $hasLink = !empty($n['link']);
                    $tag     = $hasLink ? 'a' : 'div';
                    $href    = $hasLink ? ' href="' . e(BASE_URL . $n['link']) . '"' : '';
                    ?>
                    <li>
                        <<?= $tag ?> class="item<?= $unread ? ' item--unread' : '' ?>"<?= $href ?>>
                            <span class="item__dot<?= $unread ? '' : ' is-read' ?>" aria-hidden="true"></span>
                            <span class="item__body">
                                <span class="item__msg"><?= e($n['title']) ?><?= $unread ? ' <span class="sr-only">(non lue)</span>' : '' ?></span>
                                <span class="item__time"><?= e($relTime($n['created_at'])) ?></span>
                            </span>
                        </<?= $tag ?>>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</main>

<style>
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
        overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
