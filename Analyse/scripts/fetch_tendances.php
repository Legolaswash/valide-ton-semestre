<?php
// Connexion à la base de données
require_once "../../config/ConnexionBD.php";

// Récupération des données de formulaire
$semester = $_GET['semester'];

// Requête SQL pour l'analyse des tendances par matière ou UE
$query_trends_analysis = "SELECT 
                                M.Nom_Matiere AS Matiere,
                                ROUND(AVG(MC.field_Grade), 2) AS Moyenne,
                                M.coefficient AS Coef,
                                COUNT(*) AS Nombre_Calculs
                            FROM 
                                matiere_calcul MC
                            INNER JOIN 
                                resultat_calcul RC ON MC.result_id = RC.id
                            INNER JOIN 
                                matieres M ON MC.field_name = M.Nom_Matiere
                            INNER JOIN 
                                UE ON MC.UE = UE.Nom_UE
                            WHERE 
                                RC.semester = ?
                            GROUP BY 
                                MC.field_name
                            ORDER BY M.coefficient DESC";
try {
    $stmt_trends_analysis = $pdo->prepare($query_trends_analysis);
    $stmt_trends_analysis->execute([$semester]);
    $results_trends_analysis = $stmt_trends_analysis->fetchAll(PDO::FETCH_ASSOC);

    // Affichage des résultats de l'analyse des tendances
    if ($results_trends_analysis) {
        echo '<h3>Résultats de l\'analyse des tendances par matière ou UE :</h3>';
        echo '<table>';
        echo '<tr><th>Matière</th><th>Moyenne</th><th>Coefficient</th><th>Nombre de Calculs</th></tr>';
        foreach ($results_trends_analysis as $row) {
            $matiere = $row['Matiere'];
            $moyenne = $row['Moyenne'];
            $coef = $row['Coef'];
            $nombre_calculs = $row['Nombre_Calculs'];

            // Exclure les lignes avec une moyenne nulle
            if ($moyenne !== null) {
                // Ajouter une classe CSS dynamique en fonction de la moyenne
                $color_class = '';
                if ($moyenne >= 0 && $moyenne < 10) {
                    $color_class = 'red';
                } elseif ($moyenne >= 10 && $moyenne < 15) {
                    $color_class = 'orange';
                } elseif ($moyenne >= 15) {
                    $color_class = 'green';
                }

                // Afficher la ligne avec la classe de couleur correspondante
                echo "<tr class='$color_class'><td>$matiere</td><td>$moyenne</td><td>$coef</td><td>$nombre_calculs</td></tr>";
            }
        }
        echo '</table>';
    } else {
        echo '<p>Aucun résultat trouvé pour les sélections effectuées.</p>';
    }
} catch (Exception $e) {
    error_log("Erreur dans scripts/fetch_tendances.php: " . $e->getMessage());
    echo '<p>Erreur lors de la récupération des données.</p>';
}
?>
