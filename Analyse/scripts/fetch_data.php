<?php
/**
 * API pour récupérer les données de calcul
 * 
 * Ce script récupère les données de moyennes avec leurs dates
 * pour la visualisation des tendances
 * 
 * @author Legolaswash
 * @version 1.0
 * @since 2025-01-11
 */

require_once "../../config/ConnexionBD.php";

$query = "SELECT DATE, Moyenne FROM Resultat_Calcul WHERE Moyenne IS NOT NULL ORDER BY DATE";
$stmt = $pdo->prepare($query);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
