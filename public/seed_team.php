<?php
/**
 * public/seed_team.php — RE-SEMOIR DES EMPLOYÉS (usage unique).
 * -----------------------------------------------------------------
 * Recrée les 3 comptes employés de l'agence (l'admin existe déjà).
 * Passe par User::createEmployee() : chaque employé = une ligne `users`
 * (rôle employee, mot de passe BCRYPT) + une fiche `employees` liée à
 * un département, le tout dans une transaction.
 *
 * Sécurité (esprit de install.php) :
 *  - protégé par un jeton : refuse tout accès sans ?token=<jeton> ;
 *  - IDEMPOTENT : un email déjà présent est ignoré (rejouable) ;
 *  - s'AUTO-SUPPRIME après un passage réussi (fichier jetable).
 *
 * >>> Ouvrez l'URL une fois avec le bon jeton. Le fichier disparaît seul. <<<
 * -----------------------------------------------------------------
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';   // classe mère
require_once __DIR__ . '/../app/Models/User.php';  // createEmployee()

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

// Les 3 employés à (re)créer.
$team = [
    ['name' => 'Zakaria Bli', 'email' => 'zakaria.bli99@gmail.com'],
    ['name' => 'Benchaa',     'email' => 'benchaa05@gmail.com'],
    ['name' => 'Kabadi',      'email' => 'kabadi_etd@esgen.edu.dz'],
];

$created = 0;
$skipped = 0;

try {
    $userModel = new User();
    $db = Database::getConnection();

    // Départements existants (obligatoire : chaque employé en référence un).
    $depts = $db->query("SELECT id FROM departments ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    if (!$depts) {
        throw new RuntimeException('Aucun département en base : importez d\'abord le schéma et les seeds.');
    }

    echo "=== Re-semis des employés Digital Smile ===\n\n";

    foreach ($team as $i => $emp) {
        if ($userModel->emailExists($emp['email'])) {
            $skipped++;
            echo "  {$emp['name']} <{$emp['email']}> : déjà présent (ignoré)\n";
            continue;
        }

        // Rattachement à un département existant (réparti sur la liste réelle).
        $userModel->createEmployee([
            'name'          => $emp['name'],
            'email'         => $emp['email'],
            'password'      => TEMP_PASSWORD,
            'department_id' => (int) $depts[$i % count($depts)],
        ]);
        $created++;
        echo "  {$emp['name']} <{$emp['email']}> : créé\n";
    }

    echo "\nBilan : {$created} créé(s), {$skipped} ignoré(s) (déjà présents).\n";
    echo "Connexion : mot de passe temporaire « " . TEMP_PASSWORD . " » — à changer ensuite.\n\n";

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
