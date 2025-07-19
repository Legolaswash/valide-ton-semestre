<?php
/**
 * Script de test pour vérifier les APIs
 */

// Style CSS basique
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
.success { background-color: #d4edda; border-color: #c3e6cb; }
.error { background-color: #f8d7da; border-color: #f5c6cb; }
pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

echo "<h1>🔧 Test des APIs - Valide ton Semestre</h1>";

// Test de connectivité de base
echo "<div class='test-section'>";
echo "<h2>🌐 Test de connectivité</h2>";
echo "<p>URL de base : " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";
echo "</div>";

// Test 1: get_ue_options.php
echo "<div class='test-section'>";
echo "<h2>📚 Test get_ue_options.php</h2>";

$testUrls = [
    "get_ue_options.php?semester=S1",
    "get_ue_options.php?semester=S2", 
    "get_ue_options.php?semester=S3",
    "get_ue_options.php", // Test sans paramètre
];

foreach ($testUrls as $url) {
    echo "<h3>Test: $url</h3>";
    echo "<pre>";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents("http://localhost/valide-ton-semestre/$url", false, $context);
    
    if ($response === false) {
        echo "❌ ERREUR: Impossible de contacter l'API\n";
        echo "Vérifiez que WAMP est démarré et que l'URL est correcte.";
    } else {
        echo "✅ Réponse reçue:\n";
        echo htmlspecialchars($response);
        
        // Test si c'est du JSON valide
        $json = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "\n\n📋 JSON valide - Données décodées:\n";
            print_r($json);
        } else {
            echo "\n\n⚠️  La réponse n'est pas du JSON valide";
        }
    }
    echo "</pre><hr>";
}
echo "</div>";

// Test 2: get_fields_and_weights.php
echo "<div class='test-section'>";
echo "<h2>⚖️ Test get_fields_and_weights.php</h2>";

$testUrls2 = [
    "get_fields_and_weights.php?semester=S1&ue=UE1",
    "get_fields_and_weights.php?semester=S2&ue=UE2",
    "get_fields_and_weights.php?semester=S1", // Test sans UE
    "get_fields_and_weights.php", // Test sans paramètres
];

foreach ($testUrls2 as $url) {
    echo "<h3>Test: $url</h3>";
    echo "<pre>";
    
    $response = @file_get_contents("http://localhost/valide-ton-semestre/$url", false, $context);
    
    if ($response === false) {
        echo "❌ ERREUR: Impossible de contacter l'API\n";
    } else {
        echo "✅ Réponse reçue:\n";
        echo htmlspecialchars($response);
        
        $json = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "\n\n📋 JSON valide - Données décodées:\n";
            print_r($json);
        } else {
            echo "\n\n⚠️  La réponse n'est pas du JSON valide";
        }
    }
    echo "</pre><hr>";
}
echo "</div>";

// Test 3: Base de données
echo "<div class='test-section'>";
echo "<h2>🗄️ Test de la base de données</h2>";
echo "<pre>";
try {
    require_once '../config/ConnexionBD.php';
    $pdo = DatabaseConfig::getConnection();
    
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // Test des tables
    $tables = ['Semestre_Choix', 'ues_choix'];
    foreach ($tables as $table) {
        echo "📊 Test de la table '$table':\n";
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table LIMIT 1");
            $result = $stmt->fetch();
            echo "   ✅ Table accessible - {$result['count']} enregistrements\n";
            
            // Afficher quelques exemples
            $stmt = $pdo->query("SELECT * FROM $table LIMIT 3");
            $examples = $stmt->fetchAll();
            if ($examples) {
                echo "   📝 Exemples d'enregistrements:\n";
                foreach ($examples as $row) {
                    echo "   " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
                }
            }
        } catch (Exception $e) {
            echo "   ❌ Erreur: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur de connexion à la base de données:\n";
    echo $e->getMessage();
}
echo "</pre>";
echo "</div>";

echo "<div class='test-section success'>";
echo "<h2>✅ Test terminé</h2>";
echo "<p>Si tous les tests sont verts, l'application devrait fonctionner correctement.</p>";
echo "</div>";
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

            <a href="setup.php" class="nav-link">🛠️ Configuration</a>
        </div>
    </footer>