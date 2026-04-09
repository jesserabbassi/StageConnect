/* 3. mon-portfolio.js */
/* Gestion de l'upload et des boutons simplifiée */

const fileInput = document.getElementById('cv-upload');
const badge = document.querySelector('.form-box .badge');

// Met à jour le nom du fichier
fileInput.addEventListener('change', () => {
    const file = fileInput.files[0];
    badge.textContent = file ? file.name : 'Aucun CV uploadé';
    badge.className = file ? 'badge badge-accepted' : 'badge badge-pending';
});

// Simulation de sauvegarde
document.querySelector('.form-box form').addEventListener('submit', (e) => {
    e.preventDefault();
    alert(fileInput.files[0] ? "CV sauvegardé !" : "Veuillez choisir un fichier PDF.");
});

// Alertes sur les boutons d'édition
document.querySelectorAll('.btn-outline').forEach(btn => {
    btn.addEventListener('click', () => alert("Ouverture du formulaire d'édition..."));
});