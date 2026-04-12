document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.form-box form');
    const tableBody = document.querySelector('tbody');
    const compteur = document.querySelector('.stat-number');

    form.addEventListener('submit', function(e) {
        // 1. On bloque le rechargement de la page
        e.preventDefault(); 

        // 2. On "emballe" toutes les données du formulaire automatiquement
        const formData = new FormData(this);

        // 3. On envoie ces données au fichier PHP en arrière-plan
        fetch('ajouter-offre.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text()) // On lit la réponse du PHP
        .then(result => {
            if (result.trim() === 'success') {
                // 4. LE PHP A RÉUSSI ! On met à jour le visuel en direct

                // Récupération des valeurs pour l'affichage
                const titre = document.getElementById('titre').value;
                const entreprise = document.getElementById('entreprise').value;

                // Insertion de la ligne dans le tableau
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

                // On vide le formulaire et on augmente le compteur
                form.reset();
                if(compteur) {
                    compteur.textContent = parseInt(compteur.textContent) + 1;
                }
                
                // Petit retour visuel sympa
                alert("Super ! L'offre a été ajoutée à la base de données.");
            } else {
                alert("Oups, un problème est survenu lors de l'ajout.");
            }
        })
        .catch(error => {
            console.error("Erreur de connexion avec le serveur:", error);
        });
    });
});