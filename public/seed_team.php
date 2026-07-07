<?php
/**
 * public/seed_team.php — SEMOIR D'ÉQUIPE À USAGE UNIQUE.
 * -----------------------------------------------------------------
 * Crée les comptes réels de l'équipe Digital Smile (1 admin + employés)
 * avec mots de passe HACHÉS (BCRYPT), via PDO.
 *
 * Sécurité (esprit de install.php / seed.php) :
 *  - protégé par un jeton : refuse tout accès sans ?token=<jeton> ;
 *  - IDEMPOTENT : un email déjà présent est ignoré (rejouable sans doublon) ;
 *  - mots de passe jamais en clair en base (password_hash) ;
 *  - s'AUTO-SUPPRIME après un passage réussi (fichier jetable).
 *
 * >>> Ouvrez l'URL une fois avec le bon jeton. Le fichier disparaît seul. <<<
 * -----------------------------------------------------------------
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

// Jeton d'accès. CHANGEZ cette valeur, puis ouvrez l'URL avec ?token=<cette valeur>.
// (Fichier jetable et local : le jeton disparaît avec le fichier après usage.)
const INSTALL_TOKEN = 'ds-team-CHANGE-MOI-2026';

// Garde : sans le bon jeton, on refuse (comparaison en temps constant).
if (!hash_equals(INSTALL_TOKEN, (string) ($_GET['token'] ?? ''))) {
    http_response_code(403);
    exit('Accès refusé : jeton manquant ou invalide.');
}

header('Content-Type: text/plain; charset=utf-8');

// Mot de passe temporaire commun (à changer par chacun ensuite).
const TEMP_PASSWORD = 'ChangeMe2026!';

// Comptes à créer.
$admin = ['full_name' => 'Yahiaoui Arezki', 'email' => 'arezki69@gmail.com'];
$team  = [
    ['full_name' => 'Zakaria Bli', 'email' => 'zakaria.bli99@gmail.com'],
    ['full_name' => 'Benchaa',     'email' => 'benchaa05@gmail.com'],
    ['full_name' => 'Kabadi',      'email' => 'kabadi_etd@esgen.edu.dz'],
];

// Vrai si un compte utilise déjà cet email (idempotence).
function email_exists(PDO $db, string $email): bool
{
    $s = $db->prepare("SELECT 1 FROM users WHERE email = :e LIMIT 1");
    $s->execute([':e' => $email]);
    return (bool) $s->fetchColumn();
}

// Id d'un rôle par son nom (jamais d'id en dur — règle RBAC).
function role_id(PDO $db, string $name): int
{
    $s = $db->prepare("SELECT id FROM roles WHERE name = :n LIMIT 1");
    $s->execute([':n' => $name]);
    return (int) $s->fetchColumn();
}

$created = 0;
$skipped = 0;

try {
    $db = Database::getConnection();

    // Départements existants (pour rattacher les employés). Obligatoire.
    $depts = $db->query("SELECT id, name FROM departments ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    if (!$depts) {
        throw new RuntimeException('Aucun département en base : importez d\'abord le schéma et les seeds.');
    }

    echo "=== Semis de l'équipe Digital Smile ===\n\n";

    // --- 1) Administrateur ------------------------------------------------
    echo "Administrateur\n";
    if (email_exists($db, $admin['email'])) {
        $skipped++;
        echo "  {$admin['full_name']} <{$admin['email']}> : déjà présent (ignoré)\n";
    } else {
        $ins = $db->prepare(
            "INSERT INTO users (role_id, full_name, email, password_hash)
             VALUES (:role, :name, :email, :hash)"
        );
        $ins->execute([
            ':role'  => role_id($db, 'admin'),
            ':name'  => $admin['full_name'],
            ':email' => $admin['email'],
            ':hash'  => password_hash(TEMP_PASSWORD, PASSWORD_BCRYPT),
        ]);
        $created++;
        echo "  {$admin['full_name']} <{$admin['email']}> : créé\n";
    }

    // --- 2) Employés ------------------------------------------------------
    echo "\nEmployés\n";
    $employeeRole = role_id($db, 'employee');

    foreach ($team as $i => $emp) {
        if (email_exists($db, $emp['email'])) {
            $skipped++;
            echo "  {$emp['full_name']} <{$emp['email']}> : déjà présent (ignoré)\n";
            continue;
        }

        // Rattachement à un département existant (réparti), specialty = son nom.
        $dep = $depts[$i % count($depts)];

        // Deux écritures liées dans une transaction (user + fiche employé).
        $db->beginTransaction();
        try {
            $u = $db->prepare(
                "INSERT INTO users (role_id, full_name, email, password_hash)
                 VALUES (:role, :name, :email, :hash)"
            );
            $u->execute([
                ':role'  => $employeeRole,
                ':name'  => $emp['full_name'],
                ':email' => $emp['email'],
                ':hash'  => password_hash(TEMP_PASSWORD, PASSWORD_BCRYPT),
            ]);
            $userId = (int) $db->lastInsertId();

            $e = $db->prepare(
                "INSERT INTO employees (user_id, department_id, specialty)
                 VALUES (:uid, :dept, :spec)"
            );
            $e->execute([':uid' => $userId, ':dept' => (int) $dep['id'], ':spec' => $dep['name']]);

            $db->commit();
            $created++;
            echo "  {$emp['full_name']} <{$emp['email']}> : créé (département : {$dep['name']})\n";
        } catch (Throwable $ex) {
            $db->rollBack();
            throw $ex;
        }
    }

    // --- Bilan + note de connexion ---------------------------------------
    echo "\nBilan : {$created} créé(s), {$skipped} ignoré(s) (déjà présents).\n\n";
    echo "Connexion : tout le monde se connecte avec le mot de passe temporaire\n";
    echo "« " . TEMP_PASSWORD . " » — à changer par chacun dès que possible.\n\n";

    // Auto-destruction (fichier jetable) — uniquement après un passage réussi.
    if (@unlink(__FILE__)) {
        echo "✅ public/seed_team.php s'est supprimé automatiquement.\n";
    } else {
        echo "⚠️ Suppression automatique impossible : supprimez public/seed_team.php à la main.\n";
    }
} catch (Throwable $e) {
    http_response_code(500);
    // On NE supprime PAS le fichier en cas d'erreur, pour laisser corriger.
    echo "\nErreur pendant le semis : " . $e->getMessage() . "\n";
    echo "Le fichier n'a pas été supprimé.\n";
}
