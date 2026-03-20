import { sanitizeInput } from '../js/utils/sanitizer.js';
import { setupAutocomplete } from './utils/autocomplete.js';

console.log("Home page script (home.js) loaded.");

// =============================================================================
// I. Constants and DOM Elements
// =============================================================================

// Messages for the banner and user interactions
const MESSAGES = {
    WARN_BANNER_PLACEHOLDER_MISSING: 'Le placeholder #home-message-banner-placeholder n\'a pas été trouvé. La bannière est insérée en haut du corps.',
    ERROR_LOAD_CITIES: '<strong>Erreur :</strong> Impossible de charger la liste des villes pour l\'autocomplétion.',
    WARN_SEARCH_EMPTY: '<strong>Erreur :</strong> Veuillez entrer au moins un point de départ et une destination.',
    ERROR_FORM_NOT_FOUND: '<strong>Erreur :</strong> Le formulaire principal de la page d\'accueil n\'a pas pu être chargé correctement.'
};

// Retrieving DOM elements
const homeForm = document.querySelector('.search-form');
const departureInput = document.getElementById('departurePlace');
const arrivalInput = document.getElementById('arrivalPlace');
const departureDateInput = document.getElementById('departureDate'); // Added this for clarity
const homeMessageBannerPlaceholder = document.getElementById('home-message-banner-placeholder');

// Creating the unique message banner
const mainMessageBanner = document.createElement('div');
mainMessageBanner.id = 'main-message-banner';
mainMessageBanner.className = 'alert text-center'; // Margins will be added by updateBanner
mainMessageBanner.setAttribute('role', 'alert');
mainMessageBanner.style.display = 'none'; // Hidden by default

// =============================================================================
// II. Utility and Initialization Functions
// =============================================================================

/**
 * Updates the content and style of the message banner.
 * @param {string} message - The message to display.
 * @param {'info'|'success'|'warning'|'danger'} type - The alert type (bootstrap).
 * @param {boolean} isVisible - Indicates if the banner should be visible.
 * @param {boolean} autoHide - Indicates if the banner should disappear after 3 seconds.
 */
const updateBanner = (message, type = 'info', isVisible = true, autoHide = true) => {
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
 * Inserts the banner into its placeholder, or as a fallback, prepends it to the body.
 */
const initializeMessageBanner = () => {
    if (homeMessageBannerPlaceholder) {
        homeMessageBannerPlaceholder.appendChild(mainMessageBanner);
    } else {
        // Fallback if the placeholder is not found (e.g., if HTML is not up to date)
        document.body.prepend(mainMessageBanner);
        console.warn(MESSAGES.WARN_BANNER_PLACEHOLDER_MISSING);
    }
};

/**
 * Fetches the list of cities for autocomplete and sets it up.
 */
const setupCityAutocomplete = async () => {
    let cities = [];
    try {
        const response = await fetch('/js/cities/cities.json');
        cities = await response.json();
        cities.sort((a, b) => a.nom.localeCompare(b.nom, 'fr', { sensitivity: 'base' }));

        setupAutocomplete(departureInput, cities);
        setupAutocomplete(arrivalInput, cities);
    } catch (error) {
        console.error("Error loading cities:", error);
        updateBanner(MESSAGES.ERROR_LOAD_CITIES, 'danger', true, false);
    }
};

/**
 * Handles the search form submission.
 * @param {Event} event - The form submission event.
 */
const handleFormSubmit = (event) => {
    event.preventDefault();

    const departurePlace = sanitizeInput(departureInput.value);
    const arrivalPlace = sanitizeInput(arrivalInput.value);
    const departureDate = sanitizeInput(departureDateInput.value); // Use the new input variable

    if (!departurePlace && !arrivalPlace) {
        updateBanner(MESSAGES.WARN_SEARCH_EMPTY, 'warning', true, false);
        return;
    }

    const searchParams = new URLSearchParams();
    if (departurePlace) searchParams.append('departurePlace', departurePlace);
    if (arrivalPlace) searchParams.append('arrivalPlace', arrivalPlace);
    if (departureDate) searchParams.append('departureDate', departureDate);

    const redirectToUrl = `/carpooling?${searchParams.toString()}`;

    console.log("Redirect URL constructed in home.js:", redirectToUrl);

    window.location.href = redirectToUrl;
};

/**
 * Sets up the active state for navigation links based on the current URL path.
 */
const setupActiveNavLink = () => {
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
};

// =============================================================================
// III. Main Execution
// =============================================================================

(async () => {
    initializeMessageBanner();
    await setupCityAutocomplete();

    if (homeForm) {
        homeForm.addEventListener('submit', handleFormSubmit);
    } else {
        console.error("Search form not found in home.html. Script cannot attach.");
        updateBanner(MESSAGES.ERROR_FORM_NOT_FOUND, 'danger', true, false);
    }

    setupActiveNavLink();
})();