import { fetchApi } from './api/fetch.js';
import { API_BASE_URL } from './config.js';
import { sanitizeInput } from './utils/sanitizer.js'; // AJOUTÉ : Importation de la fonction sanitizeInput

// --- DOM Elements ---
const carBrandSelect = document.getElementById('carBrand');
const modelInput = document.getElementById('model');
const colorInput = document.getElementById('color');
const energySelect = document.getElementById('energy');
const immatInput = document.getElementById('immat');
const immatInvalidFeedback = immatInput ? immatInput.nextElementSibling?.nextElementSibling : null;
const firstImmatInput = document.getElementById('firstImmat');
const seatNmbInput = document.getElementById('seatNmb');
const seatNmbInvalidFeedback = seatNmbInput ? seatNmbInput.nextElementSibling : null; // Assurez-vous que c'est le bon nextElementSibling
const petFriendlySwitch = document.getElementById('petFriendlySwitch');
const registrationForm = document.getElementById('registrationForm');
const messageDisplay = document.getElementById('messageDisplay');

// --- Regex Patterns for License Plate Validation ---
const REGEX_NEW_FORMAT = /^[A-Z]{2}\d{3}[A-Z]{2}$/i;           // Format: AA123AA
const REGEX_OLD_FORMAT_NO_SPACE = /^\d{1,4}[A-Z]{1,2}\d{1,2}$/i;   // Format: 1234AB56

// --- Message Display Utility ---
/**
 * Displays a temporary message in the message box with a type indicator.
 * @param {string} message - Message content to display.
 * @param {'success' | 'danger'} type - Bootstrap alert type for styling.
 */
const displayMessage = (message, type) => {
    if (messageDisplay) {
        messageDisplay.classList.remove('alert-success', 'alert-danger', 'd-none');
        messageDisplay.innerHTML = '';

        let iconClass = type === 'success'
            ? 'bi bi-check-circle-fill'
            : 'bi bi-x-circle-fill';

        messageDisplay.classList.add(`alert-${type}`);
        messageDisplay.innerHTML = `<i class="${iconClass} me-2"></i>${message}`;
        messageDisplay.classList.remove('d-none');

        setTimeout(() => {
            messageDisplay.classList.add('d-none');
            messageDisplay.innerHTML = '';
        }, 5000);
    }
};

// --- Validation ---
/**
 * Validates the license plate string against accepted formats.
 * @param {string} immat - License plate input string.
 * @returns {boolean} - True if valid, false otherwise.
 */
const validateImmatriculation = (immat) => {
    if (!immat) return false;
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
        if (immatInput.value.length > 0) { // Si le champ n'est pas vide, on valide
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
        } else { // Si le champ est vide, on nettoie les styles
            immatInput.classList.remove('is-invalid', 'is-valid');
            immatInvalidFeedback.style.display = 'none';
            return false; // Un champ requis vide est toujours considéré invalide pour la soumission
        }
    }
    return false;
};

/**
 * Validates the number of seats input (1-10, digits only).
 * @returns {boolean} - True if valid, false otherwise.
 */
const validateSeatNumber = () => {
    if (seatNmbInput && seatNmbInvalidFeedback) {
        const value = seatNmbInput.value.trim();
        const numValue = parseInt(value, 10);

        if (value === '') { // Si le champ est vide, on nettoie les styles
            seatNmbInput.classList.remove('is-invalid', 'is-valid');
            seatNmbInvalidFeedback.style.display = 'none';
            return false; // Un champ requis vide est toujours considéré invalide pour la soumission
        }

        // La logique de validation s'applique seulement si le champ n'est PAS vide
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

// --- Empêcher la saisie non numérique et limiter à 2 chiffres max pour le nombre de places ---
if (seatNmbInput) {
    seatNmbInput.addEventListener('input', (event) => {
        event.target.value = event.target.value.replace(/[^0-9]/g, '');
        if (event.target.value.length > 2) {
            event.target.value = event.target.value.slice(0, 2);
        }
        validateSeatNumber();
    });

    seatNmbInput.addEventListener('paste', (event) => {
        const pasteData = event.clipboardData.getData('text');
        if (!/^\d*$/.test(pasteData)) {
            event.preventDefault();
        }
    });

    seatNmbInput.addEventListener('blur', validateSeatNumber);
}

/**
 * Formats a date string from YYYY-MM-DD to DD/MM/YYYY.
 * @param {string} dateString - Date string in YYYY-MM-DD format.
 * @returns {string} - Date string in DD/MM/YYYY format, or empty string if invalid.
 */
const formatDateForBackend = (dateString) => {
    if (!dateString) return '';
    const parts = dateString.split('-'); // [YYYY, MM, DD]
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`; // DD/MM/YYYY
    }
    return '';
};

// --- Data Loading ---
/**
 * Fetches and populates the car brand dropdown from the backend API.
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
            carBrandSelect.innerHTML = '<option value="" disabled selected>Sélectionnez une marque</option>';
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
        displayMessage("Impossible de charger les marques de voiture.", 'danger');
    }
};

/**
 * Loads energy types from local JSON file and populates the select dropdown.
 */
const loadEnergyTypes = async () => {
    try {
        const response = await fetch('js/energy/energy.json');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const energies = await response.json();

        if (energySelect) {
            energySelect.innerHTML = '<option value="" disabled selected>Sélectionnez une énergie</option>';
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
        displayMessage("Impossible de charger les types d'énergie.", 'danger');
    }
};

// --- Form Submission ---
if (registrationForm) {
    registrationForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        event.stopPropagation();

        const isImmatValid = handleImmatValidation();
        const isSeatNmbValid = validateSeatNumber();

        registrationForm.classList.add('was-validated');

        if (!registrationForm.checkValidity() || !isImmatValid || !isSeatNmbValid) {
            displayMessage("Veuillez corriger les erreurs dans le formulaire.", 'danger');
            return;
        }

        const userToken = localStorage.getItem('userToken');
        if (!userToken) {
            displayMessage("Vous devez être connecté pour enregistrer un véhicule.", 'danger');
            setTimeout(() => { window.location.href = '/login'; }, 3000);
            return;
        }

        // AJOUTÉ : Assainissement des données avant de créer l'objet vehicleData
        const sanitizedModel = sanitizeInput(modelInput.value);
        const sanitizedColor = sanitizeInput(colorInput.value);
        const sanitizedLicencePlate = sanitizeInput(immatInput.value);

        const vehicleData = {
            brand_id: carBrandSelect.value,
            model: sanitizedModel, // UTILISE LA VALEUR ASSAINIE
            color: sanitizedColor, // UTILISE LA VALEUR ASSAINIE
            energy: energySelect.value,
            licencePlate: sanitizedLicencePlate, // UTILISE LA VALEUR ASSAINIE
            firstRegistrationDate: formatDateForBackend(firstImmatInput.value),
            seats: parseInt(seatNmbInput.value, 10),
            petsAllowed: petFriendlySwitch.checked,
        };

        try {
            const response = await fetchApi(
                `${API_BASE_URL}/api/car/`,
                'POST',
                vehicleData,
                { 'X-AUTH-TOKEN': userToken }
            );

            displayMessage("Véhicule enregistré avec succès ! Redirection vers votre compte...", 'success');
            registrationForm.reset();
            registrationForm.classList.remove('was-validated');
            handleImmatValidation();
            validateSeatNumber();

            setTimeout(() => {
                window.location.href = '/my-account';
            }, 2000);

        } catch (error) {
            console.error("Erreur détaillée lors de l'enregistrement du véhicule:", error);
            const errorMessage = error.message || "Une erreur inconnue est survenue.";
            displayMessage(`Erreur lors de l'enregistrement du véhicule : ${errorMessage}`, 'danger');
        }
    });
}

// --- Input Event Binding ---
if (immatInput) {
    immatInput.addEventListener('input', handleImmatValidation);
    immatInput.addEventListener('blur', handleImmatValidation);
}

// --- Initial Data Load ---
loadCarBrands();
loadEnergyTypes();
