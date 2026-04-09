/* 1. accueil.js */
/* Code simplifié pour l'accueil */

// Ajoute une petite interaction sur les boutons "Postuler"
document.querySelectorAll('.card .btn-primary').forEach(btn => {
    btn.addEventListener('click', (e) => {
        // Redirection classique laissée active, mais on peut loguer l'action
        console.log("Préparation de la candidature...");
    });
});