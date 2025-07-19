<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="assets/images/preview-icon.png" type="image/png">
        <link rel="stylesheet" href="assets/css/analyse_style_datavise.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Ajout de Font Awesome pour les icônes -->
        <script src="https://cdn.plot.ly/plotly-2.29.1.min.js"></script>
        <title>Datavisualisation</title>
    </head>
    <body>
        <!-- Barre de navigation -->
        <nav>
            <a href="Presentation.html"><i class="fas fa-home"></i> Presentation</a>
            <a href="../Valide_ton_Semestre.php"><i class="fas fa-project-diagram"></i> Projet: Valide ton Semestre</a>
            <a href="Analyse_forms.php"><i class="fas fa-chart-bar"></i> Analyse Dynamique des Données</a>
        </nav>

        <!-- Section d'en-tête avec image de fond -->
        <div class="container-header" id="home">
            <div class = "text">
                <h1>Datavisualisation des besoins des étudiants</h1>
                <h2>À partir des calculs effectués par les utilisateurs</h2>
                <a href="#distributionGraph" class="scroll-down"> Datavisualisation <i class="fas fa-chevron-down"></i></a>
            </div>
        </div>

        <!-- Section des indicateurs de performance -->
        <div id="container-header">
            <div id="header">
                <?php
                    require "../config/ConnexionBD.php";
                    $query = "SELECT COUNT(*) AS Tot FROM Resultat_Calcul";
                    $result = $pdo->query($query)->fetchAll();
                    echo "<p>Cacluls effectuées : ".$result[0]['Tot']."</p>";
                ?>
            </div>
            <div id="indicators" class="graph"></div>
        </div>

        <!-- ###################### ######### ###################### -->
        <!-- #                                                     # -->
        <!-- #                      GRAPHIQUE                      # -->
        <!-- #                                                     # -->
        <!-- ###################### ######### ###################### -->

        <div class="dashboard-container">
            <div class="graph-container">
                <div id="distributionGraph"></div>
                <div class="legend-container">
                    <div id="legend"></div>
                </div>
            </div>
        </div>

        <div class="graph-wrapper">
            <!-- ###################### ############# ###################### -->
            <!-- #                                                         # -->
            <!-- #                  HISTOGRAMME DES NOTES PAR UE           # -->
            <!-- #                                                         # -->
            <!-- ###################### ############# ###################### -->

            <div class="wrapper-graph-container">
                <div id="histogram-ue"></div>
            </div>

            <!-- ###################### ###################### ###################### -->
            <!-- #                                                                  # -->
            <!-- #              GRAPHIQUE EN SECTEURS - REPARTITION                 # -->
            <!-- #                                                                  # -->
            <!-- ###################### ###################### ###################### -->

            <div class="wrapper-graph-container">
                <div id="pie-chart"></div>
            </div>

            <!-- ###################### ##################### ###################### -->
            <!-- #                                                                 # -->
            <!-- #            GRAPHIQUE EN BOÎTE - DISPERSION DES NOTES            # -->
            <!-- #                                                                 # -->
            <!-- ###################### ##################### ###################### -->

            <div class="wrapper-graph-container">
                <div id="boxplot-semester"></div>
            </div>

        </div>

        <?php
            // ###################### ################## ###################### 
            // #                      REQUETE-INDICATEUR                      # 
            // ###################### ################## ###################### 

            // CALCUL - DATES
/*             $queryDateCalcs = "SELECT CURDATE() AS Date_Actuelle,
                (
                    SELECT COUNT(*)
                    FROM Resultat_Calcul
                    WHERE DATE = CURDATE()
                ) AS Nombre_Calculs_Aujourdhui,
                (
                    SELECT COUNT(*)
                    FROM Resultat_Calcul
                    WHERE DATE = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                ) AS Nombre_Calculs_Hier"; */

            $queryDateCalcs = "SELECT 
                    DATE AS Date_Actuelle,
                    COUNT(*) AS Nombre_Calculs
                FROM 
                    Resultat_Calcul
                WHERE 
                    DATE IN (CURDATE(), DATE_SUB(CURDATE(), INTERVAL 1 DAY))
                GROUP BY 
                    DATE WITH ROLLUP";

            $stmtDateCalcs = $pdo->query($queryDateCalcs);
            $datecalcs = $stmtDateCalcs->fetchAll(PDO::FETCH_ASSOC);

            // Initialisation des compteurs
            $Nombre_Calculs_Aujourdhui = 0;
            $Nombre_Calculs_Hier = 0;

            // Parcours des résultats
            foreach ($datecalcs as $calc) {
                // Vérifie si la date est celle d'aujourd'hui
                if ($calc['Date_Actuelle'] == date('Y-m-d')) {
                    $Nombre_Calculs_Aujourdhui = intval($calc['Nombre_Calculs']);
                }
                // Vérifie si la date est celle d'hier
                elseif ($calc['Date_Actuelle'] == date('Y-m-d', strtotime('-1 day'))) {
                    $Nombre_Calculs_Hier = intval($calc['Nombre_Calculs']);
                }
            }

            $datecalcs['Nombre_Calculs_Aujourdhui'] = $Nombre_Calculs_Aujourdhui;
            $datecalcs['Nombre_Calculs_Hier'] = $Nombre_Calculs_Hier;

            // ###################### ################# ###################### 
            // #                      REQUETE-GRAPHIQUE                      # 
            // ###################### ################# ###################### 

            // Requête SQL pour récupérer les dates et les moyennes des notes
            $query = "SELECT DATE, average, semester FROM Resultat_Calcul";
            $stmt = $pdo->query($query);
            $notesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Conversion des dates en format JavaScript (ISO 8601)
            foreach ($notesData as &$note) {
                $note['DATE'] = date('Y-m-d', strtotime($note['DATE']));
                // Assurer la cohérence des noms de propriétés
                $note['Moyenne'] = $note['average'];
                $note['Semestre'] = $note['semester'];
            }

            // DATA - Calcul
            $queryAvg = "SELECT DATE, ROUND(AVG(average), 2) AS average FROM Resultat_Calcul GROUP BY DATE";
            $stmtAvg = $pdo->query($queryAvg);
            $avgData = $stmtAvg->fetchAll(PDO::FETCH_ASSOC);

            // Conversion pour cohérence
            foreach ($avgData as &$avg) {
                $avg['DATE'] = date('Y-m-d', strtotime($avg['DATE']));
                $avg['Moyenne'] = $avg['average'];
            }

            // AXES
            $dates = array_column($notesData, 'DATE');
            $notes = array_column($notesData, 'average');

            // MOYENNES
            $avgDates = array_column($avgData, 'DATE');
            $avgNotes = array_column($avgData, 'average');

            // LEGENDE
            $querySemesters = "SELECT DISTINCT semester FROM Resultat_Calcul";
            $stmtSemesters = $pdo->query($querySemesters);
            $semesters = $stmtSemesters->fetchAll(PDO::FETCH_COLUMN);

            // LEGENDE - Liste prédéfinie de couleurs
            $predefinedColors = array('#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf');

            // Associez chaque semestre à une couleur de la liste prédéfinie
            $colors = array();
            $colorIndex = 0;
            foreach ($semesters as $semester) {
                $colors[$semester] = $predefinedColors[$colorIndex];
                $colorIndex = ($colorIndex + 1) % count($predefinedColors); // Assurez-vous de boucler sur la liste des couleurs prédéfinies
            }

            // Encodage des données en JSON pour utilisation dans le script JavaScript
            $notesDataJson = json_encode($notesData);
            $avgDataJson = json_encode($avgData);
            $colorsJson = json_encode($colors);

            $datecalcJson = json_encode($datecalcs);
        ?>


        <script> // SCRIPT GRAPHIQUE

            // ********************** ***************** ********************** 
            // *                      SCRIPT-INDICATEUR                      * 
            // ********************** ***************** ********************** 
            // GRAPHIQUE INDICATEURS
            var datecalc_data = <?php echo $datecalcJson; ?>;

            var indicatorData = [
                {
                    domain: { row: 0, column: 0 },
                    title: { text: "Calculs Aujourd'hui", font: { size: 16 } },
                    value: datecalc_data['Nombre_Calculs_Aujourdhui'], 
                    mode: "number+delta",
                    type: "indicator",
                    delta: { position: "top", reference: datecalc_data['Nombre_Calculs_Hier']},
                    number: { font: { size: 32 } }
                }
            ];

            var indicatorLayout = {
                paper_bgcolor: "rgba(0,0,0,0)",
                plot_bgcolor: "rgba(0,0,0,0)",
                width: 600,
                height: 200,
                margin: { t: 20, b: 20, l: 20, r: 20 },
                font: { family: "Arial, sans-serif" }
            };

            var indicatorConfig = {
                displayModeBar: false,
                responsive: true
            };

            Plotly.newPlot('indicators', indicatorData, indicatorLayout, indicatorConfig);

            // ********************** **************** ********************** 
            // *                      SCRIPT-GRAPHIQUE                      * 
            // ********************** **************** ********************** 
            
            // GRAPHIQUE POINTS
            // Récupération des données JSON à partir de PHP
            var notesData = <?php echo $notesDataJson; ?>;
            var avgData = <?php echo $avgDataJson; ?>;
            var colors = <?php echo $colorsJson; ?>;

            // Génération de la légende HTML
            var legendHtml = '';
            for (var semester in colors) {
                legendHtml += '<div><span style="display: inline-block; width: 12px; height: 12px; background-color: ' + colors[semester] + '; border-radius: 2px;"></span> ' + semester + '</div>';
            }
            document.getElementById('legend').innerHTML = legendHtml;

            // ------ Extraction des dates et des moyennes pour Plotly.js
            var dates = notesData.map(function(item) {
                return item.DATE;
            });

            var notes = notesData.map(function(item) {
                return parseFloat(item.average || item.Moyenne) || 0;
            });

            var semestres = notesData.map(function(item) {
                return item.semester || item.Semestre;
            });

            // ------ Extraction des moyennes des moyennes pour Plotly.js
            var avgDates = avgData.map(function(item) {
                return item.DATE;
            });

            var avgNotes = avgData.map(function(item) {
                return parseFloat(item.average || item.Moyenne) || 0;
            });

            // Création d'un graphique interactif avec Plotly.js
            var data = [{
                x: dates,
                y: notes,
                mode: 'markers',
                type: 'scatter',
                text: notesData.map(function(item) {
                    var moyenne = parseFloat(item.average || item.Moyenne) || 0;
                    var semestre = item.semester || item.Semestre;
                    return 'Moyenne : ' + moyenne.toFixed(2) + '<br>Date : ' + item.DATE + '<br>Semestre : ' + semestre;
                }),
                hoverinfo: 'text',
                marker: {
                    color: notesData.map(function(item) {
                        return colors[item.semester || item.Semestre] || 'rgba(31, 119, 180, 0.7)';
                    }),
                    size: 8,
                    line: { width: 1, color: 'white' }
                },
                name: "Moyennes individuelles",
                showlegend: true
            },
            {
                x: avgDates,
                y: avgNotes,
                mode: 'markers+lines',
                type: 'scatter',
                text: avgData.map(function(item) {
                    var moyenne = parseFloat(item.average || item.Moyenne) || 0;
                    return 'Moyenne du jour : ' + moyenne.toFixed(2) + '<br>Date : ' + item.DATE;
                }),
                hoverinfo: 'text',
                marker: {
                    color: '#ff4444',
                    size: 10,
                    symbol: 'diamond',
                    line: { color: 'white', width: 2 }
                },
                line: {
                    color: '#ff4444',
                    width: 2,
                    dash: 'dot'
                },
                name: 'Moyenne journalière',
                showlegend: true
            }];

            var layout = {
                title: {
                    text: 'Distribution des Moyennes calculées par jour',
                    font: { size: 18, family: "Arial, sans-serif" }
                },
                xaxis: {
                    title: { text: 'Date', font: { size: 14 } },
                    type: 'date',
                    tickformat: '%d/%m/%Y'
                },
                yaxis: {
                    title: { text: 'Moyenne', font: { size: 14 } },
                    range: [0, 20]
                },
                hovermode: 'closest',
                plot_bgcolor: 'rgba(240,240,240,0.5)',
                paper_bgcolor: 'white',
                margin: { t: 60, b: 60, l: 60, r: 60 },
                legend: {
                    x: 0.02,
                    y: 0.98,
                    bgcolor: 'rgba(255,255,255,0.8)',
                    bordercolor: '#ddd',
                    borderwidth: 1
                }
            };

            var config = {
                displayModeBar: false,
                responsive: true
            };

            Plotly.newPlot('distributionGraph', data, layout, config);
        </script>
        <!--
        // Création d'un boxplot de la distribution des notes
        // Vous pouvez utiliser une bibliothèque JavaScript dédiée pour les boxplots comme Plotly.js
        // Ou vous pouvez calculer les quartiles et les médianes en PHP et les transmettre à Plotly.js 
        -->

        <!-- ###################### ########################## ###################### -->
        <!-- #                                                                      # -->
        <!-- #                      TABLEAU DE DONNNEES - FIXE                      # -->
        <!-- #                                                                      # -->
        <!-- ###################### ########################## ###################### -->

        <div class="dashboard-container-table">
            <div class="table-container">
                <table class="table" border="1">
                    <caption>Statistiques par Semestre</capton>
                    <thead>
                        <tr>
                            <th scope="col">Matière</th>
                            <th scope="col">Min</th>
                            <th scope="col">Max</th>
                            <th scope="col">Moyenne</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $query = "SELECT 
                                        semester,
                                        MIN(average) AS Minimum_note,
                                        MAX(average) AS Maximum_note,
                                        ROUND(AVG(average), 2) AS Moyenne_note
                                    FROM 
                                        Resultat_Calcul
                                    GROUP BY 
                                        semester WITH ROLLUP
                                    HAVING 
                                        semester IS NOT NULL
                                    ";
                            $result = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                                echo "<tr>";
                                    echo "<td>".$row['semester']."</td>";
                                    echo "<td>".$row['Minimum_note']."</td>";
                                    echo "<td>".$row['Maximum_note']."</td>";
                                    echo "<td>".$row['Moyenne_note']."</td>";
                                echo "</tr>";
                            }
                            ?>
                    </tbody>
                </table>
            </div>
            <div class="table-container">
                <table class="table" border="1">
                    <caption>Statistiques par Semestre et UEs</capton>
                    <thead>
                        <tr>
                            <th scope="col">Semestre</th>
                            <th scope="col">UE</th>
                            <th scope="col">Moyenne</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $query = "SELECT semester, UE.Nom_UE, ROUND(AVG(Resultat_Calcul.average), 2) AS Moyenne_note
                                        FROM Resultat_Calcul
                                        INNER JOIN UE ON Resultat_Calcul.ue = UE.Nom_UE
                                        GROUP BY Resultat_Calcul.ue";
                            $result = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                                echo "<tr>";
                                    echo "<td>".$row['semester']."</td>";
                                    echo "<td>".$row['Nom_UE']."</td>";
                                    echo "<td>".$row['Moyenne_note']."</td>";
                                echo "</tr>";
                            }
                            ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php
            // ---------------- BARCHART
            // Requête SQL pour récupérer les notes par UE
            $queryHistogramUE = "SELECT UE.nom_UE AS UE, ROUND(AVG(Resultat_Calcul.average), 2) AS Moyenne
                                FROM Resultat_Calcul
                                INNER JOIN UE ON Resultat_Calcul.ue = UE.Nom_UE
                                GROUP BY Resultat_Calcul.ue
                                ORDER BY UE.Nom_UE DESC";
            $stmtHistogramUE = $pdo->query($queryHistogramUE);
            $histogramUEData = $stmtHistogramUE->fetchAll(PDO::FETCH_ASSOC);

            // Extraction des données pour Plotly.js
            $ues = array_column($histogramUEData, 'UE');
            $moyennesUE = array_column($histogramUEData, 'Moyenne');

            // Encodage des données en JSON pour utilisation dans le script JavaScript
            $histogramUEDataJson = json_encode($histogramUEData);
            
            // ---------------- SECTEUR
            // Requête SQL pour récupérer la répartition du nombre de calculs par semestre
            $queryPieChart = "SELECT 
                                    semester,
                                    COUNT(*) AS Nombre_Calculs
                                FROM 
                                    Resultat_Calcul
                                GROUP BY 
                                    semester WITH ROLLUP
                                HAVING semester IS NOT NULL";
            $stmtPieChart = $pdo->query($queryPieChart);
            $pieChartData = $stmtPieChart->fetchAll(PDO::FETCH_ASSOC);

            // Ajout de propriétés cohérentes
            foreach ($pieChartData as &$pie) {
                $pie['Semestre'] = $pie['semester'];
            }

            // Encodage des données en JSON pour utilisation dans le script JavaScript
            $pieChartDataJson = json_encode($pieChartData);

            // ---------------- BOXPLOT
            // Récupération des données pour le boxplot
            $queryBoxplot = "SELECT semester, average FROM Resultat_Calcul";
            $stmtBoxplot = $pdo->query($queryBoxplot);
            $boxplotData = $stmtBoxplot->fetchAll(PDO::FETCH_ASSOC);

            // Ajout de propriétés cohérentes
            foreach ($boxplotData as &$box) {
                $box['Semestre'] = $box['semester'];
                $box['Moyenne'] = $box['average'];
            }

            // Encodage des données en JSON
            $boxplotDataJson = json_encode($boxplotData);
        ?>

        <script>
            // ---------------- BARCHART
            // Données JSON pour l'histogramme par UE
            var histogramUEData = <?php echo $histogramUEDataJson; ?>;

            // Extraction des UE et des moyennes
            var uesUE = histogramUEData.map(function(item) {
                return item.UE;
            });

            var moyennesUE = histogramUEData.map(function(item) {
                return parseFloat(item.Moyenne) || 0;
            });

            // Création de l'histogramme avec Plotly.js
            var dataHistogramUE = [{
                x: moyennesUE,
                y: uesUE,
                type: 'bar',
                orientation: 'h',
                marker: {
                    color: 'rgba(55, 128, 191, 0.7)',
                    line: { color: 'rgba(55, 128, 191, 1)', width: 1 }
                },
                text: moyennesUE.map(function(val) {
                    return (parseFloat(val) || 0).toFixed(2);
                }),
                textposition: 'outside'
            }];

            var layoutHistogramUE = {
                title: {
                    text: 'Moyennes par UE',
                    font: { size: 16, family: "Arial, sans-serif" }
                },
                xaxis: {
                    title: { text: 'Moyenne', font: { size: 12 } },
                    range: [0, Math.max(...moyennesUE) * 1.1]
                },
                yaxis: {
                    title: { text: 'UE', font: { size: 12 } }
                },
                margin: { t: 50, b: 50, l: 150, r: 50 },
                plot_bgcolor: 'rgba(240,240,240,0.5)',
                paper_bgcolor: 'white'
            };

            var configHistogram = {
                displayModeBar: false,
                responsive: true
            };

            Plotly.newPlot('histogram-ue', dataHistogramUE, layoutHistogramUE, configHistogram);

            // ---------------- SECTEUR
            // Données JSON pour le graphique en secteurs
            var pieChartData = <?php echo $pieChartDataJson; ?>;

            // Extraction des données pour Plotly.js
            var semestres = pieChartData.map(function(item) {
                return item.semester || item.Semestre;
            });

            var nombreCalculs = pieChartData.map(function(item) {
                return parseInt(item.Nombre_Calculs) || 0;
            });

            // Création du graphique en secteurs avec Plotly.js
            var dataPieChart = [{
                values: nombreCalculs,
                labels: semestres,
                type: 'pie',
                hole: 0.3,
                textinfo: 'label+percent+value',
                textposition: 'outside',
                marker: {
                    line: { color: 'white', width: 2 }
                }
            }];

            var layoutPieChart = {
                title: {
                    text: 'Répartition des calculs par semestre',
                    font: { size: 16, family: "Arial, sans-serif" }
                },
                margin: { t: 50, b: 50, l: 50, r: 50 },
                paper_bgcolor: 'white',
                showlegend: true,
                legend: {
                    orientation: "h",
                    x: 0.5,
                    xanchor: 'center',
                    y: -0.1
                }
            };

            var configPie = {
                displayModeBar: false,
                responsive: true
            };

            Plotly.newPlot('pie-chart', dataPieChart, layoutPieChart, configPie);

            // ---------------- BOXPLOT
            // Données JSON pour le boxplot
            var boxplotData = <?php echo $boxplotDataJson; ?>;

            // Grouper les données par semestre
            var semestresUniques = [...new Set(boxplotData.map(item => item.semester || item.Semestre))];
            var dataBoxplot = [];

            semestresUniques.forEach(function(sem) {
                var moyennesSemestre = boxplotData
                    .filter(item => (item.semester || item.Semestre) === sem)
                    .map(item => parseFloat(item.average || item.Moyenne) || 0);
                
                dataBoxplot.push({
                    y: moyennesSemestre,
                    name: sem,
                    type: 'box',
                    boxpoints: 'outliers',
                    marker: { size: 4 },
                    line: { width: 2 }
                });
            });

            var layoutBoxplot = {
                title: {
                    text: 'Distribution des moyennes par semestre',
                    font: { size: 16, family: "Arial, sans-serif" }
                },
                yaxis: {
                    title: { text: 'Moyenne', font: { size: 12 } },
                    range: [0, 20]
                },
                xaxis: {
                    title: { text: 'Semestre', font: { size: 12 } }
                },
                margin: { t: 50, b: 50, l: 60, r: 50 },
                plot_bgcolor: 'rgba(240,240,240,0.5)',
                paper_bgcolor: 'white',
                showlegend: false
            };

            var configBoxplot = {
                displayModeBar: false,
                responsive: true
            };

            Plotly.newPlot('boxplot-semester', dataBoxplot, layoutBoxplot, configBoxplot);

            // Ajuster la taille des graphiques lors du redimensionnement de la fenêtre
            window.addEventListener('resize', function() {
                Plotly.Plots.resize('distributionGraph');
                Plotly.Plots.resize('histogram-ue');
                Plotly.Plots.resize('pie-chart');
                Plotly.Plots.resize('boxplot-semester');
                Plotly.Plots.resize('indicators');
            });
        </script>
        
        <!-- Styles pour le footer uniformisé -->
        <style>
            .footer-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 15px;
                background-color: #f5f5f5;
                padding: 20px;
                border-top: 1px solid #ddd;
                max-width: 1200px;
                margin: 0 auto;
            }
            
            .footer-content a:hover {
                text-decoration: underline;
            }

            .nav-link {
                color: #4caf50;
                text-decoration: none;
                padding: 8px 15px;
                border-radius: 5px;
                background-color: rgba(76, 175, 80, 0.1);
                transition: background-color 0.3s ease;
                font-size: 14px;
            }
            
            .nav-link:hover {
                background-color: rgba(76, 175, 80, 0.2);
                text-decoration: none;
            }
            
            .report-button {
                background-color: #ff9800;
                color: white;
                border: none;
                padding: 8px 15px;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                transition: background-color 0.3s ease;
            }
            
            .report-button:hover {
                background-color: #f57c00;
            }
            
            .credit {
                font-size: 10px;
                color: #666;
            }
            
            @media (max-width: 768px) {
                .footer-content {
                    flex-direction: column;
                    text-align: center;
                    gap: 10px;
                }
                
                .nav-link, .report-button {
                    font-size: 12px;
                    padding: 6px 12px;
                }
            }
        </style>
        
        <footer>
            <div class="footer-content">
                <span>Copyright &copy; Legolas <?php echo date("Y"); ?></span>
                <a href="Presentation.html" class="nav-link">📊 Présentation</a>
                <a href="Analyse_forms.php" class="nav-link">📈 Analyse dynamique</a>
                <a href="../Valide_ton_Semestre.php" class="nav-link">🧮 Calculateur de notes</a>
                <a href="../tools/check.html" class="nav-link">🔧 Tests système</a>
            </div>
        </footer>
    </body>
</html>
