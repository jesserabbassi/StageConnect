document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.form-box form');
    const tableBody = document.querySelector('tbody');
    const compteur = document.querySelector('.stat-number');

    if (!form) {
        return;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const titre = document.getElementById('titre')?.value.trim() || '';
        const entreprise = document.getElementById('entreprise')?.value.trim() || '';

        fetch(form.action, {
            method: form.method || 'POST',
            body: formData
        })
            .then((response) => response.text())
            .then((result) => {
                if (result.trim() === 'success') {
                    if (tableBody) {
                        const newRow =
                            '<tr>' +
                                '<td><strong>' + titre + '</strong></td>' +
                                '<td>' + entreprise + '</td>' +
                                '<td><span class="badge badge-accepted">Active</span></td>' +
                                '<td>' +
                                    '<button class="btn btn-outline btn-sm">Editer</button>' +
                                    '<button class="btn btn-danger btn-sm">Supprimer</button>' +
                                '</td>' +
                            '</tr>';

                        tableBody.insertAdjacentHTML('afterbegin', newRow);
                    }

                    form.reset();

                    if (compteur) {
                        compteur.textContent = String((parseInt(compteur.textContent, 10) || 0) + 1);
                    }

                    alert("Super ! L'offre a ete ajoutee a la base de donnees.");
                } else {
                    alert("Oups, un probleme est survenu lors de l'ajout.");
                }
            })
            .catch((error) => {
                console.error('Erreur de connexion avec le serveur:', error);
                alert("Impossible de contacter le serveur pour ajouter l'offre.");
            });
    });
});
