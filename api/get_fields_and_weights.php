<?php
/**
 * API pour récupérer les champs et coefficients d'une UE par semestre
 * 
 * Ce script récupère les matières et leurs coefficients pour une UE et un semestre donnés
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
 * Classe pour gérer les champs et coefficients
 */
class FieldsAndWeightsService
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    /**
     * Récupère les champs et coefficients pour un semestre et une UE donnés
     * 
     * @param string $semesterId ID du semestre
     * @param string $ueCode Code de l'UE
     * @return array Liste des champs avec leurs coefficients
     * @throws Exception En cas d'erreur de base de données
     */
    public function getFieldsAndWeightsByUe(string $semesterId, string $ueCode): array
    {
        try {
            $query = "SELECT DISTINCT name_field, coefficient_field 
                     FROM ues_choix 
                     WHERE semester_id = ? AND UE = ?
                     ORDER BY name_field";
            
            $statement = $this->database->prepare($query);
            $statement->execute([$semesterId, $ueCode]);
            
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des champs et coefficients : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des données");
        }
    }
}

// Traitement de la requête
try {
    // Validation des paramètres d'entrée
    $requiredParams = ['semester', 'ue'];
    $missingParams = [];
    
    foreach ($requiredParams as $param) {
        if (!isset($_GET[$param]) || empty($_GET[$param])) {
            $missingParams[] = $param;
        }
    }
    
    if (!empty($missingParams)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Paramètres manquants',
            'message' => 'Les paramètres suivants sont requis : ' . implode(', ', $missingParams),
            'required_params' => $requiredParams
        ]);
        exit;
    }

    $semesterId = filter_var($_GET['semester'], FILTER_SANITIZE_STRING);
    $ueCode = filter_var($_GET['ue'], FILTER_SANITIZE_STRING);
    
    $fieldsService = new FieldsAndWeightsService(DatabaseConfig::getConnection());
    $fieldsData = $fieldsService->getFieldsAndWeightsByUe($semesterId, $ueCode);
    
    echo json_encode($fieldsData, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur interne du serveur',
        'message' => 'Une erreur est survenue lors du traitement de votre demande'
    ]);
    error_log("Erreur dans get_fields_and_weights.php : " . $e->getMessage());
}
?>

