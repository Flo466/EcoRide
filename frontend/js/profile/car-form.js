
import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';
import { sanitizeInput } from '../utils/sanitizer.js';

// =============================================================================
// I. Constants and DOM Elements
// =============================================================================

// DOM Elements
const carBrandSelect = document.getElementById('carBrand');
const modelInput = document.getElementById('model');
const colorInput = document.getElementById('color');
const energySelect = document.getElementById('energy');
const immatInput = document.getElementById('immat');
const immatInvalidFeedback = immatInput ? immatInput.nextElementSibling?.nextElementSibling : null;
const firstImmatInput = document.getElementById('firstImmat');
const seatNmbInput = document.getElementById('seatNmb');
const seatNmbInvalidFeedback = seatNmbInput ? seatNmbInput.nextElementSibling : null;
const petFriendlySwitch = document.getElementById('petFriendlySwitch');
const registrationForm = document.getElementById('registrationForm');
const messageDisplay = document.getElementById('messageDisplay');
const cancelButton = document.getElementById('cancelButton');

// Regex Patterns for License Plate Validation (French formats)
const REGEX_NEW_FORMAT = /^[A-Z]{2}\d{3}[A-Z]{2}$/i; // e.g., AB123CD
const REGEX_OLD_FORMAT_NO_SPACE = /^\d{1,4}[A-Z]{1,2}\d{1,2}$/i; // e.g., 1234AB56 or 123AB45

// =============================================================================
// II. Message Display Utility
// =============================================================================

/**
 * Displays a temporary message in the message box with a type indicator.
 * @param {string} message - Message content to display.
 * @param {'success' | 'danger' | 'warning'} type - Bootstrap alert type for styling.
 */
const displayMessage = (message, type) => {
    if (messageDisplay) {
        messageDisplay.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'd-none');
        messageDisplay.innerHTML = '';

        let iconClass = '';
        if (type === 'success') {
            iconClass = 'bi bi-check-circle-fill';
        } else if (type === 'danger') {
            iconClass = 'bi bi-x-circle-fill';
        } else if (type === 'warning') {
            iconClass = 'bi bi-exclamation-triangle-fill';
        }

        messageDisplay.classList.add(`alert-${type}`);
        messageDisplay.innerHTML = `<i class="${iconClass} me-2"></i>${message}`;
        messageDisplay.classList.remove('d-none');

        setTimeout(() => {
            messageDisplay.classList.add('d-none');
            messageDisplay.innerHTML = '';
        }, 5000);
    }
};

// =============================================================================
// III. Validation Functions
// =============================================================================

/**
 * Validates the license plate string against accepted French formats.
 * @param {string} immat - License plate input string.
 * @returns {boolean} - True if valid, false otherwise.
 */
const validateImmatriculation = (immat) => {
    if (!immat) return false;
    // Normalize: convert to uppercase and remove spaces/hyphens
    const normalizedImmat = immat.toUpperCase().replace(/[\s-]/g, '');

    return REGEX_NEW_FORMAT.test(normalizedImmat) ||
        REGEX_OLD_FORMAT_NO_SPACE.test(normalizedImmat);
};

/**
 * Handles real-time license plate validation styling and feedback.
 * @returns {boolean} - True if valid, false otherwise.
 */
const handleImmatValidation = () => {
    if (immatInput && immatInvalidFeedback) {
        if (immatInput.value.length > 0) {
            if (!validateImmatriculation(immatInput.value)) {
                immatInput.classList.add('is-invalid');
                immatInput.classList.remove('is-valid');
                immatInvalidFeedback.style.display = 'block';
                return false;
            } else {
                immatInput.classList.remove('is-invalid');
                immatInput.classList.add('is-valid');
                immatInvalidFeedback.style.display = 'none';
                return true;
            }
        } else {
            // If the field is empty, remove all validation styles and hide feedback
            immatInput.classList.remove('is-invalid', 'is-valid');
            immatInvalidFeedback.style.display = 'none';
            return false; // Or true, depending on whether empty is considered valid by Bootstrap's 'required'
        }
    }
    return false;
};

/**
 * Validates the number of seats input (1-10, digits only).
 * Provides immediate feedback via Bootstrap classes.
 * @returns {boolean} - True if valid, false otherwise.
 */
const validateSeatNumber = () => {
    if (seatNmbInput && seatNmbInvalidFeedback) {
        const value = seatNmbInput.value.trim();
        const numValue = parseInt(value, 10);

        if (value === '') {
            seatNmbInput.classList.remove('is-invalid', 'is-valid');
            seatNmbInvalidFeedback.style.display = 'none';
            return false; // Consider empty invalid if 'required' is used in HTML
        }

        if (isNaN(numValue) || numValue < 1 || numValue > 10) {
            seatNmbInput.classList.add('is-invalid');
            seatNmbInput.classList.remove('is-valid');
            seatNmbInvalidFeedback.textContent = "Le nombre de places doit être un chiffre entre 1 et 10.";
            seatNmbInvalidFeedback.style.display = 'block';
            return false;
        } else {
            seatNmbInput.classList.remove('is-invalid');
            seatNmbInput.classList.add('is-valid');
            seatNmbInvalidFeedback.style.display = 'none';
            return true;
        }
    }
    return false;
};

// Event listeners for seat number input to ensure numeric input and limit length.
if (seatNmbInput) {
    seatNmbInput.addEventListener('input', (event) => {
        event.target.value = event.target.value.replace(/[^0-9]/g, ''); // Allow only digits
        if (event.target.value.length > 2) {
            event.target.value = event.target.value.slice(0, 2); // Limit to 2 digits max
        }
        validateSeatNumber(); // Re-validate on each input
    });

    seatNmbInput.addEventListener('paste', (event) => {
        const pasteData = event.clipboardData.getData('text');
        if (!/^\d*$/.test(pasteData)) { // Prevent pasting non-numeric characters
            event.preventDefault();
        }
    });

    seatNmbInput.addEventListener('blur', validateSeatNumber); // Validate when input loses focus
}

/**
 * Formats a date string from YYYY-MM-DD (ISO) to DD/MM/YYYY for backend compatibility.
 * @param {string} dateString - Date string in YYYY-MM-DD format.
 * @returns {string} - Formatted date string in DD/MM/YYYY format, or empty string if invalid.
 */
const formatDateForBackend = (dateString) => {
    if (!dateString) return '';
    const parts = dateString.split('-'); // Splits "YYYY-MM-DD" into ["YYYY", "MM", "DD"]
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`; // Re-arranges to "DD/MM/YYYY"
    }
    return '';
};

// =============================================================================
// IV. Data Loading Functions
// =============================================================================

/**
 * Fetches and populates the car brand dropdown from the backend API.
 * Requires a user token for authentication.
 */
const loadCarBrands = async () => {
    try {
        const userToken = localStorage.getItem('userToken');
        if (!userToken) {
            displayMessage("Token utilisateur manquant pour charger les marques de voiture.", 'danger');
            return;
        }

        const brands = await fetchApi(
            `${API_BASE_URL}/api/brands`,
            'GET',
            null,
            { 'X-AUTH-TOKEN': userToken }
        );

        if (carBrandSelect) {
            carBrandSelect.innerHTML = '<option value="" disabled selected>Sélectionnez une marque</option>'; // Default option
            if (brands && brands.length > 0) {
                brands.forEach(brand => {
                    const option = document.createElement('option');
                    option.value = brand.id;
                    option.textContent = brand.label;
                    carBrandSelect.appendChild(option);
                });
            } else {
                displayMessage("Aucune marque de voiture disponible.", 'warning');
            }
        }
    } catch (error) {
        console.error("Error loading car brands:", error);
        displayMessage("Impossible de charger les marques de voiture.", 'danger');
    }
};

/**
 * Loads energy types from a local JSON file and populates the energy select dropdown.
 */
const loadEnergyTypes = async () => {
    try {
        const response = await fetch('js/energy/energy.json'); // Path to the local JSON file
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const energies = await response.json();

        if (energySelect) {
            energySelect.innerHTML = '<option value="" disabled selected>Sélectionnez une énergie</option>'; // Default option
            if (energies && energies.length > 0) {
                energies.forEach(energy => {
                    const option = document.createElement('option');
                    option.value = energy;
                    option.textContent = energy;
                    energySelect.appendChild(option);
                });
            } else {
                displayMessage("Aucun type d'énergie disponible.", 'warning');
            }
        }
    } catch (error) {
        console.error("Error loading energy types:", error);
        displayMessage("Impossible de charger les types d'énergie.", 'danger');
    }
};

// =============================================================================
// V. Form Submission Handler
// =============================================================================

// Attach submit event listener to the registration form.
if (registrationForm) {
    registrationForm.addEventListener('submit', async (event) => {
        event.preventDefault(); // Prevent default form submission
        event.stopPropagation(); // Stop event propagation

        // Perform all validations
        const isImmatValid = handleImmatValidation();
        const isSeatNmbValid = validateSeatNumber();

        registrationForm.classList.add('was-validated'); // Trigger Bootstrap's built-in validation styling

        // Check overall form validity, including custom validations
        if (!registrationForm.checkValidity() || !isImmatValid || !isSeatNmbValid) {
            displayMessage("Veuillez corriger les erreurs dans le formulaire.", 'danger');
            return; // Stop if form is invalid
        }

        const userToken = localStorage.getItem('userToken');
        if (!userToken) {
            displayMessage("Vous devez être connecté pour enregistrer un véhicule.", 'danger');
            setTimeout(() => { window.location.href = '/login'; }, 3000); // Redirect to login after a delay
            return;
        }

        // Sanitize input values to prevent XSS.
        const sanitizedModel = sanitizeInput(modelInput.value);
        const sanitizedColor = sanitizeInput(colorInput.value);
        const sanitizedLicencePlate = sanitizeInput(immatInput.value);

        // Prepare vehicle data payload.
        const vehicleData = {
            brand_id: carBrandSelect.value,
            model: sanitizedModel,
            color: sanitizedColor,
            energy: energySelect.value,
            licencePlate: sanitizedLicencePlate,
            firstRegistrationDate: formatDateForBackend(firstImmatInput.value), // Format date for backend
            seats: parseInt(seatNmbInput.value, 10), // Ensure seats is an integer
            petsAllowed: petFriendlySwitch.checked, // Boolean from checkbox
        };

        try {
            // Send vehicle data to the API.
            const response = await fetchApi(
                `${API_BASE_URL}/api/car/`, // API endpoint for car registration
                'POST',
                vehicleData,
                { 'X-AUTH-TOKEN': userToken }
            );

            displayMessage("Véhicule enregistré avec succès ! Redirection vers votre compte...", 'success');
            registrationForm.reset(); // Clear the form
            registrationForm.classList.remove('was-validated'); // Remove validation styles
            handleImmatValidation(); // Reset immat validation feedback
            validateSeatNumber(); // Reset seat number validation feedback

            // Redirect to profile page after successful registration.
            setTimeout(() => {
                window.location.href = '/profile';
            }, 2000);

        } catch (error) {
            console.error("Detailed error during vehicle registration:", error);
            const errorMessage = error.message || "Une erreur inconnue est survenue.";
            displayMessage(`Erreur lors de l'enregistrement du véhicule : ${errorMessage}`, 'danger');
        }
    });
}

// =============================================================================
// VI. Event Bindings and Initial Load
// =============================================================================

// Event listeners for real-time validation on license plate input.
if (immatInput) {
    immatInput.addEventListener('input', handleImmatValidation); // Validate on every input change
    immatInput.addEventListener('blur', handleImmatValidation);   // Validate when input loses focus
}

// Initial data load for dropdowns when the script runs.
loadCarBrands();
loadEnergyTypes();

// Event listener for the Cancel button.
if (cancelButton) {
    cancelButton.addEventListener('click', () => {
        window.location.href = '/profile'; // Redirect to profile page
    });
}