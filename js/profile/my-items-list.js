import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';
import Car from '../models/Car.js';
import { Carpooling } from '../models/Carpooling.js';

const MESSAGES = {
    TOKEN_MISSING_GENERIC: "Jeton utilisateur manquant. Veuillez vous reconnecter.",
    UNKNOWN_ITEM_TYPE_DELETE: "Erreur : Type d'élément inconnu pour la suppression.",
    CONFIRM_DELETE_ITEM: (displayName) => `Êtes-vous sûr(e) de vouloir supprimer ${displayName} ?`,
    VEHICLE_DELETE_SUCCESS: "Véhicule supprimé avec succès.",
    JOURNEY_DELETE_SUCCESS: "Covoiturage supprimé avec succès.",
    DELETE_ERROR_FOREIGN_KEY: "Ce véhicule ne peut pas être supprimé car il est utilisé dans un ou plusieurs covoiturages.",
    SESSION_EXPIRED: "Votre session a expiré ou n'est plus valide. Veuillez vous reconnecter.",
    DELETE_ERROR_GENERIC: (message) => `Suppression impossible : ${message}`,
    LOADING_IN_PROGRESS: "Chargement en cours...",
    TOKEN_REQUIRED_VIEW_ITEMS: "Vous devez être connecté(e) pour voir vos éléments.",
    USER_ID_MISSING_JOURNEYS: "Impossible d'identifier l'utilisateur pour charger les voyages. Veuillez vous reconnecter.",
    UNKNOWN_DATA_TYPE_LOAD: "Erreur : Type de données inconnu à charger.",
    LOAD_SUCCESS: (count, type) => `Chargement de ${count} ${type} réussi.`,
    EMPTY_VEHICLES: "Vous n'avez pas encore de véhicule enregistré.",
    EMPTY_JOURNEYS: "Vous n'avez pas encore de voyage enregistré.",
    EMPTY_HISTORY: "Vous n'avez pas encore d'historique de covoiturage.",
    LOAD_ERROR_GENERIC: (type, message) => `Impossible de charger vos ${type} : ${message}`,
    CONFIRM_CANCEL_CARPOOLING: (displayName) => `Êtes-vous sûr(e) de vouloir annuler le covoiturage du ${displayName} ? Tous les passagers inscrits seront remboursés.`,
    CONFIRM_LEAVE_CARPOOLING: (displayName) => `Êtes-vous sûr(e) de vouloir annuler votre participation au covoiturage ${displayName} ? Vos crédits vous seront remboursés.`,
    CANCEL_CARPOOLING_SUCCESS: "Covoiturage annulé avec succès. Tous les passagers ont été remboursés.",
    LEAVE_CARPOOLING_SUCCESS: "Votre participation a été annulée avec succès. Vos crédits ont été remboursés.",
    ACTION_ERROR_GENERIC: (action, message) => `Impossible d'effectuer l'action "${action}" : ${message}`,
};

const itemsListContainer = document.querySelector('.items-list-container');
const messageDisplay = document.getElementById('messageDisplay');
const loadingMessageDisplay = document.getElementById('loadingMessageDisplay');
const backButton = document.getElementById('back-button');

const displayMessage = (message, type, targetDisplay = messageDisplay) => {
    if (!targetDisplay) return;
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

    if (targetDisplay !== loadingMessageDisplay) {
        setTimeout(() => {
            hideMessage(targetDisplay);
        }, 5000);
    }
};

const hideMessage = (targetDisplay) => {
    if (targetDisplay) {
        targetDisplay.classList.add('d-none');
        targetDisplay.innerHTML = '';
    }
};

const handleJourneyAction = async (journeyId, actionType, journeyDisplayName, reloadFunction) => {
    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        displayMessage(MESSAGES.TOKEN_MISSING_GENERIC, 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    let endpoint = '';
    let confirmMessage = '';
    let successMessage = '';
    let errorMessageAction = '';

    if (actionType === 'cancel_carpooling') {
        endpoint = `${API_BASE_URL}/api/carpoolings/${journeyId}/cancel`;
        confirmMessage = MESSAGES.CONFIRM_CANCEL_CARPOOLING(journeyDisplayName);
        successMessage = MESSAGES.CANCEL_CARPOOLING_SUCCESS;
        errorMessageAction = "annuler le covoiturage";
    } else if (actionType === 'leave_carpooling') {
        endpoint = `${API_BASE_URL}/api/carpoolings/${journeyId}/leave`;
        confirmMessage = MESSAGES.CONFIRM_LEAVE_CARPOOLING(journeyDisplayName);
        successMessage = MESSAGES.LEAVE_CARPOOLING_SUCCESS;
        errorMessageAction = "annuler ta participation";
    } else {
        displayMessage(MESSAGES.UNKNOWN_ITEM_TYPE_DELETE, 'danger');
        return;
    }

    if (!confirm(confirmMessage)) return;

    try {
        await fetchApi(endpoint, 'POST', null, { 'X-AUTH-TOKEN': userToken });
        displayMessage(successMessage, 'success');
        reloadFunction();
    } catch (error) {
        if (error.statusCode === 401 || error.message.includes('Missing credentials') || error.message.includes('Unauthorized') || error.message.includes('Authentication required')) {
            displayMessage(MESSAGES.SESSION_EXPIRED, 'danger');
            setTimeout(() => { window.location.href = '/login'; }, 4000);
        } else {
            displayMessage(MESSAGES.ACTION_ERROR_GENERIC(errorMessageAction, error.message), 'danger');
        }
    }
};

const deleteVehicle = async (itemId, itemDisplayName, reloadFunction) => {
    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        displayMessage(MESSAGES.TOKEN_MISSING_GENERIC, 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    const endpoint = `${API_BASE_URL}/api/car/${itemId}`;
    const successMessage = MESSAGES.VEHICLE_DELETE_SUCCESS;

    if (!confirm(MESSAGES.CONFIRM_DELETE_ITEM(itemDisplayName))) return;

    try {
        await fetchApi(endpoint, 'DELETE', null, { 'X-AUTH-TOKEN': userToken });
        displayMessage(successMessage, 'success');
        reloadFunction();
    } catch (error) {
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

export const loadUserItems = async (type) => {
    if (!itemsListContainer) return;

    displayMessage(MESSAGES.LOADING_IN_PROGRESS, 'info', loadingMessageDisplay);
    itemsListContainer.innerHTML = '';

    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        hideMessage(loadingMessageDisplay);
        displayMessage(MESSAGES.TOKEN_REQUIRED_VIEW_ITEMS, 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    const currentUserId = parseInt(localStorage.getItem('currentUserId'));
    if ((type === 'journeys' || type === 'history') && (isNaN(currentUserId) || currentUserId === null)) {
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

    if (type === 'vehicles') {
        endpoint = `${API_BASE_URL}/api/all-cars`;
        emptyMessage = MESSAGES.EMPTY_VEHICLES;
        ItemClass = Car;
        frenchType = 'véhicules';
        cardCreationMethod = (car) => car.toCarCardElement(displayMessage, (vehicleId) => {
            const itemDisplayName = car.getFullName();
            deleteVehicle(vehicleId, itemDisplayName, () => loadUserItems(type));
        });
    } else if (type === 'journeys' || type === 'history') {
        endpoint = `${API_BASE_URL}/api/carpoolings/list-by-user`;
        emptyMessage = type === 'journeys' ? MESSAGES.EMPTY_JOURNEYS : MESSAGES.EMPTY_HISTORY;
        ItemClass = Carpooling;
        frenchType = type === 'journeys' ? 'voyages' : 'historique';
        cardCreationMethod = (carpooling) => carpooling.toJourneyCardElement(currentUserId);
    } else {
        hideMessage(loadingMessageDisplay);
        displayMessage(MESSAGES.UNKNOWN_DATA_TYPE_LOAD, 'danger');
        return;
    }

    try {
        const itemsData = await fetchApi(endpoint, 'GET', null, { 'X-AUTH-TOKEN': userToken });
        hideMessage(loadingMessageDisplay);

        if (itemsData && itemsData.length > 0) {
            let itemsDisplayedCount = 0;
            itemsData.forEach(data => {
                const item = new ItemClass(data, currentUserId);
                let shouldDisplay = false;

                const isUserDriver = item.driver?.id === currentUserId;
                const userParticipation = data.carpoolingUsers?.find(cu => cu.user.id === currentUserId);
                const hasActiveParticipation = userParticipation && !userParticipation.isCancelled;
                const hasCancelledParticipation = userParticipation && userParticipation.isCancelled;

                if (type === 'vehicles') {
                    shouldDisplay = true;
                } else if (type === 'journeys') {
                    if (item.getStatus() === 'open' && (isUserDriver || hasActiveParticipation)) {
                        shouldDisplay = true;
                    }
                } else if (type === 'history') {
                    if (item.getStatus() === 'closed' || item.getStatus() === 'canceled' || hasCancelledParticipation) {
                        shouldDisplay = true;
                    }
                }

                if (shouldDisplay) {
                    const itemCardElement = cardCreationMethod(item);
                    if (itemCardElement) {
                        itemsListContainer.appendChild(itemCardElement);
                        itemsDisplayedCount++;

                        const actionButton = itemCardElement.querySelector('.action-button');
                        if (actionButton) {
                            actionButton.addEventListener('click', (event) => {
                                event.preventDefault();
                                const itemId = actionButton.dataset.id;
                                const actionType = actionButton.dataset.actionType;

                                let itemDisplayName = '';
                                if (type === 'vehicles') {
                                    itemDisplayName = `le véhicule "${item.brand?.name} ${item.model}"`;
                                    deleteVehicle(itemId, itemDisplayName, () => loadUserItems(type));
                                } else if (type === 'journeys') {
                                    const formattedDepartureDate = new Date(item.departureDate).toLocaleDateString('fr-FR');
                                    itemDisplayName = `${formattedDepartureDate} de ${item.departurePlace} vers ${item.arrivalPlace}`;
                                    handleJourneyAction(itemId, actionType, itemDisplayName, () => loadUserItems(type));
                                }
                            });
                        }
                    }
                }
            });

            if (itemsDisplayedCount > 0) {
                displayMessage(MESSAGES.LOAD_SUCCESS(itemsDisplayedCount, frenchType), 'success');
            } else {
                displayMessage(emptyMessage, 'info');
            }

        } else {
            displayMessage(emptyMessage, 'info');
        }
    } catch (error) {
        hideMessage(loadingMessageDisplay);
        if (error.statusCode === 401 || error.message.includes('Missing credentials') || error.message.includes('Unauthorized')) {
            displayMessage(MESSAGES.SESSION_EXPIRED, 'danger');
            setTimeout(() => { window.location.href = '/login'; }, 3000);
        } else {
            displayMessage(MESSAGES.LOAD_ERROR_GENERIC(frenchType, error.message), 'danger');
        }
    }
};

if (backButton) {
    backButton.addEventListener('click', (event) => {
        event.preventDefault();
        window.history.back();
    });
}