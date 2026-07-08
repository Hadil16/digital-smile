<?php
/**
 * public/seed_admin.php — SEMOIR / RÉINITIALISATION DU COMPTE ADMIN (usage unique).
 * -----------------------------------------------------------------
 * But : garantir un compte administrateur fonctionnel.
 *   - si arezki69@gmail.com n'existe pas → on le CRÉE (rôle admin) ;
 *   - s'il existe déjà → on RÉINITIALISE son mot de passe (et on
 *     s'assure que le compte est actif / non supprimé) pour que la
 *     connexion fonctionne.
 *
 * Sécurité (esprit de install.php) :
 *  - protégé par un jeton : refuse tout accès sans ?token=<jeton> ;
 *  - mot de passe jamais en clair en base (password_hash BCRYPT) ;
 *  - s'AUTO-SUPPRIME après un passage réussi (fichier jetable).
 *
 * >>> Ouvrez l'URL une fois avec le bon jeton. Le fichier disparaît seul. <<<
 * -----------------------------------------------------------------
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

// Jeton d'accès. CHANGEZ cette valeur, puis ouvrez l'URL avec ?token=<cette valeur>.
// (Fichier jetable et local : le jeton disparaît avec le fichier après usage.)
const INSTALL_TOKEN = 'ds-admin-CHANGE-MOI-2026';

// Garde : sans le bon jeton, on refuse (comparaison en temps constant).
if (!hash_equals(INSTALL_TOKEN, (string) ($_GET['token'] ?? ''))) {
    http_response_code(403);
    exit('Accès refusé : jeton manquant ou invalide.');
}

header('Content-Type: text/plain; charset=utf-8');

// Compte administrateur du propriétaire.
const ADMIN_NAME  = 'Yahiaoui Arezki';
const ADMIN_EMAIL = 'arezki69@gmail.com';
const ADMIN_PASS  = 'ChangeMe2026!';

try {
    $db   = Database::getConnection();
    $hash = password_hash(ADMIN_PASS, PASSWORD_BCRYPT);

    echo "=== Compte administrateur Digital Smile ===\n\n";

    // Le compte existe-t-il déjà (n'importe quelle ligne pour cet email) ?
    $sel = $db->prepare("SELECT id FROM users WHERE email = :e LIMIT 1");
    $sel->execute([':e' => ADMIN_EMAIL]);
    $existingId = $sel->fetchColumn();

    if ($existingId === false) {
        // Création : rôle admin résolu par son NOM (jamais d'id en dur).
        $roleId = (int) $db->query("SELECT id FROM roles WHERE name = 'admin'")->fetchColumn();
        if ($roleId === 0) {
            throw new RuntimeException('Rôle "admin" introuvable : importez d\'abord le schéma.');
        }
        $db->prepare(
            "INSERT INTO users (role_id, full_name, email, password_hash)
             VALUES (:role, :name, :email, :hash)"
        )->execute([
            ':role'  => $roleId,
            ':name'  => ADMIN_NAME,
            ':email' => ADMIN_EMAIL,
            ':hash'  => $hash,
        ]);
        echo "  Compte créé : " . ADMIN_NAME . " <" . ADMIN_EMAIL . ">\n";
    } else {
        // Réinitialisation : nouveau mot de passe + compte actif et non supprimé,
        // pour que la connexion fonctionne à coup sûr.
        $db->prepare(
            "UPDATE users
             SET password_hash = :hash, is_active = 1, deleted_at = NULL
             WHERE email = :email"
        )->execute([':hash' => $hash, ':email' => ADMIN_EMAIL]);
        echo "  Compte existant <" . ADMIN_EMAIL . "> : mot de passe réinitialisé.\n";
    }

    echo "\nConnexion : email « " . ADMIN_EMAIL . " », mot de passe « " . ADMIN_PASS . " »\n";
    echo "(à changer dès que possible).\n\n";

    // Auto-destruction (fichier jetable) — uniquement après un passage réussi.
    if (@unlink(__FILE__)) {
        echo "✅ public/seed_admin.php s'est supprimé automatiquement.\n";
    } else {
        echo "⚠️ Suppression automatique impossible : supprimez public/seed_admin.php à la main.\n";
    }
} catch (Throwable $e) {
    http_response_code(500);
    // On NE supprime PAS le fichier en cas d'erreur, pour laisser corriger.
    echo "\nErreur : " . $e->getMessage() . "\n";
    echo "Le fichier n'a pas été supprimé.\n";
}
