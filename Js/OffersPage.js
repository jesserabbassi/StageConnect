/* 2. offres.js */
/* Filtrage et simulation de candidature simplifiés */

// Filtrage simple
document.querySelector('.filter-action .btn-primary').addEventListener('click', (e) => {
    e.preventDefault(); 
    const mots = document.getElementById('search-mots').value.toLowerCase();
    const lieu = document.getElementById('search-lieu').value.toLowerCase();

    document.querySelectorAll('.grid-2 .card').forEach(card => {
        const texte = card.textContent.toLowerCase();
        // Affiche la carte seulement si elle contient les mots ET le lieu
        card.style.display = (texte.includes(mots) && texte.includes(lieu)) ? 'block' : 'none';
    });
});

// Alerte de candidature
document.querySelectorAll('.card .btn-primary').forEach(btn => {
    btn.addEventListener('click', () => alert("Candidature envoyée avec succès !"));
});