<?php
// Connexion à la base de données
require_once "../../config/ConnexionBD.php";

// Récupération des données de formulaire
$semestre1 = $_GET['semestre1'];
$semestre2 = $_GET['semestre2'];

// Vérifiez si les deux semestres ont été sélectionnés
if ($semestre1 && $semestre2) {
    // Requête SQL pour récupérer les performances moyennes pour chaque semestre
    $query = "SELECT semester, ROUND(AVG(average), 2) AS average FROM Resultat_Calcul WHERE semester IN (?, ?) GROUP BY semester";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$semestre1, $semestre2]);
    $performances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifiez si des résultats ont été trouvés
    if ($performances) {
        echo '<div class="comparison-box">';
        echo '<div id="performanceComparison" class="comparison-result">';
        echo '<h3>Résultats de la comparaison des performances par semestre :</h3>';

        // Stockage des performances dans un tableau associatif pour comparer
        $performance_values = [];
        foreach ($performances as $performance) {
            $performance_values[$performance['semester']] = $performance['average'];
        }

        // Affichage des résultats de la comparaison
        foreach ($performances as $performance) {
            $semestre = $performance['semester'];
            $moyenne = $performance['average'];
            $class = '';
            if ($moyenne === max($performance_values)) {
                $class = 'higher-result';
            } elseif ($moyenne === min($performance_values)) {
                $class = 'lower-result';
            }
            echo "<p class='$class'>semester : $semestre<br>Moyenne : $moyenne</p>";
        }
        echo '</div>';
        echo '</div>';

        // Calcul de la différence de performances moyennes
        if (count($performances) >= 2) {
            $diff = $performances[0]['average'] - $performances[1]['average'];
            echo "<p>Différence de performances moyennes : $diff</p>";
        } else {
            echo "<p>Impossible de calculer la différence : données insuffisantes.</p>";
        }
    } else {
        echo "<p>Aucun résultat trouvé pour les semestres sélectionnés.</p>";
    }
} else {
    echo "<p>Veuillez sélectionner deux semestres.</p>";
}
?>
