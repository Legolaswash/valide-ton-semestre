<?php
/**
 * Script de configuration et vérification du système
 * 
 * Ce script vérifie que l'environnement est correctement configuré
 * et effectue les vérifications nécessaires
 * 
 * @author Legolaswash
 * @version 1.0
 * @since 2025-01-11
 */

declare(strict_types=1);

// Configuration de l'affichage des erreurs pour le développement
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/**
 * Classe utilitaire pour les vérifications système
 */
class SystemChecker
{
    private array $checks = [];
    private bool $allPassed = true;

    /**
     * Lance toutes les vérifications
     */
    public function runAllChecks(): void
    {
        $this->checkPHPVersion();
        $this->checkDatabaseConnection();
        $this->checkRequiredExtensions();
        $this->checkFilePermissions();
        $this->checkDirectoryStructure();
        
        $this->displayResults();
    }

    /**
     * Vérifie la version de PHP
     */
    private function checkPHPVersion(): void
    {
        $currentVersion = PHP_VERSION;
        $minVersion = '7.4.0';
        
        $isValid = version_compare($currentVersion, $minVersion, '>=');
        
        $this->addCheck(
            'Version PHP',
            $isValid,
            $isValid ? "PHP $currentVersion (✓)" : "PHP $currentVersion (minimum requis: $minVersion)"
        );
    }

    /**
     * Vérifie la connexion à la base de données
     */
    private function checkDatabaseConnection(): void
    {
        try {
            require_once '../config/ConnexionBD.php';
            $pdo = DatabaseConfig::getConnection();
            
            // Test simple de connexion
            $pdo->query('SELECT 1');
            
            $this->addCheck(
                'Connexion base de données',
                true,
                'Connexion réussie'
            );
        } catch (Exception $e) {
            $this->addCheck(
                'Connexion base de données',
                false,
                'Erreur: ' . $e->getMessage()
            );
        }
    }

    /**
     * Vérifie les extensions PHP requises
     */
    private function checkRequiredExtensions(): void
    {
        $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
        
        foreach ($requiredExtensions as $extension) {
            $isLoaded = extension_loaded($extension);
            
            $this->addCheck(
                "Extension $extension",
                $isLoaded,
                $isLoaded ? 'Chargée' : 'Manquante'
            );
        }
    }

    /**
     * Vérifie les permissions des fichiers
     */
    private function checkFilePermissions(): void
    {
        $criticalFiles = [
            '../config/ConnexionBD.php',
            '../index.php',
            '../assets/js/grade-calculator.js',
            '../assets/js/menu-manager.js'
        ];

        foreach ($criticalFiles as $file) {
            $exists = file_exists($file);
            $readable = $exists && is_readable($file);
            
            $this->addCheck(
                "Fichier $file",
                $readable,
                $exists ? ($readable ? 'Accessible' : 'Non lisible') : 'Introuvable'
            );
        }
    }

    /**
     * Vérifie la structure des répertoires
     */
    private function checkDirectoryStructure(): void
    {
        $requiredDirs = [
            '../assets/js',
            '../Analyse',
            '../assets/Report_Bug'
        ];

        foreach ($requiredDirs as $dir) {
            $exists = is_dir($dir);
            
            $this->addCheck(
                "Répertoire $dir",
                $exists,
                $exists ? 'Existe' : 'Manquant'
            );
        }
    }

    /**
     * Ajoute un résultat de vérification
     */
    private function addCheck(string $name, bool $passed, string $message): void
    {
        $this->checks[] = [
            'name' => $name,
            'passed' => $passed,
            'message' => $message
        ];

        if (!$passed) {
            $this->allPassed = false;
        }
    }

    /**
     * Affiche les résultats des vérifications
     */
    private function displayResults(): void
    {
        echo "<!DOCTYPE html>\n";
        echo "<html lang='fr'>\n";
        echo "<head>\n";
        echo "    <meta charset='UTF-8'>\n";
        echo "    <title>Vérification Système - Valide ton Semestre</title>\n";
        echo "    <style>\n";
        echo "        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\n";
        echo "        .check { padding: 10px; margin: 5px 0; border-radius: 4px; }\n";
        echo "        .passed { background: #d4edda; border-left: 4px solid #28a745; }\n";
        echo "        .failed { background: #f8d7da; border-left: 4px solid #dc3545; }\n";
        echo "        .summary { padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center; }\n";
        echo "        .summary.success { background: #d4edda; color: #155724; }\n";
        echo "        .summary.error { background: #f8d7da; color: #721c24; }\n";
        echo "        h1 { color: #333; }\n";
        echo "        .actions { margin-top: 30px; text-align: center; }\n";
        echo "        .btn { padding: 10px 20px; margin: 0 10px; text-decoration: none; border-radius: 4px; }\n";
        echo "        .btn-primary { background: #007bff; color: white; }\n";
        echo "        .btn-success { background: #28a745; color: white; }\n";
        echo "    </style>\n";
        echo "</head>\n";
        echo "<body>\n";
        
        echo "<h1>🔧 Vérification Système - Valide ton Semestre</h1>\n";
        
        echo "<div class='summary " . ($this->allPassed ? 'success' : 'error') . "'>\n";
        if ($this->allPassed) {
            echo "<h2>✅ Toutes les vérifications sont passées avec succès !</h2>\n";
            echo "<p>Votre système est correctement configuré.</p>\n";
        } else {
            echo "<h2>❌ Des problèmes ont été détectés</h2>\n";
            echo "<p>Veuillez corriger les erreurs ci-dessous avant de continuer.</p>\n";
        }
        echo "</div>\n";
        
        echo "<h2>Détails des vérifications :</h2>\n";
        
        foreach ($this->checks as $check) {
            $class = $check['passed'] ? 'passed' : 'failed';
            $icon = $check['passed'] ? '✅' : '❌';
            
            echo "<div class='check $class'>\n";
            echo "    <strong>$icon {$check['name']}</strong>: {$check['message']}\n";
            echo "</div>\n";
        }
        
        echo "<div class='actions'>\n";
        echo "<a href='#' onclick='location.reload()' class='btn btn-primary'>🔄 Relancer les vérifications</a>\n";
        echo "</div>\n";
        
        echo "<hr style='margin: 40px 0;'>\n";
        echo "<h3>ℹ️ Informations système :</h3>\n";
        echo "<ul>\n";
        echo "<li><strong>PHP :</strong> " . PHP_VERSION . "</li>\n";
        echo "<li><strong>Serveur :</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu') . "</li>\n";
        echo "<li><strong>OS :</strong> " . PHP_OS . "</li>\n";
        echo "<li><strong>Répertoire :</strong> " . __DIR__ . "</li>\n";
        echo "<li><strong>Date/Heure :</strong> " . date('Y-m-d H:i:s') . "</li>\n";
        echo "</ul>\n";
        
        echo "</body>\n";
        echo "</html>\n";
    }
}

// Lancement des vérifications
$checker = new SystemChecker();
$checker->runAllChecks();
?>

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

        <!-- Footer -->
    <footer>
        <div class="footer-content">
            <span>Copyright &copy; Legolas <script>document.write(new Date().getFullYear())</script></span>
            <a href="../Analyse/Analyse_datavise.php" class="nav-link">📈 Datavisualisation</a>
            <a href="../Analyse/Analyse_forms.php" class="nav-link">📊 Analyse dynamique</a>
            <a href="../Valide_ton_Semestre.php" class="nav-link">🧮 Calculateur de notes</a>

            <a href="test_api.php" class="nav-link">🛠️ Configuration</a>
        </div>
    </footer>