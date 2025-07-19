<?php
/**
 * API pour récupérer les UE par semestre dans le module d'analyse
 * 
 * Ce script récupère toutes les UE associées à un semestre donné
 * pour le module d'analyse des données
 * 
 * @author Legolaswash
 * @version 1.0
 * @since 2025-01-11
 */

header('Content-Type: application/json; charset=utf-8');
require_once "../../config/ConnexionBD.php";

try {
    if (isset($_GET['semester'])) {
        $semester = $_GET['semester'];

        $query = "SELECT DISTINCT UE.Nom_UE 
                FROM Resultat_Calcul AS RC
                INNER JOIN UE ON RC.ue = UE.Nom_UE
                WHERE RC.semester = ?
                ORDER BY UE.Nom_UE";
                
        $stmt = $pdo->prepare($query);
        $stmt->execute([$semester]);
        $ues = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode($ues, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([]);
    }
} catch (Exception $e) {
    error_log("Erreur dans get_ue_options_Analyse.php: " . $e->getMessage());
    echo json_encode([]);
}
?>
