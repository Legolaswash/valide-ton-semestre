/**
 * Module de calcul de notes - Valide ton Semestre
 * 
 * Ce module contient toutes les fonctionnalités pour calculer les notes
 * minimales requises pour atteindre un objectif de moyenne
 * 
 * @author Legolaswash
 * @version 2.0
 * @since 2025-01-11
 */

'use strict';

/**
 * Classe principale pour gérer le calculateur de notes
 */
class GradeCalculator {
    constructor() {
        this.gradeInputs = [];
        this.weightInputs = [];
        this.fieldNames = [];
        this.isCalculationInProgress = false;
        
        this.initializeEventListeners();
    }

    /**
     * Initialise les écouteurs d'événements
     */
    initializeEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            this.bindEvents();
        });
    }

    /**
     * Lie les événements aux éléments du DOM
     */
    bindEvents() {
        const calculateButton = document.getElementById('calculateButton');
        if (calculateButton) {
            calculateButton.addEventListener('click', () => this.calculateMinimumGrade());
        }

        const goalInput = document.getElementById('goal');
        goalInput.step = '0.25';
        if (goalInput) {
            goalInput.addEventListener('wheel', (event) => this.handleGoalScroll(event));
        }
    }

    /**
     * Gère l'événement de scroll sur l'input de l'objectif
     * @param {Event} event - Événement de scroll
     */
    handleGoalScroll(event) {
        event.preventDefault();
        const delta = event.deltaY > 0 ? -0.1 : 0.1;
        const currentValue = parseFloat(event.target.value) || 0;
        const newValue = Math.max(0, Math.min(20, currentValue + delta));
        event.target.value = newValue.toFixed(1);
    }

    /**
     * Récupère et valide toutes les entrées utilisateur
     * @returns {Object} Objet contenant toutes les données validées
     */
    gatherUserInputs() {
        this.gradeInputs = Array.from(document.getElementsByClassName('gradeInput'));
        this.weightInputs = Array.from(document.getElementsByClassName('weightInput'));
        
        const goal = parseFloat(document.querySelector('#goal').value);
        const semester = document.getElementById('semesterSelect').value;
        const ue = document.getElementById('ueSelect').value;
        
        // Récupération des noms de matières
        this.fieldNames = this.extractFieldNames();
        
        return {
            goal,
            semester,
            ue,
            gradeInputs: this.gradeInputs,
            weightInputs: this.weightInputs,
            fieldNames: this.fieldNames
        };
    }

    /**
     * Extrait les noms des matières depuis les conteneurs
     * @returns {Array} Liste des noms de matières
     */
    extractFieldNames() {
        const fieldContainers = document.querySelectorAll('.containerField');
        const names = [];
        
        fieldContainers.forEach(container => {
            const label = container.querySelector('label');
            if (label) {
                names.push(label.textContent.replace(':', ''));
            }
        });
        
        return names;
    }

    /**
     * Valide les entrées utilisateur
     * @param {Object} inputs - Données d'entrée à valider
     * @returns {Object} Résultat de la validation
     */
    validateInputs(inputs) {
        const validation = {
            isValid: true,
            errors: []
        };

        // Vérification des champs vides
        const hasEmptyGrades = inputs.gradeInputs.every(input => 
            isNaN(parseFloat(input.value))
        );
        
        const hasEmptyWeights = inputs.weightInputs.every(input => 
            isNaN(parseFloat(input.value))
        );

        if (hasEmptyGrades || hasEmptyWeights) {
            validation.isValid = false;
            validation.errors.push('Veuillez remplir au moins une note et ses coefficients');
        }

        if (isNaN(inputs.goal)) {
            validation.isValid = false;
            validation.errors.push('Veuillez saisir un objectif de moyenne valide');
        }

        // Vérification des entrées invalides (bordures rouges)
        const hasInvalidInputs = [
            ...inputs.gradeInputs,
            ...inputs.weightInputs
        ].some(input => input.style.borderColor === 'red');

        if (hasInvalidInputs) {
            validation.isValid = false;
            validation.errors.push('Certaines entrées sont invalides (valeurs négatives)');
        }

        return validation;
    }

    /**
     * Traite et organise les données de notes et de coefficients
     * @param {Object} inputs - Données d'entrée
     * @returns {Object} Données organisées pour le calcul
     */
    processGradeData(inputs) {
        const data = {
            presentGrades: [],
            presentWeights: [],
            allWeights: [],
            missingIndices: [],
            missingWeights: []
        };

        for (let i = 0; i < inputs.gradeInputs.length; i++) {
            const grade = parseFloat(inputs.gradeInputs[i].value);
            const weight = parseFloat(inputs.weightInputs[i].value);

            data.allWeights.push(weight);

            if (isNaN(grade)) {
                data.missingIndices.push(i);
                data.missingWeights.push(weight);
            } else {
                data.presentGrades.push(grade);
                data.presentWeights.push(weight);
            }
        }

        return data;
    }

    /**
     * Calcule la note minimale requise
     * @param {Object} data - Données des notes organisées
     * @param {number} goal - Objectif de moyenne
     * @returns {Object} Résultat du calcul
     */
    computeMinimumGrade(data, goal) {
        const totalCredits = data.allWeights.reduce((sum, weight) => sum + weight, 0);
        const currentWeightedSum = this.calculateWeightedSum(data.presentGrades, data.presentWeights);
        const currentWeightedAverage = this.calculateWeightedAverage(data.presentGrades, data.presentWeights);
        
        const missingCount = data.missingIndices.length;
        
        let minimumGrade = 0;
        let calculationMethod = 'none';

        if (missingCount > 0) {
            const averageMissingWeight = this.calculateAverage(data.missingWeights);
            
            // Logique de calcul selon les cas
            if (averageMissingWeight !== Math.trunc(averageMissingWeight)) {
                // Cas 1: Coefficients non entiers
                minimumGrade = ((totalCredits * goal) - currentWeightedSum) / data.missingWeights.length;
                calculationMethod = 'non-integer-weights';
            } else if (missingCount > 1) {
                // Cas 2: Plusieurs matières manquantes avec coefficients entiers
                minimumGrade = ((totalCredits * goal) - currentWeightedSum) / missingCount;
                calculationMethod = 'multiple-missing';
            } else {
                // Cas 3: Une seule matière manquante
                minimumGrade = ((totalCredits * goal) - currentWeightedSum) / data.missingWeights[0];
                calculationMethod = 'single-missing';
            }

            minimumGrade = Math.max(0, parseFloat(minimumGrade.toFixed(2)));
        }

        return {
            minimumGrade,
            currentAverage: currentWeightedAverage,
            missingCount,
            calculationMethod,
            currentWeightedSum
        };
    }

    /**
     * Génère le message de résultat
     * @param {Object} result - Résultat du calcul
     * @param {number} goal - Objectif de moyenne
     * @returns {string} Message formaté
     */
    generateResultMessage(result, goal) {
        if (result.missingCount === 0) {
            return 'Tous les champs sont remplis';
        }

        const subjectText = result.missingCount > 1 ? 'matières' : 'matière';
        const verbText = result.missingCount > 1 ? 'laissées vides' : 'laissée vide';
        
        return `Vous avez besoin d'une note d'au moins ${result.minimumGrade} dans ${result.missingCount} ${subjectText} ${verbText}.`;
    }

    /**
     * Affiche le résultat dans l'interface
     * @param {Object} result - Résultat du calcul
     * @param {number} goal - Objectif de moyenne
     */
    displayResult(result, goal) {
        const resultElement = document.getElementById('result');
        const message = this.generateResultMessage(result, goal);
        
        resultElement.innerHTML = `
            <div class="result-container">
                <p>Pour atteindre une moyenne de ${goal}, ${message}</p>
                <p>Moyenne actuelle : ${result.currentAverage.toFixed(2)}</p>
                <p>${result.missingCount} matière(s) non remplie(s)</p>
            </div>
        `;
    }

    /**
     * Sauvegarde les données dans la base de données
     * @param {Object} inputs - Données d'entrée
     * @param {Object} result - Résultat du calcul
     */
    async saveToDatabase(inputs, result) {
        try {
            const resultId = await this.insertCalculationResult(inputs, result);
            await this.insertFieldData(resultId, inputs);
            
            console.log('Données sauvegardées avec succès');
        } catch (error) {
            console.error('Erreur lors de la sauvegarde :', error);
        }
    }

    /**
     * Insère le résultat principal dans la base de données
     * @param {Object} inputs - Données d'entrée
     * @param {Object} result - Résultat du calcul
     * @returns {Promise<number>} ID du résultat inséré
     */
    async insertCalculationResult(inputs, result) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        resolve(parseInt(xhr.responseText));
                    } else {
                        reject(new Error(`Erreur HTTP ${xhr.status}: ${xhr.statusText}`));
                    }
                }
            };

            const formData = new URLSearchParams({
                semester: inputs.semester,
                goal: inputs.goal,
                average: result.currentAverage,
                needed: result.minimumGrade || 'null',
                ue: inputs.ue
            });

            xhr.open('POST', '../../api/insert_result.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send(formData.toString());
        });
    }

    /**
     * Insère les données des matières dans la base de données
     * @param {number} resultId - ID du résultat principal
     * @param {Object} inputs - Données d'entrée
     */
    async insertFieldData(resultId, inputs) {
        const insertPromises = [];

        for (let i = 0; i < inputs.gradeInputs.length; i++) {
            const fieldName = inputs.fieldNames[i];
            const fieldGrade = parseFloat(inputs.gradeInputs[i].value) || null;
            
            const promise = this.insertSingleField(resultId, fieldName, fieldGrade, inputs.ue);
            insertPromises.push(promise);
        }

        await Promise.all(insertPromises);
    }

    /**
     * Insère une matière individuelle dans la base de données
     * @param {number} resultId - ID du résultat principal
     * @param {string} fieldName - Nom de la matière
     * @param {number|null} fieldGrade - Note de la matière
     * @param {string} ue - Code UE
     * @returns {Promise}
     */
    async insertSingleField(resultId, fieldName, fieldGrade, ue) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        resolve();
                    } else {
                        reject(new Error(`Erreur HTTP ${xhr.status}: ${xhr.statusText}`));
                    }
                }
            };

            const formData = new URLSearchParams({
                resultId: resultId,
                fieldName: fieldName,
                fieldGrade: fieldGrade || 'null',
                UE: ue
            });

            xhr.open('POST', '../../api/insert_field.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send(formData.toString());
        });
    }

    /**
     * Fonction principale de calcul
     */
    async calculateMinimumGrade() {
        if (this.isCalculationInProgress) return;
        
        try {
            this.isCalculationInProgress = true;
            
            // 1. Récupération et validation des données
            const inputs = this.gatherUserInputs();
            const validation = this.validateInputs(inputs);
            
            if (!validation.isValid) {
                alert(validation.errors.join('\n'));
                return;
            }

            // 2. Traitement des données
            const gradeData = this.processGradeData(inputs);
            
            // 3. Calcul de la note minimale
            const result = this.computeMinimumGrade(gradeData, inputs.goal);
            
            // 4. Affichage du résultat
            this.displayResult(result, inputs.goal);
            
            // 5. Sauvegarde en base de données (si pas d'erreurs de validation)
            if (!validation.errors.length) {
                await this.saveToDatabase(inputs, result);
            }
            
        } catch (error) {
            console.error('Erreur lors du calcul :', error);
            alert('Une erreur est survenue lors du calcul. Veuillez réessayer.');
        } finally {
            this.isCalculationInProgress = false;
        }
    }

    // ========================================
    // Méthodes utilitaires de calcul
    // ========================================

    /**
     * Calcule la moyenne pondérée
     * @param {Array} values - Valeurs
     * @param {Array} weights - Coefficients
     * @returns {number} Moyenne pondérée
     */
    calculateWeightedAverage(values, weights) {
        if (values.length === 0 || weights.length === 0) return 0;
        
        const totalWeightedSum = this.calculateWeightedSum(values, weights);
        const totalWeight = weights.reduce((sum, weight) => sum + weight, 0);
        
        return totalWeight > 0 ? totalWeightedSum / totalWeight : 0;
    }

    /**
     * Calcule la somme pondérée
     * @param {Array} values - Valeurs
     * @param {Array} weights - Coefficients
     * @returns {number} Somme pondérée
     */
    calculateWeightedSum(values, weights) {
        let sum = 0;
        for (let i = 0; i < values.length && i < weights.length; i++) {
            sum += values[i] * weights[i];
        }
        return sum;
    }

    /**
     * Calcule la moyenne simple
     * @param {Array} list - Liste de valeurs
     * @returns {number} Moyenne
     */
    calculateAverage(list) {
        if (list.length === 0) return 0;
        
        const sum = list.reduce((acc, val) => acc + val, 0);
        const average = sum / list.length;
        
        return Math.max(1, average); // Minimum 1 pour éviter les divisions par 0
    }
}

// Initialisation de l'application
const gradeCalculator = new GradeCalculator();

// Export des instances et fonctions pour compatibilité
window.gradeCalculator = gradeCalculator;
window.calculateMinimumGrade = () => gradeCalculator.calculateMinimumGrade();
window.handleGoalScroll = (event) => gradeCalculator.handleGoalScroll(event);
