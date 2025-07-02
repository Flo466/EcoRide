import { fetchApi } from './api/fetch.js';
import { API_BASE_URL } from './config.js';
import { displaySearchResults } from './components/display-results.js';
import { sanitizeInput } from '../js/utils/sanitizer.js';
import { clearMessages, displayMessage } from '../js/utils/alert.js';
import { setupAutocomplete } from './utils/autocomplete.js';

(async () => {
    const form = document.querySelector('.search-form');
    const departurePlaceInput = document.getElementById('departurePlace');
    const arrivalPlaceInput = document.getElementById('arrivalPlace');
    const departureDateInput = document.getElementById('departureDate');
    const formMessagesContainer = document.getElementById('form-messages');

    let villes = [];

    try {
        const response = await fetch('/js/cities/cities.json');
        villes = await response.json();
        villes.sort((a, b) => a.nom.localeCompare(b.nom, 'fr', { sensitivity: 'base' }));
    } catch (error) {
        console.error("Erreur lors du chargement des villes :", error);
    }

    if (!form) return;

    async function executeSearch(depart, arrivee, date) {
        const searchParams = new URLSearchParams();

        if (depart) searchParams.append('departurePlace', depart);
        if (arrivee) searchParams.append('arrivalPlace', arrivee);
        if (date) searchParams.append('departureDate', date);

        if (!depart && !arrivee) {
            displayMessage(formMessagesContainer, "Veuillez entrer au moins un point de départ et une destination.");
            return;
        }

        const apiUrl = `${API_BASE_URL}/api/carpooling/search?${searchParams.toString()}`;

        try {
            const result = await fetchApi(apiUrl);
            displaySearchResults(result, 'carpooling-results');

            const currentUrlParams = new URLSearchParams(window.location.search);
            currentUrlParams.set('page', 'carpooling');
            if (depart) currentUrlParams.set('departurePlace', depart); else currentUrlParams.delete('departurePlace');
            if (arrivee) currentUrlParams.set('arrivalPlace', arrivee); else currentUrlParams.delete('arrivalPlace');
            if (date) currentUrlParams.set('departureDate', date); else currentUrlParams.delete('departureDate');

            window.history.pushState(null, '', `?${currentUrlParams.toString()}`);
        } catch {
            displayMessage(formMessagesContainer, 'Désolé une erreur est survenue');
        }
    }

    if (departurePlaceInput && arrivalPlaceInput) {
        setupAutocomplete(departurePlaceInput, villes);
        setupAutocomplete(arrivalPlaceInput, villes);
    }

    // Sanitize values from URL
    const paramsFromUrl = new URLSearchParams(window.location.search);
    const initialDeparturePlace = sanitizeInput(paramsFromUrl.get('departurePlace'));
    const initialArrivalPlace = sanitizeInput(paramsFromUrl.get('arrivalPlace'));
    const initialDepartureDate = sanitizeInput(paramsFromUrl.get('departureDate'));

    if (departurePlaceInput && initialDeparturePlace) departurePlaceInput.value = initialDeparturePlace;
    if (arrivalPlaceInput && initialArrivalPlace) arrivalPlaceInput.value = initialArrivalPlace;
    if (departureDateInput && initialDepartureDate) departureDateInput.value = initialDepartureDate;

    if (initialDeparturePlace || initialArrivalPlace || initialDepartureDate) {
        await executeSearch(initialDeparturePlace, initialArrivalPlace, initialDepartureDate);
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearMessages(formMessagesContainer);

        // Sanitize form inputs
        const newDeparturePlace = sanitizeInput(departurePlaceInput.value);
        const newArrivalPlace = sanitizeInput(arrivalPlaceInput.value);
        const newDepartureDate = sanitizeInput(departureDateInput.value);

        await executeSearch(newDeparturePlace, newArrivalPlace, newDepartureDate);
    });

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