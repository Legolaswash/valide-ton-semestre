<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="assets/images/analytics-icon.png" type="image/png">
        <link rel="stylesheet" href="assets/css/analyse_style_form.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <title>Résumé Dynamique</title>
    </head>
    <body>
        <!-- Barre de navigation -->
        <nav>
        <a href="Presentation.html"><i class="fas fa-home"></i> Presentation</a>
            <a href="../Valide_ton_Semestre.php"><i class="fas fa-project-diagram"></i> Projet: Valide ton Semestre</a>
            <a href="Analyse_datavise.php"><i class="fas fa-chart-line"></i> Datavisualisation</a>
        </nav>

        <!-- Section d'en-tête avec image de fond -->
        <div class="container-header" id="home">
            <div class = "text">
            <h1>Résumé dynamique des besoins des étudiants</h1>
            <h2>À partir des calculs effectués par les utilisateurs</h2>
                <a href="#haut_de_page" class="scroll-down"> Résumé <i class="fas fa-chevron-down"></i></a>
            </div>
        </div>

        <!-- Section de sélection et d'analyse -->
        <?php
            require "../config/ConnexionBD.php";
            
            /**
             * Récupère tous les semestres disponibles dans la base de données
             */
            function getAllSemesters($pdo) {
                $query = "SELECT 
                            semester
                        FROM 
                            Resultat_Calcul
                        GROUP BY 
                            semester WITH ROLLUP
                        HAVING
                            semester IS NOT NULL";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Function to get all UEs for a given semester from the database
            function getUEsForSemester($pdo, $semester) {
                $query = "SELECT DISTINCT ue FROM Resultat_Calcul WHERE semester = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$semester]);
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            // Get all semesters
            $semesters = getAllSemesters($pdo);
        ?>
        <div id="haut_de_page"></div>

        <div class="stats-containers">
            <div class="container container-1">
                <div class="select-container">
                    <label for="semesterSelect">Selectionner Semestre : </label>
                    <select id="semesterSelect" onchange="changeUEFields()">
                        <option value="">Selectionner Semestre</option>
                        <?php
                            foreach ($semesters as $semester) {
                                echo '<option value="' . $semester['semester'] . '">' . $semester['semester'] . '</option>';
                            }
                        ?>
                    </select>
                </div>
                <div id="successrate" class="stats-container"></div>
            </div>
            <!-- UE dropdown menu -->
            <div class="container container-2">
                <div class="select-container">
                    <label for="ueSelect">Selectionner UE:</label>
                    <select id="ueSelect" onchange=updateStatistics()>
                    </select>
                </div>
                <div id="statistics" class="stats-container"></div>
            </div>
        </div>

        <!-- ################# ##################################### ################## -->
        <!-- #                                                                        # -->
        <!-- #                 FORMULAIRE STATS COMPARAISON SEMESTRE                  # -->
        <!-- #                                                                        # -->
        <!-- ################# ##################################### ################## -->

        <div class="comparison-container">
            <form method="post" action="scripts/comparaison_performance.php">
                <div class="select-container-comparison">  
                    <label for="semestre1">Selectionner Semestre 1 : </label>
                    <select name="semestre1" id="semestre1" onchange="updatePerformanceComparison()">
                        <option value="">Selectionner Semestre</option>
                        <?php
                            foreach ($semesters as $semester) {
                                echo '<option value="' . $semester['semester'] . '">' . $semester['semester'] . '</option>';
                            }
                        ?>
                    </select>
                </div>
                <div class="select-container-comparison">
                    <label for="semestre2">Selectionner Semestre 2 : </label>
                    <select name="semestre2" id="semestre2" onchange="updatePerformanceComparison()">
                        <option value="">Selectionner Semestre</option>
                        <?php
                            foreach ($semesters as $semester) {
                                echo '<option value="' . $semester['semester'] . '">' . $semester['semester'] . '</option>';
                            }
                        ?>
                    </select>
                </div>
            </form>
            <div class="comparison-box">
                <div id="performanceComparison" class="comparison-result">
                    <!-- Le résultat de la comparaison sera affiché ici -->
                </div>
            </div>
        </div>

        <!-- ################# ################################ ################## -->
        <!-- #                                                                   # -->
        <!-- #                 FORMULAIRE MATIERES PAR SEMESTRE                  # -->
        <!-- #                                                                   # -->
        <!-- ################# ################################ ################## -->

        <div class="container-analysis">
        <h3>Analyse des tendances par matière ou UE</h3>
        <form>
            <div class="select-container-analysis">  
                <label for="matiere_semesterSelect">Sélectionner le semestre :</label>
                <select name="semester" id="matiere_semesterSelect" onchange="updateTrendsAnalysis()">
                    <?php
                        $first = true; // Variable pour suivre la première itération
                        foreach ($semesters as $semester) {
                            // Si c'est la première itération, ajouter l'attribut "selected"
                            $selected = $first ? 'selected' : '';
                            echo '<option value="' . $semester['semester'] . '"' . $selected . '>' . $semester['semester'] . '</option>';
                            $first = false; // Mettre la variable à faux après la première itération
                        }
                    ?>
                </select>
            </div>
        </form>
        <div id="trendsAnalysis" class="analysis-result">
            <!-- Le résultat de l'analyse sera affiché ici -->
        </div>
    </div>
    </body>
    <script>
        // ******* **************************************** ******* 
        // *       SCRIPT-CHANGEMENT D'UEs POUR UN SEMESTRE       * 
        // ******* **************************************** ******* 
        function changeUEFields() {
            updateSuccessRate();

            // Get the selected semester value from the dropdown
            var selectedSemester = document.getElementById("semesterSelect").value;

            // Obtenir l'élément déroulant de l'UE
            var ueDropdown = document.getElementById("ueSelect");

            // Efface les options existantes
            ueDropdown.innerHTML = '<option value="">Selectionner UE</option>';

            // Récupérer les UE pour le semestre sélectionné de manière asynchrone (= garde active l'interface utilisateurs pendant l'exec)
            // https://www.devenir-webmaster.com/V2/TUTO/CHAPITRE/JAVASCRIPT/60-requete-asynchrone/
            if (selectedSemester) { 
                // ***** Configuration de la requete
                // Appellé a chaque changement de semestre, permet de faire des requetesans recharger la page
                var xhr = new XMLHttpRequest(); // instance permettant de faire des requete HTTP
                xhr.onreadystatechange = function() { // appellé a chaque changement de la requete
                    if (xhr.readyState === XMLHttpRequest.DONE) { 
                        if (xhr.status === 200) { // 200 = requete effectué avec succes

                            // Resultats de la requete analysé sous forme de JSON
                            var ueOptions = JSON.parse(xhr.responseText);

                            // Ajout des options, résultat de la requete, en tant qu'option d'UE
                            ueOptions.forEach(function(ue) {
                                var option = document.createElement("option");
                                option.value = ue;
                                option.text = ue;
                                ueDropdown.add(option);
                            });
                        } else {
                            console.error("Error fetching UEs:", xhr.statusText); // en cas d'erreur d'exec de la requete
                        }
                    }
                };
                // ***** Execution de la requete, permettant de récuperer toutes les UE possibles pour un semestre specifique
                xhr.open("GET", "scripts/get_ue_options_Analyse.php?semester=" + selectedSemester, true);
                xhr.send();
            }
        }

        // ******* ************************************ ******* 
        // *       SCRIPT-UPDATE STATS POUR L'UE SELECT       * 
        // ******* ************************************ ******* 
        // Fonction pour mettre à jour les statistiques en fonction des sélections de l'utilisateur
        // Fonction statistiques générale : Nb reponses - Moyenne
        function updateStatistics() {
            var selectedSemester = document.getElementById("semesterSelect").value;
            var selectedUE = document.getElementById("ueSelect").value;

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        document.getElementById("statistics").innerHTML = xhr.responseText;
                    } else {
                        console.error("Error fetching statistics:", xhr.statusText);
                    }
                }
            };
            xhr.open("GET", "scripts/fetch_statistics.php?semester=" + selectedSemester + "&ue=" + selectedUE, true);
            xhr.send();
        }

        // Appel initial pour afficher les statistiques pour la première fois lors du chargement de la page
        updateStatistics();

        // ******* **************************************** ******* 
        // *       SCRIPT-UPDATE STATS POUR SEMESTRE SELECT       * 
        // ******* **************************************** ******* 
        // Fonction statistique Success Rate
        function updateSuccessRate() {
            var selectedSemester = document.getElementById("semesterSelect").value;

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        document.getElementById("successrate").innerHTML = xhr.responseText;
                    } else {
                        console.error("Error fetching statistics:", xhr.statusText);
                    }
                }
            };
            xhr.open("GET", "scripts/fetch_success_rate.php?semester=" + selectedSemester);
            xhr.send();
        }


        // Fonction pour mettre à jour la comparaison de performances par semestre
        function updatePerformanceComparison() {
            var semestre1 = document.getElementById("semestre1").value;
            var semestre2 = document.getElementById("semestre2").value;

            // Vérifiez si les deux semestres ont été sélectionnés
            if (semestre1 && semestre2) {
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            document.getElementById("performanceComparison").innerHTML = xhr.responseText;
                        } else {
                            console.error("Error fetching performance comparison:", xhr.statusText);
                        }
                    }
                };
                xhr.open("GET", "scripts/comparaison_performance.php?semestre1=" + semestre1 + "&semestre2=" + semestre2);
                xhr.send();
            } else {
                document.getElementById("performanceComparison").innerHTML = "Veuillez sélectionner deux semestres.";
            }
        }

        // ******* **************************************** ******* 
        // *       SCRIPT-UPDATE STATS MATIERE PAR SEMESTRE       * 
        // ******* **************************************** ******* 

        function updateTrendsAnalysis() {
            var selectedSemester = document.getElementById("matiere_semesterSelect").value;

            if (selectedSemester) {
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            document.getElementById("trendsAnalysis").innerHTML = xhr.responseText;
                        } else {
                            console.error("Error fetching trends analysis:", xhr.statusText);
                        }
                    }
                };

                var url = "scripts/fetch_tendances.php?semester=" + selectedSemester;
                xhr.open("GET", url, true);
                xhr.send();
            } else {
                document.getElementById("trendsAnalysis").innerHTML = "";
            }
        }

        window.onload = function() {
            updateTrendsAnalysis(); // Appelle la fonction lors du chargement de la page
        };
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
            <a href="Analyse_datavise.php" class="nav-link">📈 Datavisualisation</a>
            <a href="../Valide_ton_Semestre.php" class="nav-link">🧮 Calculateur de notes</a>
            <a href="../tools/check.html" class="nav-link">🔧 Tests système</a>
        </div>
    </footer>
</html>
