<?php
/**
 * public/seed.php — SEMOIR DE BASE À USAGE UNIQUE.
 * -----------------------------------------------------------------
 * Remplit la base avec les données de référence (départements +
 * catégories + services), en exécutant les deux fichiers .sql via PDO.
 *
 * Sécurité (esprit de install.php) :
 *  - protégé par un jeton : refuse tout accès sans ?token=<jeton> ;
 *  - IDEMPOTENT : les .sql n'insèrent jamais de doublon (rejouable) ;
 *  - s'AUTO-SUPPRIME après un remplissage réussi (fichier jetable).
 *
 * >>> Ouvrez l'URL une fois avec le bon jeton. Le fichier disparaît seul. <<<
 * -----------------------------------------------------------------
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

// Jeton d'accès. CHANGEZ cette valeur, puis ouvrez l'URL avec ?token=<cette valeur>.
// (Fichier jetable et local : le jeton disparaît avec le fichier après usage.)
const INSTALL_TOKEN = 'ds-seed-CHANGEZ-MOI-2026';

// Garde : sans le bon jeton, on refuse (comparaison en temps constant).
if (!hash_equals(INSTALL_TOKEN, (string) ($_GET['token'] ?? ''))) {
    http_response_code(403);
    exit('Accès refusé : jeton manquant ou invalide.');
}

header('Content-Type: text/plain; charset=utf-8');

// Exécute un fichier .sql instruction par instruction (une seule par exec).
// On retire les commentaires de ligne puis on découpe sur ';'
// (aucune de nos valeurs ne contient de ';', le découpage est sûr).
function run_sql_file(PDO $db, string $path): void
{
    $sql = @file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException("Fichier SQL introuvable : $path");
    }
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);           // retire les commentaires
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        $db->exec($stmt);
    }
}

// Compte les lignes d'une table (nom issu d'une liste codée en dur, jamais d'entrée utilisateur).
function count_rows(PDO $db, string $table): int
{
    return (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}

$tables = ['departments', 'service_categories', 'services'];

try {
    $db  = Database::getConnection();
    $dir = ROOT_PATH . '/database';

    // Comptes AVANT.
    $before = [];
    foreach ($tables as $t) {
        $before[$t] = count_rows($db, $t);
    }

    // Départements AVANT services (les services référencent un département).
    run_sql_file($db, $dir . '/seed_departments.sql');
    run_sql_file($db, $dir . '/seed_services.sql');

    // Rapport français (total + ajouts de cette exécution).
    echo "=== Semis de la base terminé ===\n\n";
    foreach ($tables as $t) {
        $now = count_rows($db, $t);
        $add = $now - $before[$t];
        echo sprintf("  %-20s : %2d au total  (+%d cette fois)\n", $t, $now, $add);
    }
    echo "\nIdempotent : relancer l'opération n'ajoutera aucun doublon.\n\n";

    // Auto-destruction (fichier jetable) — uniquement après succès.
    if (@unlink(__FILE__)) {
        echo "✅ public/seed.php s'est supprimé automatiquement.\n";
    } else {
        echo "⚠️ Suppression automatique impossible : supprimez public/seed.php à la main.\n";
    }
} catch (Throwable $e) {
    http_response_code(500);
    // On NE supprime PAS le fichier en cas d'erreur, pour laisser corriger.
    echo "Erreur pendant le semis : " . $e->getMessage() . "\n";
    echo "Le fichier n'a pas été supprimé. Vérifiez que database/schema.sql est bien importé.\n";
}
