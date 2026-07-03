<?php
/**
 * public/health.php
 * -----------------------------------------------------------------
 * Page de "bilan de santé" du système.
 * Ouvrez-la dans le navigateur pour vérifier d'un coup d'œil que
 * tout fonctionne : PHP, connexion MySQL, tables, langues.
 * Utile pendant le développement, à supprimer avant la mise en ligne.
 * -----------------------------------------------------------------
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$checks = [];

// 1. Version de PHP (on veut 8.0+)
$phpOk = version_compare(PHP_VERSION, '8.0.0', '>=');
$checks[] = ['PHP ' . PHP_VERSION . ' (8.0+ requis)', $phpOk];

// 2. Extension PDO MySQL présente ?
$checks[] = ['Extension PDO MySQL', extension_loaded('pdo_mysql')];

// 3. Connexion à la base
$dbOk = false; $tableCount = 0;
try {
    $db = Database::getConnection();
    $dbOk = true;
    // 4. Compter les tables créées
    $stmt = $db->query("SHOW TABLES");
    $tableCount = $stmt->rowCount();
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}
$checks[] = ['Connexion MySQL (base "' . DB_NAME . '")', $dbOk];
$checks[] = ['Tables présentes : ' . $tableCount . ' / 15 attendues', $tableCount >= 15];

// 5. Dossier uploads accessible en écriture ?
$checks[] = ['Dossier uploads inscriptible', is_writable(UPLOAD_PATH)];

// 6. Fichiers de langue présents ?
$langOk = true;
foreach (LANGUAGES as $l) {
    if (!file_exists(ROOT_PATH . "/lang/$l.php")) $langOk = false;
}
$checks[] = ['Fichiers de langue (fr, ar, en)', $langOk];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bilan de santé — Digital Smile</title>
    <style>
        body { font-family: system-ui, sans-serif; background:#f4f4f7; color:#222;
               display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
        .card { background:#fff; padding:2rem 2.5rem; border-radius:16px;
                box-shadow:0 8px 30px rgba(0,0,0,.08); max-width:520px; width:90%; }
        h1 { color:#4A3F9E; margin-top:0; font-size:1.4rem; }
        .row { display:flex; justify-content:space-between; align-items:center;
               padding:.7rem 0; border-bottom:1px solid #eee; }
        .row:last-child { border-bottom:none; }
        .ok  { color:#3B6D11; font-weight:600; }
        .bad { color:#A32D2D; font-weight:600; }
        .foot { margin-top:1.2rem; font-size:.85rem; color:#666; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🩺 Bilan de santé — Digital Smile</h1>
        <?php foreach ($checks as [$label, $ok]): ?>
            <div class="row">
                <span><?= htmlspecialchars($label) ?></span>
                <span class="<?= $ok ? 'ok' : 'bad' ?>"><?= $ok ? '✅ OK' : '❌ Échec' ?></span>
            </div>
        <?php endforeach; ?>
        <?php if (!empty($dbError)): ?>
            <p class="bad" style="margin-top:1rem;">⚠️ <?= htmlspecialchars($dbError) ?></p>
        <?php endif; ?>
        <p class="foot">
            Si tout est vert : votre base est prête. Lancez ensuite
            <code>install.php</code> pour créer le compte admin,
            puis supprimez ces deux fichiers de test.
        </p>
    </div>
</body>
</html>
