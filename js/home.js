import { sanitizeInput } from '../js/utils/sanitizer.js';
import { clearMessages, displayMessage } from '../js/utils/alert.js';
import { setupAutocomplete } from './utils/autocomplete.js';

(async () => {
    const homeForm = document.querySelector('.search-form');
    const formMessagesContainer = document.getElementById('form-messages');
    const departureInput = document.getElementById('departurePlace');
    const arrivalInput = document.getElementById('arrivalPlace');

    let villes = [];

    try {
        const response = await fetch('/js/cities/cities.json');
        villes = await response.json();

        // Trie les villes par nom (ordre alphabétique)
        villes.sort((a, b) => a.nom.localeCompare(b.nom, 'fr', { sensitivity: 'base' }));

        setupAutocomplete(departureInput, villes);  // Initialisation de l'autocomplétion pour le départ
        setupAutocomplete(arrivalInput, villes);  // Initialisation de l'autocomplétion pour l'arrivée
    } catch (error) {
        console.error("Erreur lors du chargement des villes :", error);
    }

    if (homeForm) {
        homeForm.addEventListener('submit', (event) => {
            event.preventDefault();

            clearMessages(formMessagesContainer);

            const departurePlace = sanitizeInput(departureInput.value);
            const arrivalPlace = sanitizeInput(arrivalInput.value);
            const departureDate = sanitizeInput(document.getElementById('departureDate').value);

            if (!departurePlace && !arrivalPlace) {
                displayMessage(formMessagesContainer, "Veuillez entrer au moins un point de départ et une destination.");
                return;
            }

            const searchParams = new URLSearchParams();
            if (departurePlace) searchParams.append('departurePlace', departurePlace);
            if (arrivalPlace) searchParams.append('arrivalPlace', arrivalPlace);
            if (departureDate) searchParams.append('departureDate', departureDate);

            const redirectToUrl = `/covoiturages?${searchParams.toString()}`;

            console.log("URL de redirection construite dans home.js :", redirectToUrl);

            window.location.href = redirectToUrl;
        });
    } else {
        console.error("Formulaire de recherche non trouvé dans home.html. Le script ne peut pas s'attacher.");
    }
})();
