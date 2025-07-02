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

        villes.sort((a, b) => a.nom.localeCompare(b.nom, 'fr', { sensitivity: 'base' }));

        setupAutocomplete(departureInput, villes);
        setupAutocomplete(arrivalInput, villes);
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

    let currentPath = window.location.pathname;
    if (currentPath.endsWith('/') && currentPath.length > 1) {
        currentPath = currentPath.slice(0, -1);
    }

    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    navLinks.forEach(link => {
        let linkPath = link.getAttribute('href');
        if (linkPath === '/') {
            if (currentPath === '/' || currentPath === '') {
                link.classList.add('active');
                link.setAttribute('aria-current', 'page');
            }
        } else if (currentPath.includes(linkPath) && linkPath !== '/') {
            link.classList.add('active');
            link.setAttribute('aria-current', 'page');
        }
    });
})();