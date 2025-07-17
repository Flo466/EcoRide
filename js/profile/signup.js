
import { fetchApi } from '../api/fetch.js';
import { sanitizeInput } from '../utils/sanitizer.js';
import { API_BASE_URL } from '../config.js';

// =============================================================================
// I. Constants and DOM Elements
// =============================================================================

// User-facing messages
const MESSAGES = {
    EMAIL_MISMATCH: "Les adresses e-mail ne correspondent pas.",
    PASSWORD_INVALID: "Le mot de passe ne respecte pas les normes de sécurité.",
    PASSWORD_MISMATCH: "Les mots de passe ne correspondent pas.",
    USERNAME_CHECKING: "Vérification...",
    USERNAME_TOO_SHORT: "Le nom d'utilisateur doit contenir au moins 3 caractères.",
    USERNAME_AVAILABLE: "Nom d'utilisateur disponible !",
    USERNAME_TAKEN: "Nom d'utilisateur déjà pris.",
    USERNAME_CHECK_ERROR_UNEXPECTED: "Erreur de vérification (réponse inattendue).",
    USERNAME_CHECK_ERROR_NETWORK: (message) => `Erreur de vérification: ${message || 'réseau'}.`,
    EMAIL_INVALID_FORMAT: "Format d'email invalide.",
    EMAIL_CHECKING: "Vérification...",
    EMAIL_AVAILABLE: "Email disponible !",
    EMAIL_TAKEN: "Email déjà utilisé.",
    EMAIL_CHECK_ERROR_UNEXPECTED: "Erreur de vérification (réponse inattendue).",
    EMAIL_CHECK_ERROR_NETWORK: (message) => `Erreur de vérification: ${message || 'réseau'}.`,
    FORM_CORRECTION_NEEDED: "Veuillez corriger les erreurs dans le formulaire.",
    REGISTRATION_SUCCESS: "Compte créé avec succès ! Vous pouvez maintenant vous connecter.",
    REGISTRATION_ERROR_GENERIC: "Une erreur est survenue lors de l'inscription.",
    API_TOKEN_MISSING: "Aucun apiToken reçu après inscription. L'utilisateur ne sera pas connecté automatiquement."
};

// Regular expression for password validation
const PASSWORD_PATTERN = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+={}\[\]|\\:;"'<>,.?/~`])[A-Za-z\d!@#$%^&*()_+={}\[\]|\\:;"'<>,.?/~`]{8,}$/;

// DOM elements
const form = document.getElementById('registrationForm');
const userNameInput = document.getElementById('userName');
const emailInput = document.getElementById('email');
const confirmEmailInput = document.getElementById('confirmEmail');
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirmPassword');
const apiMessageContainer = document.getElementById('apiMessage');
const userNameStatus = document.getElementById('userNameStatus');
const emailStatus = document.getElementById('emailStatus');

// =============================================================================
// II. Utility Functions
// =============================================================================

/**
 * Displays a message in a specified container.
 * @param {HTMLElement} container - The DOM element to display the message in.
 * @param {string} message - The message text.
 * @param {boolean} isSuccess - True for success (green), false for error (red).
 */
function displayMessage(container, message, isSuccess = false) {
    container.innerHTML = `<div class="alert alert-${isSuccess ? 'success' : 'danger'}" role="alert">${message}</div>`;
    container.style.display = 'block';
}

/**
 * Clears any message from a specified container.
 * @param {HTMLElement} container - The DOM element to clear.
 */
function clearMessage(container) {
    container.innerHTML = '';
    container.style.display = 'none';
}

/**
 * Debounces a function call, ensuring it's not executed too frequently.
 * @param {Function} func - The function to debounce.
 * @param {number} delay - The delay in milliseconds.
 * @returns {Function} The debounced function.
 */
function debounce(func, delay) {
    let timeout;
    return function (...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), delay);
    };
}

// =============================================================================
// III. Validation Functions
// =============================================================================

/** Validates if email and confirm email fields match. */
function validateEmails() {
    if (emailInput.value !== confirmEmailInput.value) {
        confirmEmailInput.setCustomValidity(MESSAGES.EMAIL_MISMATCH);
    } else {
        confirmEmailInput.setCustomValidity("");
    }
}

/** Validates the password against the defined pattern. */
function validatePassword() {
    if (!PASSWORD_PATTERN.test(passwordInput.value)) {
        passwordInput.setCustomValidity(MESSAGES.PASSWORD_INVALID);
    } else {
        passwordInput.setCustomValidity("");
    }
    validatePasswordAndConfirm(); // Also validate confirmation field
}

/** Validates if password and confirm password fields match and meet pattern requirements. */
function validatePasswordAndConfirm() {
    if (passwordInput.value !== confirmPasswordInput.value) {
        confirmPasswordInput.setCustomValidity(MESSAGES.PASSWORD_MISMATCH);
    } else {
        if (PASSWORD_PATTERN.test(passwordInput.value)) {
            confirmPasswordInput.setCustomValidity("");
        } else {
            confirmPasswordInput.setCustomValidity(MESSAGES.PASSWORD_INVALID);
        }
    }
}

/**
 * Checks username availability via API call.
 * @param {string} userName - The username to check.
 */
const checkUserName = debounce(async (userName) => {
    const sanitizedUserName = sanitizeInput(userName);
    if (sanitizedUserName.length < 3) {
        userNameStatus.textContent = '';
        userNameStatus.style.color = 'initial';
        return;
    }

    userNameStatus.textContent = MESSAGES.USERNAME_CHECKING;
    userNameStatus.style.color = 'orange';

    try {
        const result = await fetchApi(`${API_BASE_URL}/api/check-userName`, 'POST', { userName: sanitizedUserName });

        if (result && typeof result.isAvailable !== 'undefined') {
            if (result.isAvailable) {
                userNameStatus.textContent = MESSAGES.USERNAME_AVAILABLE;
                userNameStatus.style.color = 'green';
            } else {
                userNameStatus.textContent = MESSAGES.USERNAME_TAKEN;
                userNameStatus.style.color = 'red';
            }
        } else {
            userNameStatus.textContent = MESSAGES.USERNAME_CHECK_ERROR_UNEXPECTED;
            userNameStatus.style.color = 'gray';
        }
    } catch (error) {
        console.error('Error checking username:', error);
        userNameStatus.textContent = MESSAGES.USERNAME_CHECK_ERROR_NETWORK(error.message);
        userNameStatus.style.color = 'gray';
    }
}, 500);

/**
 * Checks email availability and format via API call.
 * @param {string} email - The email to check.
 */
const checkEmailLive = debounce(async (email) => {
    const sanitizedEmail = sanitizeInput(email);

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(sanitizedEmail)) {
        emailStatus.textContent = MESSAGES.EMAIL_INVALID_FORMAT;
        emailStatus.style.color = 'red';
        return;
    }

    emailStatus.textContent = MESSAGES.EMAIL_CHECKING;
    emailStatus.style.color = 'orange';

    try {
        const result = await fetchApi(`${API_BASE_URL}/api/check-email`, 'POST', { email: sanitizedEmail });

        if (result && typeof result.isAvailable !== 'undefined') {
            if (result.isAvailable) {
                emailStatus.textContent = MESSAGES.EMAIL_AVAILABLE;
                emailStatus.style.color = 'green';
            } else {
                emailStatus.textContent = MESSAGES.EMAIL_TAKEN;
                emailStatus.style.color = 'red';
            }
        } else {
            emailStatus.textContent = MESSAGES.EMAIL_CHECK_ERROR_UNEXPECTED;
            emailStatus.style.color = 'gray';
        }
    } catch (error) {
        console.error('Error checking email:', error);
        emailStatus.textContent = MESSAGES.EMAIL_CHECK_ERROR_NETWORK(error.message);
        emailStatus.style.color = 'gray';
    }
}, 500);

// =============================================================================
// IV. Initialization and Event Listeners
// =============================================================================

(async function () {
    'use strict';

    // Add input event listeners for real-time validation and availability checks.
    emailInput.addEventListener('input', validateEmails);
    confirmEmailInput.addEventListener('input', validateEmails);
    passwordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', validatePasswordAndConfirm);
    passwordInput.addEventListener('input', validatePasswordAndConfirm); // Redundant listener, but harmless

    userNameInput.addEventListener('keyup', (e) => checkUserName(e.target.value));
    emailInput.addEventListener('keyup', (e) => checkEmailLive(e.target.value));

    // Handle form submission.
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearMessage(apiMessageContainer);

        form.classList.add('was-validated');

        // Re-run all validations on submit to ensure latest state.
        validateEmails();
        validatePassword();
        validatePasswordAndConfirm();

        // Prevent submission if real-time checks indicate an issue.
        if (userNameStatus.style.color === 'red' || emailStatus.style.color === 'red') {
            displayMessage(apiMessageContainer, MESSAGES.FORM_CORRECTION_NEEDED, false);
            return;
        }

        // Check overall form validity as per HTML5 validation.
        if (!form.checkValidity()) {
            console.log("Client-side form invalid.");
            return;
        }

        // Prepare user data for API submission.
        const userData = {
            userName: sanitizeInput(userNameInput.value),
            email: sanitizeInput(emailInput.value),
            password: passwordInput.value
        };

        try {
            // Send registration request to the API.
            const result = await fetchApi(`${API_BASE_URL}/api/registration`, 'POST', userData);

            displayMessage(apiMessageContainer, MESSAGES.REGISTRATION_SUCCESS, true);
            form.reset(); // Clear form fields
            form.classList.remove('was-validated'); // Remove validation styles

            userNameStatus.textContent = ''; // Clear status messages
            emailStatus.textContent = '';

            // If an API token is received, save user details to local storage.
            if (result.apiToken) {
                localStorage.setItem('userToken', result.apiToken);
                console.log('API token saved after registration.');

                if (result.id) {
                    localStorage.setItem('currentUserId', result.id);
                    console.log('User ID saved:', result.id);
                }
                if (result.email) {
                    localStorage.setItem('userEmail', result.email);
                    console.log('User email saved:', result.email);
                }
                if (result.roles) {
                    localStorage.setItem('userRoles', JSON.stringify(result.roles));
                    console.log('User roles saved:', result.roles);
                }
            } else {
                console.warn(MESSAGES.API_TOKEN_MISSING);
            }

            // Redirect to the login page after a delay.
            setTimeout(() => {
                window.location.href = '/login';
            }, 3000);
        } catch (error) {
            // Display API or network errors.
            const errorMessage = error.message || MESSAGES.REGISTRATION_ERROR_GENERIC;
            displayMessage(apiMessageContainer, `Erreur: ${errorMessage}`);
            console.error('API or network error:', error);
        }
    });
})();