import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';
import { sanitizeInput } from '../utils/sanitizer.js';

// =============================================================================
// I. Constants and DOM Elements
// =============================================================================

// DOM Elements
const registrationForm = document.getElementById('registrationForm');
const departureCitySelect = document.getElementById('departureCity');
const departureDateInput = document.getElementById('departureDate');
const departureTimeInput = document.getElementById('departureTime');
const arrivalCitySelect = document.getElementById('arrivalCity');
const arrivalDateInput = document.getElementById('arrivalDate');
const arrivalTimeInput = document.getElementById('arrivalTime');
const carSelect = document.getElementById('car');
const priceInput = document.getElementById('price');
const enterVehicleFormBtn = document.getElementById('enterVehicleFormBtn');
const cancelButton = document.getElementById('cancelButton');
const messageDisplay = document.getElementById('messageDisplay');

// Global variable to store fetched vehicles.
let userVehicles = [];

// User-facing messages
const MESSAGES = {
    FORM_VALIDATION_ERROR: "Veuillez corriger les erreurs dans le formulaire.",
    PRICE_RANGE_ERROR: "Le prix doit être un chiffre entre 1 et 50.",
    PRICE_MIN_COMMISSION_ERROR: "Le prix ne peut pas être inférieur à 3 crédits (commission de 2 crédits incluse).",
    FETCH_CITIES_ERROR: "Impossible de charger les villes. Veuillez réessayer.",
    FETCH_VEHICLES_ERROR: "Impossible de charger vos véhicules. Veuillez vous reconnecter.",
    NO_VEHICLES_REGISTERED: "Vous n'avez pas de véhicule enregistré. Veuillez d'abord en ajouter un.",
    JOURNEY_CREATED_SUCCESS: "Voyage proposé avec succès ! Redirection...",
    JOURNEY_CREATION_ERROR: (msg) => `Erreur lors de la proposition du voyage : ${msg}`,
    AUTH_REQUIRED: "Vous devez être connecté pour proposer un voyage. Redirection...",
    TOKEN_MISSING: "Jeton utilisateur manquant. Redirection vers la page de connexion.",
    SERVER_ERROR: "Erreur serveur. Veuillez réessayer plus tard.",
    INVALID_CAR_SELECTED: "Veuillez sélectionner un véhicule valide.",
    SEATS_NOT_DETERMINED: "Impossible de déterminer le nombre de places pour le véhicule sélectionné. Veuillez réessayer."
};

// =============================================================================
// II. Utility Functions
// =============================================================================

/**
 * Displays a temporary message in the dedicated box.
 */
const displayMessage = (message, type, targetDisplay = messageDisplay) => {
    if (!targetDisplay) {
        console.error('displayMessage: targetDisplay is null or undefined. Message:', message);
        return;
    }

    targetDisplay.classList.remove('alert-success', 'alert-danger', 'alert-info', 'd-none');
    targetDisplay.innerHTML = '';

    let iconClass = '';
    if (type === 'success') {
        targetDisplay.classList.add('alert-success');
        iconClass = 'bi bi-check-circle-fill';
    } else if (type === 'danger') {
        targetDisplay.classList.add('alert-danger');
        iconClass = 'bi bi-x-circle-fill';
    } else if (type === 'info') {
        targetDisplay.classList.add('alert-info');
        iconClass = 'bi bi-info-circle-fill';
    }

    targetDisplay.innerHTML = `<i class="${iconClass} me-2"></i>${message}`;
    targetDisplay.classList.remove('d-none');

    setTimeout(() => {
        targetDisplay.classList.add('d-none');
        targetDisplay.innerHTML = '';
    }, 5000);
};

// =============================================================================
// III. Data Loading Functions
// =============================================================================

/**
 * Fetches cities from a local JSON file and populates the city select elements.
 */
const fetchAndPopulateCities = async () => {
    try {
        const response = await fetch('/js/cities/cities.json');
        if (!response.ok) {
            throw new Error(`HTTP Error! Status: ${response.status}`);
        }
        let cities = await response.json();

        // Sort cities alphabetically.
        cities.sort((a, b) => a.nom.localeCompare(b.nom));

        if (cities && cities.length > 0) {
            cities.forEach(city => {
                const optionDep = document.createElement('option');
                optionDep.value = city.nom;
                optionDep.textContent = city.nom;
                departureCitySelect.appendChild(optionDep);

                const optionArr = document.createElement('option');
                optionArr.value = city.nom;
                optionArr.textContent = city.nom;
                arrivalCitySelect.appendChild(optionArr);
            });
        } else {
            console.warn("No cities found or invalid response format.");
            displayMessage(MESSAGES.FETCH_CITIES_ERROR, 'danger');
        }
    } catch (error) {
        console.error("Error loading cities:", error);
        displayMessage(MESSAGES.FETCH_CITIES_ERROR, 'danger');
    }
};

/**
 * Fetches user's registered vehicles and populates the vehicle select element.
 * Enables/disables the selector and button based on vehicle availability.
 * Stores fetched vehicles in `userVehicles` global variable.
 */
const fetchAndPopulateVehicles = async () => {
    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        displayMessage(MESSAGES.TOKEN_MISSING, 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    try {
        const vehicles = await fetchApi(
            `${API_BASE_URL}/api/all-cars`,
            'GET',
            null,
            { 'X-AUTH-TOKEN': userToken }
        );

        userVehicles = vehicles; // Store fetched vehicles.

        if (userVehicles && userVehicles.length > 0) {
            carSelect.disabled = false;
            enterVehicleFormBtn.classList.add('d-none');
            carSelect.innerHTML = '<option value="" disabled selected>Sélectionnez un véhicule</option>'; // Default option

            userVehicles.forEach(car => {
                const option = document.createElement('option');
                option.value = car.id;
                const brandName = car.brand && car.brand.label ? car.brand.label : 'Marque inconnue';
                option.textContent = `${brandName} ${car.model} (${car.licencePlate})`;
                carSelect.appendChild(option);
            });
        } else {
            carSelect.disabled = true;
            carSelect.innerHTML = '<option value="" disabled selected>Aucun véhicule enregistré</option>';
            enterVehicleFormBtn.classList.remove('d-none');
            displayMessage(MESSAGES.NO_VEHICLES_REGISTERED, 'info');
        }
    } catch (error) {
        console.error("Error loading vehicles:", error);
        displayMessage(MESSAGES.FETCH_VEHICLES_ERROR, 'danger');
        carSelect.disabled = true;
        enterVehicleFormBtn.classList.remove('d-none');
        if (error.message.includes('401') || error.message.includes('Unauthorized')) {
            setTimeout(() => { window.location.href = '/login'; }, 3000);
        }
    }
};

// =============================================================================
// IV. Form Submission Handling
// =============================================================================

/**
 * Handles the form submission for adding a new journey.
 */
const handleJourneySubmission = async (event) => {
    event.preventDefault();
    event.stopPropagation();

    registrationForm.classList.add('was-validated');

    if (!registrationForm.checkValidity()) {
        displayMessage(MESSAGES.FORM_VALIDATION_ERROR, 'danger');
        return;
    }

    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        displayMessage(MESSAGES.AUTH_REQUIRED, 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    // Explicit price validation.
    const priceValue = parseFloat(priceInput.value);
    if (isNaN(priceValue) || priceValue < 1 || priceValue > 50) {
        priceInput.classList.add('is-invalid');
        priceInput.nextElementSibling.textContent = MESSAGES.PRICE_RANGE_ERROR;
        displayMessage(MESSAGES.PRICE_RANGE_ERROR, 'danger');
        return;
    }

    // Price validation: minimum 3 credits due to commission.
    if (priceValue < 3) {
        priceInput.classList.add('is-invalid');
        priceInput.nextElementSibling.textContent = MESSAGES.PRICE_MIN_COMMISSION_ERROR;
        displayMessage(MESSAGES.PRICE_MIN_COMMISSION_ERROR, 'danger');
        return;
    } else {
        priceInput.classList.remove('is-invalid');
        priceInput.classList.add('is-valid');
    }

    const selectedCarId = parseInt(carSelect.value, 10);
    const selectedCar = userVehicles.find(car => car.id === selectedCarId);

    if (!selectedCar) {
        displayMessage(MESSAGES.INVALID_CAR_SELECTED, 'danger');
        return;
    }

    const availableSeats = selectedCar.seats || selectedCar.numberOfSeats;
    if (typeof availableSeats === 'undefined' || availableSeats === null) {
        console.error("Selected vehicle does not have 'seats' or 'numberOfSeats' property.", selectedCar);
        displayMessage(MESSAGES.SEATS_NOT_DETERMINED, 'danger');
        return;
    }

    // Prepare journey data for API with sanitized inputs.
    const journeyData = {
        departurePlace: sanitizeInput(departureCitySelect.value),
        arrivalPlace: sanitizeInput(arrivalCitySelect.value),
        departureDate: sanitizeInput(departureDateInput.value),
        departureTime: sanitizeInput(`${departureTimeInput.value}:00`),
        arrivalDate: sanitizeInput(arrivalDateInput.value),
        arrivalTime: sanitizeInput(`${arrivalTimeInput.value}:00`),
        car: selectedCarId,
        pricePerPassenger: priceValue,
        availableSeats: availableSeats
    };

    try {
        const response = await fetchApi(
            `${API_BASE_URL}/api/carpoolings`,
            'POST',
            journeyData,
            { 'X-AUTH-TOKEN': userToken }
        );

        if (response.id) {
            displayMessage(MESSAGES.JOURNEY_CREATED_SUCCESS, 'success');
            setTimeout(() => {
                window.location.href = '/profile';
            }, 2000);
        } else {
            const errorMessage = response.message || MESSAGES.SERVER_ERROR;
            displayMessage(MESSAGES.JOURNEY_CREATION_ERROR(errorMessage), 'danger');
        }
    } catch (error) {
        console.error("Error creating journey:", error);
        const apiErrorMessage = error.message || MESSAGES.SERVER_ERROR;
        displayMessage(MESSAGES.JOURNEY_CREATION_ERROR(apiErrorMessage), 'danger');
    }
};

// =============================================================================
// V. Date Handling Functions
// =============================================================================

/**
 * Sets the minimum date for departure and arrival inputs to today's date.
 */
const setMinDates = () => {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const minDate = `${yyyy}-${mm}-${dd}`;

    departureDateInput.min = minDate;
    arrivalDateInput.min = minDate;
};

/**
 * Event listener for departure date change to ensure arrival date is not earlier than departure date.
 */
const handleDepartureDateChange = () => {
    arrivalDateInput.min = departureDateInput.value;
    if (arrivalDateInput.value < departureDateInput.value) {
        arrivalDateInput.value = departureDateInput.value;
    }
};

/**
 * Event listener for arrival date change to ensure departure date is not later than arrival date.
 */
const handleArrivalDateChange = () => {
    if (departureDateInput.value > arrivalDateInput.value) {
        departureDateInput.value = arrivalDateInput.value;
    }
    departureDateInput.max = arrivalDateInput.value;
};

// =============================================================================
// VI. Initialization
// =============================================================================

// Self-executing async function for initialization when the script loads.
(async () => {
    setMinDates();
    await fetchAndPopulateCities();
    await fetchAndPopulateVehicles();

    // Event listeners.
    registrationForm.addEventListener('submit', handleJourneySubmission);
    cancelButton.addEventListener('click', () => { window.location.href = '/profile'; });
    enterVehicleFormBtn.addEventListener('click', () => { window.location.href = '/car-form'; });
    departureDateInput.addEventListener('change', handleDepartureDateChange);
    arrivalDateInput.addEventListener('change', handleArrivalDateChange);
})();