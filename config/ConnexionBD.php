<?php
/**
 * Fichier de configuration et connexion à la base de données
 * 
 * @author Legolaswash
 * @version 1.0
 * @since 2025-01-11
 */

declare(strict_types=1);

/**
 * Configuration de la base de données
 */
class DatabaseConfig
{
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'valide_ton_semestre';
    private const DB_USER = 'root';
    private const DB_PASSWORD = ''; // Par défaut vide pour WAMP
    private const DB_CHARSET = 'utf8mb4';
    private const DB_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    private static ?PDO $connection = null;

    /**
     * Obtient une instance de connexion à la base de données (Singleton)
     * 
     * @return PDO Instance de la connexion PDO
     * @throws PDOException En cas d'erreur de connexion
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    self::DB_HOST,
                    self::DB_NAME,
                    self::DB_CHARSET
                );

                self::$connection = new PDO(
                    $dsn,
                    self::DB_USER,
                    self::DB_PASSWORD,
                    self::DB_OPTIONS
                );
            } catch (PDOException $e) {
                error_log('Erreur de connexion à la base de données : ' . $e->getMessage());
                throw new PDOException('Impossible de se connecter à la base de données');
            }
        }

        return self::$connection;
    }

    /**
     * Ferme la connexion à la base de données
     */
    public static function closeConnection(): void
    {
        self::$connection = null;
    }
}

// Maintien de la compatibilité avec l'ancien code
try {
    $pdo = DatabaseConfig::getConnection();
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}
?>
