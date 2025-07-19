/**
 * Module de gestion du rapport de bugs
 * Gère l'interface de signalement de problèmes avec capture d'écran
 */

// Variable pour suivre l'état du bouton de capture d'écran
var screenshotButtonClicked = false;

/**
 * Affiche la fenêtre de rapport de bugs
 */
function openBugReport() {
    var bugReportModal = document.getElementById('bugReportModal');
    bugReportModal.style.display = 'block';
}

// Ferme la fenêtre de rapport
function closeBugReport() {
    document.getElementById('bugReportModal').style.display = 'none';
}

// Prend une capture d'écran et l'affiche dans la fenêtre de rapport
function takeScreenshotModal() {
    html2canvas(document.body).then(function(canvas) {
        var screenshotPreview = document.getElementById('screenshotPreview');
        screenshotPreview.innerHTML = '';
        screenshotPreview.appendChild(canvas);
        screenshotButtonClicked = true;
        document.getElementById('screenshot-button').style.display = 'block';
        closeBugReport();
    });
}

// Prend une capture d'écran de la page et l'affiche dans la fenêtre de rapport
function takeScreenshotPage() {
    if (screenshotButtonClicked) {
        html2canvas(document.body).then(function(canvas) {
            var screenshotPreview = document.getElementById('screenshotPreview');
            screenshotPreview.innerHTML = '';
            screenshotPreview.appendChild(canvas);
            openBugReport();
            screenshotButtonClicked = false;
            document.getElementById('screenshot-button').style.display = 'none';
        });
    }
}

/**
 * Envoie le rapport de bug (simulation)
 */
function submitBugReport() {
    alert('Rapport de bug envoyé avec succès');
    resetForm();
    closeBugReport();
}

/**
 * Remet à zéro tous les champs du formulaire
 */
function resetForm() {
    document.getElementById('bugDescription').value = '';
    document.getElementById('bugScreenshot').value = '';
    document.getElementById('screenshotPreview').innerHTML = '';
    document.getElementById('screenshot-button').style.display = 'none';
}