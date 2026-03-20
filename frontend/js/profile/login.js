
import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';

// =============================================================================
// I. Constants and DOM Elements
// =============================================================================

// User-facing messages (kept in French)
const MESSAGES = {
    GENERIC_LOGIN_ERROR: 'Une erreur est survenue lors de la connexion. Veuillez réessayer.',
    INVALID_CREDENTIALS: 'Email ou mot de passe incorrect. Veuillez vérifier vos informations.',
    NETWORK_ERROR: 'Impossible de se connecter au serveur. Vérifiez votre connexion internet ou réessayez plus tard.',
    LOGIN_IN_PROGRESS: 'Connexion en cours...',
    LOGIN_BUTTON_TEXT: 'Se connecter',
    FORM_NOT_FOUND_WARNING: "Formulaire de connexion ou éléments associés non trouvés sur cette page. La logique de connexion ne sera pas activée."
};

// DOM elements
const loginForm = document.querySelector('.needs-validation');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const submitButton = loginForm ? loginForm.querySelector('button[type="submit"]') : null;
const errorMessageDiv = document.getElementById('errorMessage');

// =============================================================================
// II. Message Display Functions
// =============================================================================

/**
 * Displays an error message in the designated container.
 * @param {string} message - The error message to display.
 */
const showErrorMessage = (message) => {
    if (errorMessageDiv) {
        errorMessageDiv.textContent = message;
        errorMessageDiv.style.display = 'block';
    }
};

/**
 * Hides the error message from the display.
 */
const hideErrorMessage = () => {
    if (errorMessageDiv) {
        errorMessageDiv.textContent = '';
        errorMessageDiv.style.display = 'none';
    }
};

// =============================================================================
// III. Initialization and Event Listener
// =============================================================================

// Check if all required DOM elements are present.
if (loginForm && submitButton && emailInput && passwordInput) {
    // Add event listener for form submission.
    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        event.stopPropagation(); // Prevent default form submission and event bubbling.

        hideErrorMessage(); // Clear any previous error messages.

        loginForm.classList.add('was-validated'); // Add Bootstrap validation class.

        // If form validation fails, stop execution.
        if (!loginForm.checkValidity()) {
            return;
        }

        // Disable button and change text during submission.
        submitButton.disabled = true;
        submitButton.textContent = MESSAGES.LOGIN_IN_PROGRESS;

        const email = emailInput.value;
        const password = passwordInput.value;

        try {
            // Attempt to log in via API.
            const loginUrl = `${API_BASE_URL}/api/login`;
            const data = await fetchApi(loginUrl, 'POST', { email, password });

            // Store user information in local storage upon successful login.
            localStorage.setItem('userToken', data.apiToken);
            localStorage.setItem('currentUserId', data.id);
            localStorage.setItem('userEmail', data.email);
            localStorage.setItem('userRoles', JSON.stringify(data.roles));

            // Redirect to the profile page.
            window.location.href = '/profile';
        } catch (error) {
            // Handle login errors.
            let userFriendlyMessage = MESSAGES.GENERIC_LOGIN_ERROR;

            if (error.message.includes('Incorrect credentials')) {
                userFriendlyMessage = MESSAGES.INVALID_CREDENTIALS;
            } else if (error.message.includes('Network request failed') || error.message.includes('Failed to fetch')) {
                userFriendlyMessage = MESSAGES.NETWORK_ERROR;
            }

            showErrorMessage(userFriendlyMessage);
        } finally {
            // Re-enable button and restore original text after attempt.
            submitButton.disabled = false;
            submitButton.textContent = MESSAGES.LOGIN_BUTTON_TEXT;
        }
    });
} else {
    console.warn(MESSAGES.FORM_NOT_FOUND_WARNING);
}