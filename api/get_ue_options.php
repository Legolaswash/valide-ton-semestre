<?php
/**
 * API pour récupérer les unités d'enseignement (UE) par semestre
 * 
 * Ce script récupère toutes les UE associées à un semestre donné
 * 
 * @author Legolaswash
 * @version 1.0
 * @since 2025-01-11
 */

declare(strict_types=1);

// Désactivation de l'affichage des erreurs pour éviter la corruption du JSON
ini_set('display_errors', '0');
error_reporting(0);

require_once "../config/ConnexionBD.php";

header('Content-Type: application/json; charset=utf-8');

/**
 * Classe pour gérer les UE par semestre
 */
class UeOptionsService
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    /**
     * Récupère toutes les UE pour un semestre donné
     * 
     * @param string $semesterId ID du semestre
     * @return array Liste des UE
     * @throws Exception En cas d'erreur de base de données
     */
    public function getUesBySemester(string $semesterId): array
    {
        try {
            $query = "SELECT DISTINCT UE FROM ues_choix WHERE semester_id = ? ORDER BY UE";
            $statement = $this->database->prepare($query);
            $statement->execute([$semesterId]);
            
            return $statement->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des UE : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des UE");
        }
    }
}

// Traitement de la requête
try {
    if (!isset($_GET['semester']) || empty($_GET['semester'])) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Le paramètre semester est requis',
            'message' => 'Veuillez fournir un ID de semestre valide'
        ]);
        exit;
    }

    $semesterId = filter_var($_GET['semester'], FILTER_SANITIZE_STRING);
    
    $ueService = new UeOptionsService(DatabaseConfig::getConnection());
    $ueList = $ueService->getUesBySemester($semesterId);
    
    echo json_encode($ueList, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur interne du serveur',
        'message' => 'Une erreur est survenue lors du traitement de votre demande'
    ]);
    error_log("Erreur dans get_ue_options.php : " . $e->getMessage());
}
?>
