import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';
import Carpooling from '../models/Carpooling.js';

// --- DOM Elements ---
const vehicleListContainer = document.querySelector('.journey-list');
const messageDisplay = document.getElementById('messageDisplay');
const loadingMessageDisplay = document.getElementById('loadingMessageDisplay');

// --- Message Display Utility ---
/**
 * Displays a temporary message in the message box with a type indicator.
 * @param {string} message - Message content to display.
 * @param {'success' | 'danger' | 'warning' | 'info'} type - Bootstrap alert type for styling.
 * @param {HTMLElement} targetDisplay - The DOM element where the message should be displayed (defaults to messageDisplay).
 */
const displayMessage = (message, type, targetDisplay = messageDisplay) => {
    if (targetDisplay) {
        // Remove all previous alert classes and ensure it's not hidden
        targetDisplay.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'alert-info', 'd-none');
        targetDisplay.innerHTML = '';

        let iconClass = '';
        // Apply appropriate Bootstrap alert class and icon based on message type
        if (type === 'success') {
            targetDisplay.classList.add('alert-success');
            iconClass = 'bi bi-check-circle-fill';
        } else if (type === 'danger') {
            targetDisplay.classList.add('alert-danger');
            iconClass = 'bi bi-x-circle-fill';
        } else if (type === 'warning') {
            targetDisplay.classList.add('alert-warning');
            iconClass = 'bi bi-exclamation-triangle-fill';
        } else if (type === 'info') {
            targetDisplay.classList.add('alert-info');
            iconClass = 'bi bi-info-circle-fill';
        }

        // Set the message content with icon
        targetDisplay.innerHTML = `<i class="${iconClass} me-2"></i>${message}`;
        // Make the message visible
        targetDisplay.classList.remove('d-none');

        // Temporary messages disappear after 5 seconds, unless it's a loading message
        // which has its own specific hide mechanism.
        if (targetDisplay !== loadingMessageDisplay) {
            setTimeout(() => {
                targetDisplay.classList.add('d-none'); // Hide after timeout
                targetDisplay.innerHTML = ''; // Clear content
            }, 5000);
        }
    } else {
        // Log an error if the target display element is not found
        console.error('displayMessage: targetDisplay est null ou undefined. Message:', message);
    }
};

/**
 * Hides a specific message element by adding the 'd-none' class and clearing its content.
 * @param {HTMLElement} targetDisplay - The DOM element to hide.
 */
const hideMessage = (targetDisplay) => {
    if (targetDisplay) {
        targetDisplay.classList.add('d-none'); // Hide the element
        targetDisplay.innerHTML = ''; // Clear its content
    }
};

/**
 * Handles user token validation, API call, and displays success/error messages.
 * @param {number} vehicleId - The ID of the vehicle to delete.
 */
const deleteVehicle = async (vehicleId) => {
    const userToken = localStorage.getItem('userToken');
    // Redirect to login if user token is missing
    if (!userToken) {
        displayMessage("Jeton utilisateur manquant. Veuillez vous reconnecter.", 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    try {
        // Call the fetchApi function to send a DELETE request.
        // fetchApi is now expected to handle 204 No Content and throw standardized errors.
        const responseData = await fetchApi(
            `${API_BASE_URL}/api/car/${vehicleId}`,
            'DELETE',
            null, // No request body for DELETE operation
            { 'X-AUTH-TOKEN': userToken } // Custom authentication header
        );

        // If a 204 status was returned by the API, responseData will be an empty object {}.
        // Otherwise, it will contain any JSON data returned by the server (e.g., a success message).
        displayMessage(responseData.message || "Véhicule supprimé avec succès.", 'success');

        // Reload the list of user vehicles to reflect the deletion
        loadUserVehicles();
    } catch (error) {
        // Log the detailed error for debugging
        console.error("Erreur lors de la suppression du véhicule:", error);
        // Display a user-friendly error message, using the message property from the thrown error.
        displayMessage(`Erreur lors de la suppression du véhicule : ${error.message}`, 'danger');
    }
};


/**
 * Loads user vehicles from the API and displays them in the vehicle list container.
 * Handles loading states, token validation, and error messages.
 */
const loadUserVehicles = async () => {
    console.log('loadUserVehicles: Attempting to load user vehicles.');
    // Ensure the vehicle list container exists before proceeding
    if (!vehicleListContainer) {
        console.error('loadUserVehicles: .vehicule-list container not found.');
        return;
    }

    // Display a loading message and clear previous vehicle list content
    displayMessage('Chargement des véhicules en cours...', 'info', loadingMessageDisplay);
    vehicleListContainer.innerHTML = '';

    const userToken = localStorage.getItem('userToken');
    console.log('loadUserVehicles: User Token:', userToken ? 'Present' : 'Missing');

    // If user token is missing, hide loading message and redirect to login
    if (!userToken) {
        hideMessage(loadingMessageDisplay);
        displayMessage("Vous devez être connecté pour voir vos véhicules.", 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    try {
        console.log('loadUserVehicles: Fetching vehicles from API: /api/all-cars');
        // Fetch all vehicles associated with the current user via the API
        const vehiclesData = await fetchApi(
            `${API_BASE_URL}/api/all-cars`,
            'GET',
            null,
            { 'X-AUTH-TOKEN': userToken }
        );

        console.log('loadUserVehicles: Raw Vehicles Data from API:', vehiclesData);

        // Hide the loading message once data is fetched
        hideMessage(loadingMessageDisplay);

        // Check if vehicles data was successfully retrieved and is not empty
        if (vehiclesData && vehiclesData.length > 0) {
            // Iterate over each vehicle data and create a car card element for display
            vehiclesData.forEach(data => {
                const vehicle = new Car(data);
                const vehicleCardElement = vehicle.toCarCardElement(displayMessage, deleteVehicle);
                vehicleListContainer.appendChild(vehicleCardElement);
            });
            displayMessage(`Chargement de ${vehiclesData.length} véhicule(s) réussi.`, 'success');
        } else {
            // Display a message if no vehicles are registered
            displayMessage("Vous n'avez pas encore de véhicule enregistré.", 'info');
        }

    } catch (error) {
        // Hide loading message and log the error in case of failure
        hideMessage(loadingMessageDisplay);
        console.error("Erreur lors du chargement des véhicules de l'utilisateur:", error);
        // Handle specific unauthorized errors (e.g., expired token) by redirecting to login
        if (error.statusCode === 401 || error.message.includes('Missing credentials') || error.message.includes('Unauthorized')) {
            displayMessage("Votre session a expiré ou n'est plus valide. Veuillez vous reconnecter.", 'danger');
            setTimeout(() => { window.location.href = '/login'; }, 3000);
        } else {
            // Display a generic error message for other loading failures
            displayMessage(`Impossible de charger vos véhicules : ${error.message}`, 'danger');
        }
    }
};

// Initialize by loading user vehicles when the script runs
loadUserVehicles();

// Get the back button element
const backButton = document.getElementById('back-button');
// Add an event listener to handle the back button click
if (backButton) {
    backButton.addEventListener('click', (event) => {
        event.preventDefault(); // Prevent default link behavior
        window.history.back(); // Navigate to the previous page in history
    });
}