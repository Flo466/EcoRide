import { fetchApi } from './api/fetch.js';
import { API_BASE_URL } from './config.js';
import { sanitizeInput } from '../js/utils/sanitizer.js';
import { setupAutocomplete } from './utils/autocomplete.js';
import { Carpooling } from './models/Carpooling.js';


// =============================================================================
// I. Constants and DOM Elements
// =============================================================================

// Messages for the banner and user interactions
const MESSAGES = {
    WARN_BANNER_PLACEHOLDER_MISSING: 'Le conteneur #carpooling-results n\'a pas été trouvé. La bannière est insérée en haut du corps.',
    ERROR_LOAD_CITIES: '<strong>Erreur :</strong> Impossible de charger la liste des villes pour l\'autocomplétion.',
    INFO_LOADING: 'Chargement en cours, merci de patienter... ⏳',
    WARN_SEARCH_EMPTY: '<strong>Erreur :</strong> Veuillez entrer au moins un point de départ et une destination.',
    INFO_NO_RESULTS: '<strong>Information :</strong> Aucun covoiturage trouvé pour votre recherche.',
    ERROR_SEARCH_FAILED: '<strong>Désolé, une erreur est survenue lors de la recherche des covoiturages.</strong> Veuillez réessayer plus tard.',
    ERROR_RESULTS_CONTAINER_NOT_FOUND: 'Impossible de charger les résultats de la recherche.'
};

// Retrieving DOM elements
const form = document.querySelector('.search-form');
const departurePlaceInput = document.getElementById('departurePlace');
const arrivalPlaceInput = document.getElementById('arrivalPlace');
const departureDateInput = document.getElementById('departureDate');
const carpoolingResultsContainer = document.getElementById('carpooling-results');

// Creating the message/loading banner
const mainMessageBanner = document.createElement('div');
mainMessageBanner.id = 'main-message-banner';
mainMessageBanner.className = 'alert text-center';
mainMessageBanner.setAttribute('role', 'alert');
mainMessageBanner.style.display = 'none';

// =============================================================================
// II. Utility and Initialization Functions
// =============================================================================

/**
 * Updates the content and style of the message banner.
 * @param {string} message - The message to display.
 * @param {'info'|'success'|'warning'|'danger'} type - The alert type (bootstrap).
 * @param {boolean} isVisible - Indicates if the banner should be visible.
 * @param {boolean} autoHide - Indicates if the banner should disappear after 3 seconds (default is false for this page).
 */
const updateBanner = (message, type = 'info', isVisible = true, autoHide = false) => {
    mainMessageBanner.innerHTML = message;
    mainMessageBanner.className = `alert alert-${type} text-center mt-3`;
    mainMessageBanner.style.display = isVisible ? 'block' : 'none';

    if (isVisible && autoHide && type !== 'danger') {
        setTimeout(() => {
            mainMessageBanner.style.display = 'none';
        }, 3000);
    }
};

/**
 * Inserts the banner just before the results container, or as a fallback, prepends it to the body.
 */
const initializeMessageBanner = () => {
    if (carpoolingResultsContainer && carpoolingResultsContainer.parentNode) {
        carpoolingResultsContainer.parentNode.insertBefore(mainMessageBanner, carpoolingResultsContainer);
    } else {
        document.body.prepend(mainMessageBanner);
        console.warn(MESSAGES.WARN_BANNER_PLACEHOLDER_MISSING);
    }
};

/**
 * Displays carpooling results in the designated container.
 * @param {Array<Object>} data - An array of carpooling data objects.
 */
function displayCarpoolingResults(data) {
    if (!carpoolingResultsContainer) {
        console.error('Results container not found');
        return;
    }

    carpoolingResultsContainer.innerHTML = ''; // Clear previous results

    if (data && data.length > 0) {
        const filteredData = data.filter(itemData => {
            const carpooling = new Carpooling(itemData);
            return carpooling.getStatus() === 'open';
        });

        if (filteredData.length > 0) {
            filteredData.forEach(itemData => {
                const carpooling = new Carpooling(itemData);
                const cardElement = carpooling.toCardElement();
                carpoolingResultsContainer.appendChild(cardElement);
            });
        } else {
            // If after filtering, no results are left, display the "no results" message
            updateBanner(MESSAGES.INFO_NO_RESULTS, 'info', true);
        }
    } else {
        // No results from API, display the "no results" message
        updateBanner(MESSAGES.INFO_NO_RESULTS, 'info', true);
    }
}


/**
 * Fetches the list of cities for autocomplete and sets it up.
 * @returns {Array<Object>} - The sorted array of cities.
 */
const getAndSetupCitiesForAutocomplete = async () => {
    let cities = [];
    try {
        const response = await fetch('/js/cities/cities.json');
        cities = await response.json();
        cities.sort((a, b) => a.nom.localeCompare(b.nom, 'fr', { sensitivity: 'base' }));

        if (departurePlaceInput && arrivalPlaceInput) {
            setupAutocomplete(departurePlaceInput, cities);
            setupAutocomplete(arrivalPlaceInput, cities);
        }
    } catch (error) {
        console.error("Error loading cities:", error);
        updateBanner(MESSAGES.ERROR_LOAD_CITIES, 'danger', true);
    }
    return cities;
};

// =============================================================================
// III. Search Logic
// =============================================================================

/**
 * Executes a carpooling search based on provided parameters.
 * @param {string} depart - Departure place.
 * @param {string} arrivee - Arrival place.
 * @param {string} date - Departure date.
 */
async function executeSearch(depart, arrivee, date) {
    // Display loading banner
    updateBanner(MESSAGES.INFO_LOADING, 'info', true);
    if (carpoolingResultsContainer) {
        carpoolingResultsContainer.innerHTML = '';
    }

    const searchParams = new URLSearchParams();
    if (depart) searchParams.append('departurePlace', depart);
    if (arrivee) searchParams.append('arrivalPlace', arrivee);
    if (date) searchParams.append('departureDate', date);

    if (!depart && !arrivee) {
        updateBanner(MESSAGES.WARN_SEARCH_EMPTY, 'warning', true);
        return;
    }

    const apiUrl = `${API_BASE_URL}/api/carpoolings/search?${searchParams.toString()}`;

    try {
        const result = await fetchApi(apiUrl);
        displayCarpoolingResults(result); // Call the integrated function
        updateBanner('', 'info', false);

        // Update URL in browser history without reloading the page
        const currentUrlParams = new URLSearchParams(window.location.search);
        currentUrlParams.set('page', 'carpooling'); // This might be redundant if the URL path is already /carpooling
        if (depart) currentUrlParams.set('departurePlace', depart); else currentUrlParams.delete('departurePlace');
        if (arrivee) currentUrlParams.set('arrivalPlace', arrivee); else currentUrlParams.delete('arrivalPlace');
        if (date) currentUrlParams.set('departureDate', date); else currentUrlParams.delete('departureDate');

        window.history.pushState(null, '', `?${currentUrlParams.toString()}`);
    } catch (error) {
        console.error("Error searching for carpoolings:", error);
        updateBanner(MESSAGES.ERROR_SEARCH_FAILED, 'danger', true);
        if (carpoolingResultsContainer) {
            carpoolingResultsContainer.innerHTML = `<p class="text-danger text-center">${MESSAGES.ERROR_RESULTS_CONTAINER_NOT_FOUND}</p>`;
        }
    }
}

// =============================================================================
// IV. Initialization and Event Listeners
// =============================================================================

(async () => {
    // Initial banner setup
    initializeMessageBanner();

    // Load cities and setup autocomplete
    await getAndSetupCitiesForAutocomplete();

    // Sanitize values from URL parameters and pre-fill form fields
    const paramsFromUrl = new URLSearchParams(window.location.search);
    const initialDeparturePlace = sanitizeInput(paramsFromUrl.get('departurePlace'));
    const initialArrivalPlace = sanitizeInput(paramsFromUrl.get('arrivalPlace'));
    const initialDepartureDate = sanitizeInput(paramsFromUrl.get('departureDate'));

    if (departurePlaceInput && initialDeparturePlace) departurePlaceInput.value = initialDeparturePlace;
    if (arrivalPlaceInput && initialArrivalPlace) arrivalPlaceInput.value = initialArrivalPlace;
    if (departureDateInput && initialDepartureDate) departureDateInput.value = initialDepartureDate;

    // Execute initial search if parameters are present in the URL
    if (initialDeparturePlace || initialArrivalPlace || initialDepartureDate) {
        await executeSearch(initialDeparturePlace, initialArrivalPlace, initialDepartureDate);
    }

    // Add event listener for form submission
    if (form) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            // Sanitize form inputs
            const newDeparturePlace = sanitizeInput(departurePlaceInput.value);
            const newArrivalPlace = sanitizeInput(arrivalPlaceInput.value);
            const newDepartureDate = sanitizeInput(departureDateInput.value);

            await executeSearch(newDeparturePlace, newArrivalPlace, newDepartureDate);
        });
    } else {
        console.error("Search form not found. Script cannot attach event listener.");
        // Consider adding a user-facing message if this is a critical error
    }
})();