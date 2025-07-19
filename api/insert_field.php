<?php
/**
 * API pour l'insertion des données de matières
 * 
 * Ce script insère les données des matières (notes et informations) dans la base de données
 * 
 * @author Legolaswash
 * @version 1.0
 * @since 2025-01-11
 */

declare(strict_types=1);

require_once "../config/ConnexionBD.php";

header('Content-Type: application/json; charset=utf-8');

/**
 * Classe pour gérer l'insertion des données de matières
 */
class FieldInsertionService
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
     * Récupère l'ID d'une matière par son nom et l'ID de l'UE
     * 
     * @param string $fieldName Nom de la matière
     * @param int $ueId ID de l'UE
     * @return int|null ID de la matière ou null si non trouvée
     */
    private function getFieldIdByNameAndUe(string $fieldName, int $ueId): ?int
    {
        try {
            $query = "SELECT id FROM matieres WHERE Nom_Matiere = ? AND UE_id = ?";
            $statement = $this->database->prepare($query);
            $statement->execute([$fieldName, $ueId]);
            
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['id'] : null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'ID matière : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Insère les données d'une matière dans la table de calculs
     * 
     * @param array $fieldData Données de la matière
     * @return bool Succès de l'insertion
     * @throws Exception En cas d'erreur d'insertion
     */
    public function insertFieldCalculation(array $fieldData): bool
    {
        try {
            $this->database->beginTransaction();

            // Validation et nettoyage des données
            $resultId = isset($fieldData['resultId']) ? (int)$fieldData['resultId'] : null;
            $fieldName = $fieldData['fieldName'] ?? '';
            $fieldGrade = ($fieldData['fieldGrade'] === 'null' || $fieldData['fieldGrade'] === null) ? null : (float)$fieldData['fieldGrade'];
            $ueName = $fieldData['UE'] ?? '';

            // Validation des données requises
            if ($resultId === null || empty($fieldName) || empty($ueName)) {
                throw new Exception("Données obligatoires manquantes");
            }

            // Récupération des IDs nécessaires
            $ueId = $this->getUeIdByName($ueName);
            if ($ueId === null) {
                throw new Exception("UE non trouvée : $ueName");
            }

            $fieldId = $this->getFieldIdByNameAndUe($fieldName, $ueId);
            // Note: fieldId peut être null, ce qui est acceptable selon la logique actuelle

            // Insertion dans la table matiere_calcul
            $query = "INSERT INTO matiere_calcul (result_id, field_name, field_Grade, UE) VALUES (?, ?, ?, ?)";
            $statement = $this->database->prepare($query);
            $statement->execute([$resultId, $fieldName, $fieldGrade, $ueName]);

            $this->database->commit();
            
            return true;
            
        } catch (PDOException $e) {
            $this->database->rollback();
            error_log("Erreur lors de l'insertion de la matière : " . $e->getMessage());
            throw new Exception("Erreur lors de l'insertion de la matière");
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
    $requiredFields = ['resultId', 'fieldName', 'UE'];
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

    $fieldService = new FieldInsertionService(DatabaseConfig::getConnection());
    $success = $fieldService->insertFieldCalculation($_POST);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Données insérées avec succès']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur interne du serveur',
        'message' => $e->getMessage()
    ]);
    error_log("Erreur dans insert_field.php : " . $e->getMessage());
}
?>
