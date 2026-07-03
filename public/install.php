<?php
/**
 * public/install.php
 * -----------------------------------------------------------------
 * Script d'installation à lancer UNE SEULE FOIS.
 * Il crée le compte administrateur avec un mot de passe HACHÉ
 * correctement par PHP (password_hash), ce qu'on ne peut pas faire
 * en SQL pur.
 *
 * >>> APRÈS UTILISATION, SUPPRIMEZ CE FICHIER. <<<
 * -----------------------------------------------------------------
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$adminEmail    = 'admin@digitalsmile.dz';
$adminName     = 'Yahiaoui Arezki';
$adminPassword = 'Admin@2026';   // <-- changez-le après la 1re connexion
$adminPhone    = '+213542054123';

try {
    $db = Database::getConnection();

    // On vérifie que l'admin n'existe pas déjà (idempotent : relançable sans doublon).
    $check = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $check->execute([':email' => $adminEmail]);

    if ($check->fetch()) {
        echo "<h2>ℹ️ Le compte administrateur existe déjà.</h2>";
        echo "<p>Rien à faire. Vous pouvez supprimer ce fichier install.php.</p>";
        exit;
    }

    // Hachage sécurisé du mot de passe (bcrypt).
    $hash = password_hash($adminPassword, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (role_id, full_name, email, password_hash, phone, lang)
            VALUES (1, :name, :email, :hash, :phone, 'fr')";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':name'  => $adminName,
        ':email' => $adminEmail,
        ':hash'  => $hash,
        ':phone' => $adminPhone,
    ]);

    echo "<h2>✅ Compte administrateur créé avec succès.</h2>";
    echo "<ul>";
    echo "<li><strong>Email :</strong> " . htmlspecialchars($adminEmail) . "</li>";
    echo "<li><strong>Mot de passe :</strong> " . htmlspecialchars($adminPassword) . "</li>";
    echo "</ul>";
    echo "<p style='color:#A32D2D'><strong>⚠️ IMPORTANT :</strong> supprimez maintenant le fichier "
       . "<code>public/install.php</code> et changez ce mot de passe après connexion.</p>";

} catch (Throwable $e) {
    echo "<h2>❌ Erreur pendant l'installation.</h2>";
    if (APP_ENV === 'dev') {
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        echo "<p>Vérifiez que vous avez bien importé <code>database/schema.sql</code> dans phpMyAdmin.</p>";
    }
}
