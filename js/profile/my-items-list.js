// src/js/profile/my-items-list.js

import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';
import Car from '../models/Car.js';
import { Carpooling } from '../models/Carpooling.js';

// =============================================================================
// I. Constants and DOM Elements
// =============================================================================

// User-facing messages (kept in French)
const MESSAGES = {
    TOKEN_MISSING_GENERIC: "Jeton utilisateur manquant. Veuillez vous reconnecter.",
    UNKNOWN_ITEM_TYPE_DELETE: "Erreur: Type d'élément inconnu pour la suppression.",
    CONFIRM_DELETE_ITEM: (displayName) => `Es-tu sûr de vouloir supprimer ${displayName} ?`,
    VEHICLE_DELETE_SUCCESS: "Véhicule supprimé avec succès.",
    JOURNEY_DELETE_SUCCESS: "Covoiturage supprimé avec succès.",
    DELETE_ERROR_FOREIGN_KEY: "Ce véhicule ne peut pas être supprimé car il est utilisé dans un ou plusieurs covoiturages.",
    SESSION_EXPIRED: "Votre session a expiré ou n'est plus valide. Veuillez vous reconnecter.",
    DELETE_ERROR_GENERIC: (message) => `Suppression impossible : ${message}`,
    LOADING_IN_PROGRESS: "Chargement en cours...",
    TOKEN_REQUIRED_VIEW_ITEMS: "Vous devez être connecté pour voir vos éléments.",
    USER_ID_MISSING_JOURNEYS: "Impossible d'identifier l'utilisateur pour charger les voyages. Veuillez vous reconnecter.",
    UNKNOWN_DATA_TYPE_LOAD: "Erreur : Type de données inconnu à charger.",
    LOAD_SUCCESS: (count, type) => `Chargement de ${count} ${type} réussi.`,
    EMPTY_VEHICLES: "Vous n'avez pas encore de véhicule enregistré.",
    EMPTY_JOURNEYS: "Vous n'avez pas encore de voyage enregistré.",
    LOAD_ERROR_GENERIC: (type, message) => `Impossible de charger vos ${type} : ${message}`,
};

// DOM elements
const itemsListContainer = document.querySelector('.items-list-container');
const messageDisplay = document.getElementById('messageDisplay');
const loadingMessageDisplay = document.getElementById('loadingMessageDisplay');
const backButton = document.getElementById('back-button');

// =============================================================================
// II. Message Display Utilities
// =============================================================================

/**
 * Displays a temporary message in the dedicated box.
 * @param {string} message - The text message to display.
 * @param {'success' | 'danger' | 'warning' | 'info'} type - The type of message.
 * @param {HTMLElement} targetDisplay - The DOM element where the message will be displayed (defaults to messageDisplay).
 */
const displayMessage = (message, type, targetDisplay = messageDisplay) => {
    if (!targetDisplay) {
        console.error('displayMessage: targetDisplay is null or undefined. Message:', message);
        return;
    }

    // Clear previous classes and content
    targetDisplay.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'alert-info', 'd-none');
    targetDisplay.innerHTML = '';

    let iconClass = '';
    switch (type) {
        case 'success':
            targetDisplay.classList.add('alert-success');
            iconClass = 'bi bi-check-circle-fill';
            break;
        case 'danger':
            targetDisplay.classList.add('alert-danger');
            iconClass = 'bi bi-x-circle-fill';
            break;
        case 'warning':
            targetDisplay.classList.add('alert-warning');
            iconClass = 'bi bi-exclamation-triangle-fill';
            break;
        case 'info':
            targetDisplay.classList.add('alert-info');
            iconClass = 'bi bi-info-circle-fill';
            break;
    }

    targetDisplay.innerHTML = `<i class="${iconClass} me-2"></i>${message}`;
    targetDisplay.classList.remove('d-none');

    // Hide message after 5 seconds, unless it's the loading message
    if (targetDisplay !== loadingMessageDisplay) {
        setTimeout(() => {
            hideMessage(targetDisplay);
        }, 5000);
    }
};

/**
 * Hides a message in a specified display container.
 * @param {HTMLElement} targetDisplay - The DOM element whose message should be hidden.
 */
const hideMessage = (targetDisplay) => {
    if (targetDisplay) {
        targetDisplay.classList.add('d-none');
        targetDisplay.innerHTML = '';
    }
};

// =============================================================================
// III. Item Management Functions
// =============================================================================

/**
 * Deletes an item (vehicle or journey) via API.
 * @param {number} itemId - The ID of the item to delete.
 * @param {'vehicle' | 'journey'} itemType - The type of item ('vehicle' or 'journey').
 * @param {string} itemDisplayName - The user-friendly name of the item for confirmation alerts.
 * @param {Function} reloadFunction - The function to call to reload the list after deletion.
 */
const deleteItem = async (itemId, itemType, itemDisplayName, reloadFunction) => {
    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        displayMessage(MESSAGES.TOKEN_MISSING_GENERIC, 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    let endpoint = '';
    let successMessage = '';
    if (itemType === 'vehicle') {
        endpoint = `${API_BASE_URL}/api/car/${itemId}`;
        successMessage = MESSAGES.VEHICLE_DELETE_SUCCESS;
    } else if (itemType === 'journey') {
        endpoint = `${API_BASE_URL}/api/carpoolings/${itemId}`;
        successMessage = MESSAGES.JOURNEY_DELETE_SUCCESS;
    } else {
        console.error('deleteItem: Unknown item type:', itemType);
        displayMessage(MESSAGES.UNKNOWN_ITEM_TYPE_DELETE, 'danger');
        return;
    }

    if (!confirm(MESSAGES.CONFIRM_DELETE_ITEM(itemDisplayName))) {
        return;
    }

    try {
        await fetchApi(endpoint, 'DELETE', null, { 'X-AUTH-TOKEN': userToken });
        displayMessage(successMessage, 'success');
        reloadFunction(); // Reload the list on success
    } catch (error) {
        console.error(`Error deleting ${itemType}:`, error);

        if (error.statusCode === 409 && error.message.includes('foreign key constraint fails')) {
            displayMessage(MESSAGES.DELETE_ERROR_FOREIGN_KEY, 'warning');
        } else if (error.statusCode === 401 || error.message.includes('Missing credentials') || error.message.includes('Unauthorized') || error.message.includes('Authentication required')) {
            displayMessage(MESSAGES.SESSION_EXPIRED, 'danger');
            setTimeout(() => { window.location.href = '/login'; }, 4000);
        } else {
            displayMessage(MESSAGES.DELETE_ERROR_GENERIC(error.message), 'danger');
        }
    }
};

/**
 * Loads user's items (vehicles or journeys) from the API and displays them.
 * @param {'vehicles' | 'journeys'} type - The type of items to load.
 */
export const loadUserItems = async (type) => {
    if (!itemsListContainer) {
        console.error(`loadUserItems: .items-list-container not found for ${type}.`);
        return;
    }

    displayMessage(MESSAGES.LOADING_IN_PROGRESS, 'info', loadingMessageDisplay);
    itemsListContainer.innerHTML = ''; // Clear container before loading

    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        hideMessage(loadingMessageDisplay);
        displayMessage(MESSAGES.TOKEN_REQUIRED_VIEW_ITEMS, 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    const currentUserId = parseInt(localStorage.getItem('currentUserId'));
    if (type === 'journeys' && (isNaN(currentUserId) || currentUserId === null)) {
        console.error('loadUserItems: currentUserId is missing or invalid for loading journeys. Value:', currentUserId);
        hideMessage(loadingMessageDisplay);
        displayMessage(MESSAGES.USER_ID_MISSING_JOURNEYS, 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    let endpoint = '';
    let emptyMessage = '';
    let frenchType = '';
    let ItemClass;
    let cardCreationMethod;
    let itemTypeForDelete;

    // Configure loading parameters based on item type.
    if (type === 'vehicles') {
        endpoint = `${API_BASE_URL}/api/all-cars`;
        emptyMessage = MESSAGES.EMPTY_VEHICLES;
        ItemClass = Car;
        frenchType = 'véhicules';
        cardCreationMethod = 'toCarCardElement';
        itemTypeForDelete = 'vehicle';
    } else if (type === 'journeys') {
        endpoint = `${API_BASE_URL}/api/carpoolings/list-by-user`;
        emptyMessage = MESSAGES.EMPTY_JOURNEYS;
        ItemClass = Carpooling;
        frenchType = 'voyages';
        cardCreationMethod = 'toJourneyCardElement';
        itemTypeForDelete = 'journey';
    } else {
        console.error('loadUserItems: Unknown data type:', type);
        hideMessage(loadingMessageDisplay);
        displayMessage(MESSAGES.UNKNOWN_DATA_TYPE_LOAD, 'danger');
        return;
    }

    try {
        const itemsData = await fetchApi(endpoint, 'GET', null, { 'X-AUTH-TOKEN': userToken });
        hideMessage(loadingMessageDisplay);

        if (itemsData && itemsData.length > 0) {
            itemsData.forEach(data => {
                // Pass currentUserId only if it's for journey and it's a valid ID for the Carpooling constructor
                const userIdToPass = (type === 'journeys' && !isNaN(currentUserId)) ? currentUserId : null;

                const item = new ItemClass(data, userIdToPass);
                const itemCardElement = item[cardCreationMethod](); // Dynamically call card creation method

                itemsListContainer.appendChild(itemCardElement);

                // Attach delete event listener to the button on the created card.
                const deleteButton = itemCardElement.querySelector('.delete-item-btn');
                if (deleteButton) {
                    deleteButton.addEventListener('click', (event) => {
                        event.preventDefault();
                        const itemId = deleteButton.dataset.id;
                        const typeToDelete = deleteButton.dataset.type;

                        // Determine display name for confirmation message.
                        let itemDisplayName = '';
                        if (typeToDelete === 'vehicle') {
                            itemDisplayName = `le véhicule "${item.brand.name} ${item.model}"`;
                        } else if (typeToDelete === 'journey') {
                            const formattedDepartureDate = new Date(item.departureDate).toLocaleDateString('fr-FR');
                            const formattedDepartureTime = item.departureTime.substring(0, 5);
                            itemDisplayName = `le covoiturage du ${formattedDepartureDate} de ${item.departurePlace} vers ${item.arrivalPlace}`;
                        }
                        deleteItem(itemId, typeToDelete, itemDisplayName, () => loadUserItems(type));
                    });
                }
            });
            displayMessage(MESSAGES.LOAD_SUCCESS(itemsData.length, frenchType), 'success');
        } else {
            displayMessage(emptyMessage, 'info');
        }
    } catch (error) {
        hideMessage(loadingMessageDisplay);
        console.error(`Error loading user ${type}:`, error);
        if (error.statusCode === 401 || error.message.includes('Missing credentials') || error.message.includes('Unauthorized')) {
            displayMessage(MESSAGES.SESSION_EXPIRED, 'danger');
            setTimeout(() => { window.location.href = '/login'; }, 3000);
        } else {
            displayMessage(MESSAGES.LOAD_ERROR_GENERIC(frenchType, error.message), 'danger');
        }
    }
};

// =============================================================================
// IV. Event Listeners and Initialization
// =============================================================================

// Event listener for the back button.
if (backButton) {
    backButton.addEventListener('click', (event) => {
        event.preventDefault();
        window.history.back();
    });
}