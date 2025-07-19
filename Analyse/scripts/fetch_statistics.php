<?php
/**
 * API pour récupérer les statistiques par semestre et UE
 * 
 * Ce script calcule et retourne les statistiques générales
 * pour un semestre et une UE donnés
 * 
 * @author Legolaswash
 * @version 1.0
 * @since 2025-01-11
 */

require_once "../../config/ConnexionBD.php";

$semester = $_GET['semester'];
$ue = $_GET['ue'];

// Requête SQL - Statistiques générales
    $query = "SELECT COUNT(*) AS NbRep, ROUND(AVG(average), 2) AS Moy
            FROM Resultat_Calcul AS RC
            INNER JOIN UE ON RC.ue = UE.Nom_UE
            WHERE semester = ? AND UE.Nom_UE = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$semester, $ue]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification des résultats
    if ($result) {
        $nbRep = $result['NbRep'];
        $moy = $result['Moy'];
        echo "Nombre de réponses : $nbRep<br>";
        echo "Moyenne des moyennes : $moy<br>";
    } else {
        echo "Aucun résultat trouvé.";
    }
?>
