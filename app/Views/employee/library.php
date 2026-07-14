<?php
/**
 * app/Views/employee/library.php
 * -----------------------------------------------------------------
 * Ma bibliothèque (employé) : les livrables déposés, en grille de
 * cartes (aperçu si image, sinon icône de type). Variable : $deliverables.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

$fmtDate = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
$isImage = fn($path) => in_array(strtolower(pathinfo((string) $path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
$iconFor = function ($path) {
    $ext = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));
    return ['pdf' => '📄', 'zip' => '🗜️', 'doc' => '📝', 'docx' => '📝'][$ext] ?? '📎';
};

// Coquille employé commune (sidebar + entête).
$employeeActive = 'bibliotheque';
$pageTitle      = 'Ma bibliothèque';
$pageSubtitle   = 'Tous les livrables que vous avez déposés';
require ROOT_PATH . '/app/Views/partials/employee-sidebar.php';
?>
        <?php if (empty($deliverables)): ?>
            <p class="adm-empty">Vous n'avez encore déposé aucun livrable.</p>
        <?php else: ?>
            <div class="emp-lib">
                <?php foreach ($deliverables as $f): ?>
                    <a class="emp-lib__card" href="<?= e(BASE_URL) ?>/<?= e($f['stored_path']) ?>"
                       target="_blank" rel="noopener">
                        <span class="emp-lib__thumb">
                            <?php if ($isImage($f['stored_path'])): ?>
                                <img src="<?= e(BASE_URL) ?>/<?= e($f['stored_path']) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <span class="emp-lib__ico" aria-hidden="true"><?= $iconFor($f['stored_path']) ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="emp-lib__body">
                            <span class="emp-lib__title"><?= e($f['original_name']) ?></span>
                            <span class="emp-lib__meta"><?= e($f['order_number']) ?> · <?= e($f['project_name']) ?></span>
                            <span class="emp-lib__date"><?= e($fmtDate($f['created_at'])) ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<style>
    /* Grille bibliothèque (auto-portée, tokens clair/sombre). */
    .emp-lib { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
    .emp-lib__card { display: flex; flex-direction: column; background: var(--color-surface);
        border: 1px solid var(--color-border); border-radius: 16px; overflow: hidden; text-decoration: none;
        transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition); }
    .emp-lib__card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); border-color: rgba(139, 198, 63, .5); }
    .emp-lib__thumb { display: grid; place-items: center; height: 140px; background: var(--color-surface-alt);
        border-bottom: 1px solid var(--color-border); overflow: hidden; }
    .emp-lib__thumb img { width: 100%; height: 100%; object-fit: cover; }
    .emp-lib__ico { font-size: 46px; }
    .emp-lib__body { display: flex; flex-direction: column; gap: 4px; padding: 14px 16px; }
    .emp-lib__title { font-weight: 600; color: var(--color-text); font-size: 14px; line-height: 1.35;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .emp-lib__meta { font-size: 12px; color: var(--color-primary-light); font-weight: 600; }
    .emp-lib__date { font-size: 12px; color: var(--color-muted); }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
