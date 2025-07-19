<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="assets\css\main_styles.css">
        <link rel="stylesheet" href="assets\Report_Bug\ReportBug_styles.css">
        <link rel="icon" href="assets\images\calculatrice.png" type="image/png">
        <title>Valide ton Semestre</title>
    </head>

    <body>
        <?php
            require "config\ConnexionBD.php";

            // Function to get all semesters from the database
            function getAllSemesters($pdo) {
                $query = "SELECT * FROM Semestre_Choix";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Function to get all UEs for a given semester from the database
            function getUEsForSemester($pdo, $semester) {
                $query = "SELECT DISTINCT UE FROM ues_choix WHERE semester_id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$semester]);
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            // Get all semesters
            $semesters = getAllSemesters($pdo);
        ?>

        <h1 class="Main Title">Valide ton Semestre:</h1>

        <div id="Explications">
            <!-- Tutorial section -->
            <div id="tutorial">
                <h3>Comment Utiliser</h3>
                <p>Cet outil vous aide à calculer la note minimale que vous devez obtenir pour atteindre votre moyenne visée.</p>
                <p>1. Sélectionnez le semestre et l'unité d'enseignement (UE).</p>
                <p>2. Remplissez les champs avec vos notes actuelles et les coefficients.</p>
                <p>3. Entrez votre moyenne visée dans le champ "Moyenne visée".</p>
                <p>4. Cliquez sur le bouton "Calculer la Note Minimale" pour voir la note minimale dont vous avez besoin pour atteindre votre objectif.</p>
                <p>5. Si vous rencontrez des bogues ou des problèmes, veuillez utiliser le bouton "Signaler un Problème" ci-dessous.</p>
            </div>
            <!-- Limitations section -->
            <div id="limitations">
                <h3>Limitations</h3>
                <p>Cet outil fonctionne pour :</p>
                <p>- 1 note manquante, à n'importe quel coefficient</p>
                <p>- 2 notes manquantes, au même coefficient</p>
                <p>(pour toutes les notes sur la même échelle (par exemple : toutes sur /20).)</p>
            </div>
        </div>

        <!-- Sélection du semestre -->
        <label for="semesterSelect">Sélectionner le semestre :</label>
        <select id="semesterSelect" onchange="changeUEFields()">
            <option value="">Sélectionner un semestre</option>
            <?php
                foreach ($semesters as $semester) {
                    echo '<option value="' . $semester['semester_id'] . '">' . $semester['semester_id'] . '</option>';
                }
            ?>
        </select>

        <!-- Sélection de l'UE -->
        <label for="ueSelect">Sélectionner l'UE :</label>
        <select id="ueSelect" onchange="loadFields()">
            <option value="">Sélectionner une UE</option>
        </select>

        <!-- Conteneur pour le formulaire dynamique -->
        <div id="dynamicFormContainer"></div>

        <!-- Champ de saisie de l'objectif -->
        <label for="goal">Moyenne visée :</label>
        <input type="number" id="goal" class="goal" placeholder="10" value="10" onwheel="handleGoalScroll(event)">

        <!-- Bouton de calcul -->
        <button id="calculateButton" onclick="calculateMinimumGrade()">Calculer la note nécessaire</button>
        <div id="result"></div>

        <!-- Formulaire de rapport de bugs -->
        <div id="bugReportModal" class="modal">
            <div class="modal-content">
                <!-- Bouton de fermeture -->
                <span class="close" onclick="closeBugReport()">&times;</span>
                <h2>Bug Report</h2>

                <!-- Bug Description input -->
                <label for="bugDescription">Description:</label>
                <textarea id="bugDescription" rows="4"></textarea>

                <!-- Loader files from the device -->
                <input type="file" id="bugScreenshot" accept="image/*">

                <!-- Button to trigger screenshot capture in the modal -->
                <button class="modal-screenshot-button" onclick="takeScreenshotModal()">Prendre une capture d'écran</button>
                <div id="screenshotPreview"></div> <!-- Display area for the screenshot preview -->

                <!-- Reset and Submit buttons container -->
                <div class="button-container">
                    <button class="reset" onclick="resetForm()">Reset</button>
                    <button class="submit" onclick="submitBugReport()">Submit</button>
                </div>

            </div>
        </div>

        <!-- Button to capture screenshot in the page -->
        <button id="screenshot-button" class="screenshot-button" onclick="takeScreenshotPage()">Prendre une capture d'écran</button>

        <script src="assets\Report_Bug\ReportBug_html2canvas.js"></script>
        <script src="assets\Report_Bug\ReportBug_script.js"></script>

        <!-- Nouveaux modules refactorisés -->
        <script src="assets\js\menu-manager.js"></script>
        <script src="assets\js\grade-calculator.js"></script>

        <!-- Script d'initialisation et compatibilité -->
        <script>
            // Fonction utilitaire pour attendre qu'une variable soit disponible
            function waitForModule(moduleName, maxAttempts = 50) {
                return new Promise((resolve, reject) => {
                    let attempts = 0;
                    const checkModule = () => {
                        if (window[moduleName]) {
                            resolve(window[moduleName]);
                        } else if (attempts < maxAttempts) {
                            attempts++;
                            setTimeout(checkModule, 100);
                        } else {
                            reject(new Error(`Module ${moduleName} non disponible après ${maxAttempts} tentatives`));
                        }
                    };
                    checkModule();
                });
            }

            // Attendre que les modules soient chargés ET que le DOM soit prêt
            function initializeApp() {
                try {
                    // Vérifier que les classes sont disponibles
                    if (typeof DynamicMenuManager === 'undefined' || typeof GradeCalculator === 'undefined') {
                        console.warn('⏳ Classes non encore chargées, nouvelle tentative...');
                        setTimeout(initializeApp, 100);
                        return;
                    }

                    // Attendre que les instances soient disponibles
                    Promise.all([
                        waitForModule('menuManager'),
                        waitForModule('gradeCalculator')
                    ]).then(() => {
                        console.log('✅ Application initialisée avec succès');
                        console.log('📊 Modules disponibles:', {
                            menuManager: !!window.menuManager,
                            gradeCalculator: !!window.gradeCalculator
                        });
                    }).catch(error => {
                        console.error('❌ Erreur lors du chargement des modules:', error);
                    });
                    
                } catch (error) {
                    console.error('❌ Erreur lors de l\'initialisation:', error);
                }
            }

            // Initialiser dès que possible
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeApp);
            } else {
                initializeApp();
            }

            // Fonctions de compatibilité pour les événements HTML onclick/onchange
            async function changeUEFields() {
                try {
                    const manager = await waitForModule('menuManager', 10);
                    if (manager && typeof manager.handleSemesterChange === 'function') {
                        manager.handleSemesterChange();
                    }
                } catch (error) {
                    // Fallback : utiliser la fonction globale si elle existe
                    if (window.changeUEFields !== changeUEFields && typeof window.changeUEFields === 'function') {
                        window.changeUEFields();
                    }
                }
            }

            async function loadFields() {
                try {
                    const manager = await waitForModule('menuManager', 10);
                    if (manager && typeof manager.handleUeChange === 'function') {
                        manager.handleUeChange();
                    }
                } catch (error) {
                    // Fallback : utiliser la fonction globale si elle existe
                    if (window.loadFields !== loadFields && typeof window.loadFields === 'function') {
                        window.loadFields();
                    }
                }
            }

            async function calculateMinimumGrade() {
                try {
                    const calculator = await waitForModule('gradeCalculator', 10);
                    if (calculator && typeof calculator.calculateMinimumGrade === 'function') {
                        calculator.calculateMinimumGrade();
                    }
                } catch (error) {
                    // Fallback : utiliser la fonction globale si elle existe
                    if (window.calculateMinimumGrade !== calculateMinimumGrade && typeof window.calculateMinimumGrade === 'function') {
                        window.calculateMinimumGrade();
                    }
                }
            }

            async function handleGoalScroll(event) {
                try {
                    const calculator = await waitForModule('gradeCalculator', 10);
                    if (calculator && typeof calculator.handleGoalScroll === 'function') {
                        calculator.handleGoalScroll(event);
                    }
                } catch (error) {
                    console.warn('⚠️ gradeCalculator non disponible pour handleGoalScroll:', error.message);
                    // Fallback : utiliser la fonction globale si elle existe
                    if (window.handleGoalScroll !== handleGoalScroll && typeof window.handleGoalScroll === 'function') {
                        window.handleGoalScroll(event);
                    }
                }
            }
        </script>

        <footer>
            <div class="footer-content">
                <span>Copyright &copy; Legolas <?php echo date("Y"); ?></span>
                <a><button class="report-button" onclick="openBugReport()">Report Bug</button></a>
                <a href="Analyse/Presentation.html" class="nav-link">📊 Présentation</a>
                <a href="Analyse/Analyse_datavise.php" class="nav-link">📈 Datavisualisation</a>
                <a href="Analyse/Analyse_forms.php" class="nav-link">📊 Analyse dynamique</a>
                <a href="tools/check.html" class="nav-link">🔧 Tests système</a>
            </div>
        </footer>
    </body>
</html>
