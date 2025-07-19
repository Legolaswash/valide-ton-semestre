<?php
/**
 * API pour l'insertion des résultats de calculs
 * 
 * Ce script insère les résultats de calculs de notes dans la base de données
 * 
 * @author Legolaswash
 * @version 1.0
 * @since 2025-01-11
 */

declare(strict_types=1);

require_once "../config/ConnexionBD.php";

header('Content-Type: application/json; charset=utf-8');

/**
 * Classe pour gérer l'insertion des résultats
 */
class ResultInsertionService
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    /**
     * Récupère l'ID d'une UE par son nom
     * 
     * @param string $ueName Nom de l'UE
     * @return int|null ID de l'UE ou null si non trouvée
     */
    private function getUeIdByName(string $ueName): ?int
    {
        try {
            $query = "SELECT id FROM ue WHERE Nom_UE = ?";
            $statement = $this->database->prepare($query);
            $statement->execute([$ueName]);
            
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['id'] : null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'ID UE : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Insère un résultat de calcul dans la base de données
     * 
     * @param array $resultData Données du résultat
     * @return int ID du résultat inséré
     * @throws Exception En cas d'erreur d'insertion
     */
    public function insertCalculationResult(array $resultData): int
    {
        try {
            $this->database->beginTransaction();

            // Validation et nettoyage des données
            $semester = $resultData['semester'] ?? '';
            $goal = isset($resultData['goal']) ? (float)$resultData['goal'] : null;
            $average = isset($resultData['average']) ? (float)$resultData['average'] : null;
            $needed = ($resultData['needed'] === 'null' || $resultData['needed'] === null) ? null : (float)$resultData['needed'];
            $ueName = $resultData['ue'] ?? '';

            // Validation des données requises
            if (empty($semester) || empty($ueName) || $goal === null) {
                throw new Exception("Données obligatoires manquantes");
            }

            $query = "INSERT INTO Resultat_Calcul (semester, goal, average, needed, ue) VALUES (?, ?, ?, ?, ?)";
            $statement = $this->database->prepare($query);
            $statement->execute([$semester, $goal, $average, $needed, $ueName]);

            $resultId = (int)$this->database->lastInsertId();
            
            $this->database->commit();
            
            error_log("Résultat inséré avec succès - ID: $resultId");
            
            return $resultId;
            
        } catch (PDOException $e) {
            $this->database->rollback();
            error_log("Erreur lors de l'insertion du résultat : " . $e->getMessage());
            throw new Exception("Erreur lors de l'insertion du résultat");
        }
    }
}

// Traitement de la requête
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée', 'message' => 'Seule la méthode POST est acceptée']);
        exit;
    }

    // Validation des paramètres requis
    $requiredFields = ['semester', 'goal', 'average', 'needed', 'ue'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Champs manquants',
            'message' => 'Les champs suivants sont requis : ' . implode(', ', $missingFields)
        ]);
        exit;
    }

    $resultService = new ResultInsertionService(DatabaseConfig::getConnection());
    $resultId = $resultService->insertCalculationResult($_POST);
    
    echo $resultId; // Maintien de la compatibilité avec le frontend existant
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur interne du serveur',
        'message' => $e->getMessage()
    ]);
    error_log("Erreur dans insert_result.php : " . $e->getMessage());
}
?>
