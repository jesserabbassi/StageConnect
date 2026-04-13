document.addEventListener('DOMContentLoaded', () => {
    const searchButton = document.querySelector('.filter-action .btn-primary');
    const searchMots = document.getElementById('search-mots');
    const searchLieu = document.getElementById('search-lieu');
    const offerCards = document.querySelectorAll('main .grid.grid-2 > .card');

    if (searchButton && searchMots && searchLieu) {
        searchButton.addEventListener('click', (e) => {
            e.preventDefault();

            const mots = searchMots.value.toLowerCase().trim();
            const lieu = searchLieu.value.toLowerCase().trim();

            offerCards.forEach((card) => {
                const texte = card.textContent.toLowerCase();
                const matchesMots = !mots || texte.includes(mots);
                const matchesLieu = !lieu || texte.includes(lieu);

                card.style.display = matchesMots && matchesLieu ? 'block' : 'none';
            });
        });
    }

    offerCards.forEach((card) => {
        const applyButton = card.querySelector('.btn-primary');

        if (!applyButton) {
            return;
        }

        applyButton.addEventListener('click', (e) => {
            e.preventDefault();
            alert('Candidature envoyee avec succes !');
        });
    });
});
