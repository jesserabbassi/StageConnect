/* 4. admin-dashboard.js */
/* Ajout et suppression d'offres utilisant la délégation d'événements (plus propre) */

const form = document.querySelector('.form-box form');
const tableBody = document.querySelector('tbody');
const compteur = document.querySelector('.stat-number');

// Ajouter une nouvelle offre
form.addEventListener('submit', (e) => {
    e.preventDefault();
    const titre = document.getElementById('titre').value;
    const entreprise = document.getElementById('entreprise').value;

    // Insertion directe de la ligne HTML
    tableBody.insertAdjacentHTML('afterbegin', `
        <tr>
            <td><strong>${titre}</strong></td>
            <td>${entreprise}</td>
            <td><span class="badge badge-accepted">Active</span></td>
            <td>
                <button class="btn btn-outline btn-sm">Éditer</button>
                <button class="btn btn-danger btn-sm">Supprimer</button>
            </td>
        </tr>
    `);

    form.reset();
    compteur.textContent = parseInt(compteur.textContent) + 1; // +1 offre
});

// Supprimer une offre (Délégation d'événement sur tout le tableau)
tableBody.addEventListener('click', (e) => {
    if (e.target.classList.contains('btn-danger')) {
        if (confirm('Supprimer cette offre ?')) {
            e.target.closest('tr').remove();
            compteur.textContent = parseInt(compteur.textContent) - 1; // -1 offre
        }
    }
});