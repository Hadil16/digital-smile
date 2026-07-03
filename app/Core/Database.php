<?php
/**
 * app/Core/Database.php
 * -----------------------------------------------------------------
 * Connexion à MySQL via PDO.
 *
 * Pourquoi PDO ? Parce qu'il permet les "requêtes préparées",
 * notre bouclier N°1 contre les injections SQL (la faille la plus
 * dangereuse du web). On ne concatène JAMAIS une variable dans une
 * requête ; on passe toujours par des paramètres liés (:param).
 *
 * Pourquoi Singleton ? On ouvre UNE seule connexion et on la réutilise
 * partout, au lieu d'en rouvrir une à chaque requête (plus rapide).
 * -----------------------------------------------------------------
 */

class Database
{
    // L'instance unique de connexion (le "Singleton").
    private static ?PDO $instance = null;

    // Constructeur privé : on interdit de faire "new Database()".
    private function __construct() {}

    /**
     * Renvoie la connexion PDO. La crée si elle n'existe pas encore.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST
                 . ';dbname=' . DB_NAME
                 . ';charset=' . DB_CHARSET;

            // Options de sécurité et de confort :
            $options = [
                // 1. Lever une exception en cas d'erreur (au lieu de rester silencieux).
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // 2. Récupérer les résultats sous forme de tableau associatif.
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // 3. Désactiver l'émulation : de VRAIES requêtes préparées côté MySQL.
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // En dev on montre l'erreur, en prod on reste discret.
                if (APP_ENV === 'dev') {
                    die('Erreur de connexion à la base : ' . $e->getMessage());
                }
                die('Le service est momentanément indisponible.');
            }
        }

        return self::$instance;
    }
}
