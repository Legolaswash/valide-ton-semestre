/**
 * Module de gestion des menus et chargement dynamique - Valide ton Semestre
 * 
 * Ce module gère l'affichage dynamique des menus UE et le chargement des matières
 * 
 * @author Legolaswash
 * @version 2.0
 * @since 2025-01-11
 */

'use strict';

/**
 * Classe pour gérer les menus dynamiques et le chargement des données
 */
class DynamicMenuManager {
    constructor() {
        this.currentSemester = null;
        this.currentUe = null;
        this.isLoading = false;
        
        this.initializeEventListeners();
    }

    /**
     * Initialise les écouteurs d'événements
     */
    initializeEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            this.bindEvents();
            this.loadInitialData();
        });
    }

    /**
     * Lie les événements aux éléments du DOM
     */
    bindEvents() {
        const semesterSelect = document.getElementById('semesterSelect');
        const ueSelect = document.getElementById('ueSelect');

        if (semesterSelect) {
            semesterSelect.addEventListener('change', () => this.handleSemesterChange());
        }

        if (ueSelect) {
            ueSelect.addEventListener('change', () => this.handleUeChange());
        }
    }

    /**
     * Charge les données initiales (semestres)
     */
    async loadInitialData() {
        try {
            await this.loadSemesters();
        } catch (error) {
            console.error('Erreur lors du chargement initial :', error);
            this.showError('Erreur lors du chargement des données initiales');
        }
    }

    /**
     * Charge la liste des semestres
     */
    async loadSemesters() {
        const semesterSelect = document.getElementById('semesterSelect');
        if (!semesterSelect) return;

        try {
            // Note: Cette fonction dépend des données PHP injectées dans la page
            // Pour une version complètement refactorisée, il faudrait créer une API dédiée
            console.log('Semestres chargés depuis PHP');
        } catch (error) {
            console.error('Erreur lors du chargement des semestres :', error);
        }
    }

    /**
     * Gère le changement de semestre
     */
    async handleSemesterChange() {
        const semesterSelect = document.getElementById('semesterSelect');
        const selectedSemester = semesterSelect?.value;

        if (!selectedSemester) {
            this.clearUeOptions();
            this.clearDynamicForm();
            return;
        }

        if (selectedSemester === this.currentSemester) return;

        this.currentSemester = selectedSemester;
        this.currentUe = null;

        try {
            this.setLoadingState(true);
            await this.loadUeOptions(selectedSemester);
            this.clearDynamicForm();
        } catch (error) {
            console.error('Erreur lors du changement de semestre :', error);
            this.showError('Erreur lors du chargement des UE');
        } finally {
            this.setLoadingState(false);
        }
    }

    /**
     * Gère le changement d'UE
     */
    async handleUeChange() {
        const ueSelect = document.getElementById('ueSelect');
        const selectedUe = ueSelect?.value;

        if (!selectedUe || !this.currentSemester) {
            this.clearDynamicForm();
            return;
        }

        if (selectedUe === this.currentUe) return;

        this.currentUe = selectedUe;

        try {
            this.setLoadingState(true);
            await this.loadFieldsForUe(this.currentSemester, selectedUe);
        } catch (error) {
            console.error('Erreur lors du changement d\'UE :', error);
            this.showError('Erreur lors du chargement des matières');
        } finally {
            this.setLoadingState(false);
        }
    }

    /**
     * Charge les options d'UE pour un semestre donné
     * @param {string} semesterId - ID du semestre
     */
    async loadUeOptions(semesterId) {
        const ueDropdown = document.getElementById('ueSelect');
        if (!ueDropdown) return;

        try {
            const ueList = await this.fetchUeOptions(semesterId);
            this.populateUeDropdown(ueList);
        } catch (error) {
            console.error('Erreur lors du chargement des UE :', error);
            throw error;
        }
    }

    /**
     * Récupère les options d'UE depuis l'API
     * @param {string} semesterId - ID du semestre
     * @returns {Promise<Array>} Liste des UE
     */
    async fetchUeOptions(semesterId) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        try {
                            const ueOptions = JSON.parse(xhr.responseText);
                            resolve(ueOptions);
                        } catch (parseError) {
                            reject(new Error('Erreur de parsing JSON'));
                        }
                    } else {
                        reject(new Error(`Erreur HTTP ${xhr.status}: ${xhr.statusText}`));
                    }
                }
            };

            xhr.open('GET', `../../api/get_ue_options.php?semester=${encodeURIComponent(semesterId)}`, true);
            xhr.send();
        });
    }

    /**
     * Remplit le menu déroulant des UE
     * @param {Array} ueList - Liste des UE
     */
    populateUeDropdown(ueList) {
        const ueDropdown = document.getElementById('ueSelect');
        if (!ueDropdown) return;

        // Vider les options existantes
        ueDropdown.innerHTML = '<option value="">Sélectionner une UE</option>';

        // Ajouter les nouvelles options
        ueList.forEach(ue => {
            const option = document.createElement('option');
            option.value = ue;
            option.textContent = ue;
            ueDropdown.appendChild(option);
        });
    }

    /**
     * Charge les matières pour une UE donnée
     * @param {string} semesterId - ID du semestre
     * @param {string} ueCode - Code de l'UE
     */
    async loadFieldsForUe(semesterId, ueCode) {
        try {
            const fieldsData = await this.fetchFieldsData(semesterId, ueCode);
            this.generateDynamicForm(fieldsData);
        } catch (error) {
            console.error('Erreur lors du chargement des matières :', error);
            throw error;
        }
    }

    /**
     * Récupère les données des matières depuis l'API
     * @param {string} semesterId - ID du semestre
     * @param {string} ueCode - Code de l'UE
     * @returns {Promise<Array>} Données des matières
     */
    async fetchFieldsData(semesterId, ueCode) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        try {
                            const fieldsData = JSON.parse(xhr.responseText);
                            resolve(fieldsData);
                        } catch (parseError) {
                            reject(new Error('Erreur de parsing JSON'));
                        }
                    } else {
                        reject(new Error(`Erreur HTTP ${xhr.status}: ${xhr.statusText}`));
                    }
                }
            };

            const url = `../../api/get_fields_and_weights.php?semester=${encodeURIComponent(semesterId)}&ue=${encodeURIComponent(ueCode)}`;
            xhr.open('GET', url, true);
            xhr.send();
        });
    }

    /**
     * Génère le formulaire dynamique des matières
     * @param {Array} fieldsData - Données des matières
     */
    generateDynamicForm(fieldsData) {
        const formContainer = document.getElementById('dynamicFormContainer');
        if (!formContainer) return;

        // Vider le conteneur
        formContainer.innerHTML = '';

        // Créer les champs pour chaque matière
        fieldsData.forEach(field => {
            const fieldContainer = this.createFieldContainer(field);
            formContainer.appendChild(fieldContainer);
        });
    }

    /**
     * Crée un conteneur pour une matière
     * @param {Object} field - Données de la matière
     * @returns {HTMLElement} Conteneur de la matière
     */
    createFieldContainer(field) {
        const container = document.createElement('div');
        container.className = 'containerField';

        const label = this.createLabel(field.name_field);
        const gradeInput = this.createGradeInput();
        const weightInput = this.createWeightInput(field.coefficient_field);

        container.appendChild(label);
        container.appendChild(gradeInput);
        container.appendChild(weightInput);

        return container;
    }

    /**
     * Crée un label pour une matière
     * @param {string} fieldName - Nom de la matière
     * @returns {HTMLElement} Label
     */
    createLabel(fieldName) {
        const label = document.createElement('label');
        label.setAttribute('for', fieldName);
        label.textContent = `${fieldName}:`;
        return label;
    }

    /**
     * Crée un input pour la note
     * @returns {HTMLElement} Input de note
     */
    createGradeInput() {
        const input = document.createElement('input');
        input.type = 'number';
        input.className = 'gradeInput';
        input.placeholder = 'Entrer une note';
        input.min = '0';
        input.max = '20';
        input.step = '0.25';
        
        // Désactiver le scroll sur l'input
        input.addEventListener('wheel', (event) => {
            if (window.app?.gradeCalculator?.handleGoalScroll) {
                window.app.gradeCalculator.handleGoalScroll(event);
            } else if (window.handleGoalScroll) {
                window.handleGoalScroll(event);
            }
        });

        // Validation en temps réel
        input.addEventListener('input', () => this.validateInput(input));
        
        return input;
    }

    /**
     * Crée un input pour le coefficient
     * @param {number} coefficient - Coefficient de la matière
     * @returns {HTMLElement} Input de coefficient
     */
    createWeightInput(coefficient) {
        const input = document.createElement('input');
        input.type = 'number';
        input.className = 'weightInput';
        input.placeholder = 'Entrer un coefficient';
        input.value = coefficient;
        input.min = '0';
        input.step = '0.25';
        
        // Désactiver le scroll sur l'input
        input.addEventListener('wheel', (event) => {
            if (window.app?.gradeCalculator?.handleGoalScroll) {
                window.app.gradeCalculator.handleGoalScroll(event);
            } else if (window.handleGoalScroll) {
                window.handleGoalScroll(event);
            }
        });

        // Validation en temps réel
        input.addEventListener('input', () => this.validateInput(input));
        
        return input;
    }

    /**
     * Valide une entrée utilisateur
     * @param {HTMLElement} input - Élément input à valider
     */
    validateInput(input) {
        const value = parseFloat(input.value);
        
        if (input.value !== '' && (isNaN(value) || value < 0)) {
            input.style.borderColor = 'red';
            input.title = 'Veuillez entrer une valeur positive';
        } else {
            input.style.borderColor = '';
            input.title = '';
        }
    }

    /**
     * Vide les options d'UE
     */
    clearUeOptions() {
        const ueDropdown = document.getElementById('ueSelect');
        if (ueDropdown) {
            ueDropdown.innerHTML = '<option value="">Sélectionner une UE</option>';
        }
    }

    /**
     * Vide le formulaire dynamique
     */
    clearDynamicForm() {
        const formContainer = document.getElementById('dynamicFormContainer');
        if (formContainer) {
            formContainer.innerHTML = '';
        }
    }

    /**
     * Définit l'état de chargement
     * @param {boolean} loading - État de chargement
     */
    setLoadingState(loading) {
        this.isLoading = loading;
        
        const semesterSelect = document.getElementById('semesterSelect');
        const ueSelect = document.getElementById('ueSelect');
        
        if (semesterSelect) semesterSelect.disabled = loading;
        if (ueSelect) ueSelect.disabled = loading;
        
        // Afficher un indicateur de chargement si nécessaire
        if (loading) {
            this.showLoadingIndicator();
        } else {
            this.hideLoadingIndicator();
        }
    }

    /**
     * Affiche un indicateur de chargement
     */
    showLoadingIndicator() {
        // Implémentation optionnelle d'un indicateur de chargement
        console.log('Chargement en cours...');
    }

    /**
     * Cache l'indicateur de chargement
     */
    hideLoadingIndicator() {
        // Implémentation optionnelle
        console.log('Chargement terminé');
    }

    /**
     * Affiche un message d'erreur
     * @param {string} message - Message d'erreur
     */
    showError(message) {
        console.error(message);
        // Optionnel: afficher un toast ou une notification
        alert(message);
    }
}

// Initialisation du gestionnaire de menus
const menuManager = new DynamicMenuManager();

// Export des instances et fonctions pour compatibilité
window.menuManager = menuManager;
window.changeUEFields = () => menuManager.handleSemesterChange();
window.loadFields = () => menuManager.handleUeChange();
