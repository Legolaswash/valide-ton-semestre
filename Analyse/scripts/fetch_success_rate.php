<?php
    // Connexion à la base de données
    require_once "../../config/ConnexionBD.php";

    // Récupération des données de formulaire
    $semester = $_GET['semester'];

    // Requête SQL pour le taux de réussite par matière
    $query_success_rate = "SELECT 
                                RC.semester AS Semestre,
                                SUM(CASE WHEN RC.average >= RC.goal AND RC.needed IS NULL THEN 1 ELSE 0 END) AS Reussi,
                                COUNT(*) AS Total,
                                ROUND((SUM(CASE WHEN RC.average >= RC.goal AND RC.needed IS NULL THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) AS Taux_Reussite
                            FROM Resultat_Calcul RC
                            INNER JOIN UE U ON RC.ue = U.Nom_UE
                            WHERE RC.semester = ?
                            GROUP BY RC.semester";
    $stmt_success_rate = $pdo->prepare($query_success_rate);
    $stmt_success_rate->execute([$semester]);
    $results_success_rate = $stmt_success_rate->fetchAll(PDO::FETCH_ASSOC);

    // Vérification des résultats pour le taux de réussite par matière
    if ($results_success_rate) {
        echo '<div class="statistic">';
        echo "<p>Taux de réussite du semestre (calculé pour les saisies complète) :</p>";
        echo '<ul>';
        foreach ($results_success_rate as $row) {
            $Semestre = $row['Semestre'];
            $reussi = $row['Reussi'];
            $total = $row['Total'];
            $taux_reussite = $row['Taux_Reussite'];
            echo "<p>$Semestre : $taux_reussite%</p>";
        }
        echo '</ul>';
        echo '</div>';
    } else {
        echo '<div class="statistic">Aucun résultat trouvé pour le taux de réussite par matière.</div>';
    }
?>