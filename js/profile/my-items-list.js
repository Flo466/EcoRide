// src/pages/profile/my-items-list.js

import { fetchApi } from '../api/fetch.js'; // Remonte d'un dossier
import { API_BASE_URL } from '../config.js';
import Car from '../models/Car.js';
import { Carpooling } from '../models/Carpooling.js'; // Importe la classe Carpooling

// --- DOM Elements ---
// On utilise un sélecteur plus générique si les deux pages utilisent le même conteneur
const itemsListContainer = document.querySelector('.items-list-container'); // Renomme le conteneur dans tes HTML
const messageDisplay = document.getElementById('messageDisplay');
const loadingMessageDisplay = document.getElementById('loadingMessageDisplay');

// --- Message Display Utility (inchangé) ---
const displayMessage = (message, type, targetDisplay = messageDisplay) => {
    if (targetDisplay) {
        targetDisplay.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'alert-info', 'd-none');
        targetDisplay.innerHTML = '';

        let iconClass = '';
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

        targetDisplay.innerHTML = `<i class="${iconClass} me-2"></i>${message}`;
        targetDisplay.classList.remove('d-none');

        if (targetDisplay !== loadingMessageDisplay) {
            setTimeout(() => {
                targetDisplay.classList.add('d-none');
                targetDisplay.innerHTML = '';
            }, 5000);
        }
    } else {
        console.error('displayMessage: targetDisplay est null ou undefined. Message:', message);
    }
};

const hideMessage = (targetDisplay) => {
    if (targetDisplay) {
        targetDisplay.classList.add('d-none');
        targetDisplay.innerHTML = '';
    }
};

/**
 * Supprime un élément (véhicule ou covoiturage) via l'API.
 * @param {number} itemId - L'ID de l'élément à supprimer.
 * @param {'vehicle' | 'journey'} itemType - Le type d'élément ('vehicle' ou 'journey').
 * @param {Function} reloadFunction - La fonction à appeler pour recharger la liste après suppression.
 */
const deleteItem = async (itemId, itemType, reloadFunction) => {
    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        displayMessage("Jeton utilisateur manquant. Veuillez vous reconnecter.", 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    let endpoint = '';
    let successMessage = '';
    if (itemType === 'vehicle') {
        endpoint = `${API_BASE_URL}/api/car/${itemId}`;
        successMessage = "Véhicule supprimé avec succès.";
    } else if (itemType === 'journey') {
        endpoint = `${API_BASE_URL}/api/carpooling/delete/${itemId}`; // Adapte cette route à ton API pour supprimer un covoiturage
        successMessage = "Covoiturage supprimé avec succès.";
    } else {
        console.error('deleteItem: Type d\'élément inconnu :', itemType);
        displayMessage("Erreur: Type d'élément inconnu pour la suppression.", 'danger');
        return;
    }

    try {
        await fetchApi(endpoint, 'DELETE', null, { 'X-AUTH-TOKEN': userToken });
        displayMessage(successMessage, 'success');
        reloadFunction(); // Recharge la liste appropriée
    } catch (error) {
        console.error(`Erreur lors de la suppression du ${itemType}:`, error);
        displayMessage(`Erreur lors de la suppression du ${itemType} : ${error.message}`, 'danger');
    }
};


/**
 * Charge les éléments (véhicules ou covoiturages) de l'utilisateur depuis l'API et les affiche.
 * @param {'vehicles' | 'journeys'} type - Le type d'éléments à charger ('vehicles' ou 'journeys').
 */
export const loadUserItems = async (type) => {
    console.log(`loadUserItems: Attempting to load user ${type}.`);

    if (!itemsListContainer) {
        console.error(`loadUserItems: .items-list-container not found for ${type}.`);
        return;
    }

    displayMessage(`Chargement en cours...`, 'info', loadingMessageDisplay);
    itemsListContainer.innerHTML = ''; // Vide le contenu précédent

    const userToken = localStorage.getItem('userToken');
    console.log('loadUserItems: User Token:', userToken ? 'Present' : 'Missing');

    if (!userToken) {
        hideMessage(loadingMessageDisplay);
        displayMessage("Vous devez être connecté pour voir vos éléments.", 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    let endpoint = '';
    let emptyMessage = '';
    let frenchType = '';
    let ItemClass;
    let cardCreationMethod; // Méthode pour créer la carte (ex: toCarCardElement, toCarpoolingCardElement)
    let itemTypeForDelete; // 'vehicle' ou 'journey' pour la fonction deleteItem

    if (type === 'vehicles') {
        endpoint = `${API_BASE_URL}/api/all-cars`;
        emptyMessage = "Vous n'avez pas encore de véhicule enregistré.";
        ItemClass = Car;
        frenchType = 'véhicules'
        cardCreationMethod = 'toCarCardElement';
        itemTypeForDelete = 'vehicle';
    } else if (type === 'journeys') {
        endpoint = `${API_BASE_URL}/api/carpoolings/list-by-user`;
        emptyMessage = "Vous n'avez pas encore de voyage enregistré.";
        ItemClass = Carpooling;
        frenchType = 'voyages';
        cardCreationMethod = 'toJourneyCardElement';
        itemTypeForDelete = 'journey';
    } else {
        console.error('loadUserItems: Type de données inconnu :', type);
        hideMessage(loadingMessageDisplay);
        displayMessage("Erreur : Type de données inconnu à charger.", 'danger');
        return;
    }

    try {
        console.log(`loadUserItems: Fetching ${type} from API: ${endpoint}`);
        const itemsData = await fetchApi(endpoint, 'GET', null, { 'X-AUTH-TOKEN': userToken });

        console.log(`loadUserItems: Raw ${type} Data from API:`, itemsData);
        hideMessage(loadingMessageDisplay);

        if (itemsData && itemsData.length > 0) {
            itemsData.forEach(data => {
                const item = new ItemClass(data);
                // Passe la fonction de suppression appropriée
                const itemCardElement = item[cardCreationMethod](displayMessage, (id) => deleteItem(id, itemTypeForDelete, () => loadUserItems(type)));
                itemsListContainer.appendChild(itemCardElement);
            });
            displayMessage(`Chargement de ${itemsData.length} ${frenchType} réussi.`, 'success');
        } else {
            displayMessage(emptyMessage, 'info');
        }

    } catch (error) {
        hideMessage(loadingMessageDisplay);
        console.error(`Erreur lors du chargement des ${type} de l'utilisateur:`, error);
        if (error.statusCode === 401 || error.message.includes('Missing credentials') || error.message.includes('Unauthorized')) {
            displayMessage("Votre session a expiré ou n'est plus valide. Veuillez vous reconnecter.", 'danger');
            setTimeout(() => { window.location.href = '/login'; }, 3000);
        } else {
            displayMessage(`Impossible de charger vos ${frenchType} : ${error.message}`, 'danger');
        }
    }
};

const backButton = document.getElementById('back-button');
if (backButton) {
    backButton.addEventListener('click', (event) => {
        event.preventDefault();
        window.history.back();
    });
}