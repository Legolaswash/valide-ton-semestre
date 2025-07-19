# Valide ton Semestre 📊

**Application web professionnelle** permettant aux étudiants de calculer la note minimale nécessaire dans leurs matières pour atteindre leur objectif de moyenne semestrielle.

## 🎯 Fonctionnalités

- **Calcul intelligent** : Note minimale basée sur les coefficients (1+ notes manquantes)
- **Interface responsive** : Design adaptatif avec navigation dynamique
- **Analyse avancée** : Graphiques interactifs, statistiques et tableaux de bord
- **Qualité** : Rapport de bugs, tests automatisés, logging

## 🚀 Installation

### Prérequis
- **Serveur web** : Apache 2.4+ ou Nginx
- **PHP** : Version 8.0 ou supérieure
- **Base de données** : MySQL 8.0+ ou MariaDB 10.5+
- **Extensions PHP** : PDO, PDO_MySQL, mbstring

### Installation
```bash
# Cloner le repository
git clone https://github.com/Legolaswash/valide-ton-semestre.git

# Importer la structure de base de données
mysql -u root -p < config/valide_ton_semestre-Structure.sql

# Configurer la connexion base de données : config/ConnexionBD.php
class DatabaseConfig {
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'valide_ton_semestre';
    private const DB_USER = 'votre_utilisateur';
    private const DB_PASSWORD = 'votre_mot_de_passe';
}

# Tester l'installation : tools/setup.php
```

## 🏗️ Architecture technique

### Stack technologique
- **Frontend** : HTML5, CSS3 (Grid/Flexbox), JavaScript ES6+ (modules)
- **Backend** : PHP 8+ avec architecture orientée objet
- **Base de données** : MySQL avec requêtes préparées PDO
- **API** : Endpoints REST JSON avec validation des données

### Architecture modulaire
```
valide-ton-semestre/
├── api/                          # APIs REST
│   ├── get_ue_options.php       # Récupération des UE
│   ├── get_fields_and_weights.php # Matières et coefficients
│   ├── insert_field.php         # Insertion des notes
│   └── insert_result.php        # Sauvegarde des calculs
├── assets/
│   ├── css/
│   │   └── main_styles.css      # CSS Grid responsive
│   ├── js/
│   │   ├── grade-calculator.js  # Moteur de calcul
│   │   └── menu-manager.js      # Gestion dynamique des menus
│   └── Report_Bug/              # Système de rapport de bugs
├── Analyse/                     # Module d'analyse
│   ├── Analyse_datavise.php     # Datavisualisation
│   ├── Analyse_forms.php        # Analyses dynamiques
│   └── scripts/                 # APIs d'analyse
├── config/                      # Configuration système
└── tools/                       # Outils de maintenance
```

**Sécurité** : Requêtes préparées, validation, headers sécurisés

## 📊 Utilisation

### Interface principale
1. **Sélection** : Choisir le semestre et l'unité d'enseignement (UE)
2. **Saisie** : Remplir les notes obtenues (laisser vides les non-évaluées)
3. **Objectif** : Définir la moyenne visée
4. **Calcul** : Obtenir la note nécessaire pour atteindre l'objectif

### Module d'analyse
- **Accès** : Menu "Analyse dynamique" ou "Datavisualisation"
- **Filtres** : Sélection par semestre, UE, période

## 🛠️ Maintenance et tests

### Tests système

**Tests intégrés :** `http://votre-domaine/tools/check.html`
**Tests API :** `http://votre-domaine/tools/test_api.php`
**Vérification configuration :** `http://votre-domaine/tools/setup.php`
**Monitoring, Logs d'erreurs** : Consultables via les outils du serveur

## 📄 Licence

- **Licence** : MIT License
- **Auteur** : Legolaswash
- **Version** : 2.0 (juillet 2025)
