import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';
import Car from '../models/Car.js';

// --- DOM Elements ---
const vehicleListContainer = document.querySelector('.vehicule-list');
const messageDisplay = document.getElementById('messageDisplay');

// --- Message Display Utility ---
/**
 * Displays a temporary message in the message box with a type indicator.
 * @param {string} message - Message content to display.
 * @param {'success' | 'danger' | 'warning'} type - Bootstrap alert type for styling.
 * @param {HTMLElement} targetDisplay - L'élément DOM où afficher le message (par défaut messageDisplay).
 */
const displayMessage = (message, type, targetDisplay = messageDisplay) => {
    if (targetDisplay) {
        targetDisplay.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'd-none');
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
        }

        targetDisplay.innerHTML = `<i class="${iconClass} me-2"></i>${message}`;
        targetDisplay.classList.remove('d-none');

        setTimeout(() => {
            targetDisplay.classList.add('d-none');
            targetDisplay.innerHTML = '';
        }, 5000);
    } else {
        console.error('displayMessage: targetDisplay est null ou undefined. Message:', message);
    }
};

/**
 * Supprime un véhicule via l'API.
 * @param {number} vehicleId - L'ID du véhicule à supprimer.
 */
const deleteVehicle = async (vehicleId) => {
    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        displayMessage("Token utilisateur manquant. Veuillez vous reconnecter.", 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    try {
        const response = await fetchApi(
            `${API_BASE_URL}/api/car/${vehicleId}`,
            'DELETE',
            null,
            { 'X-AUTH-TOKEN': userToken }
        );

        if (response.message) {
            displayMessage(response.message, 'success');
        } else {
            displayMessage("Véhicule supprimé avec succès.", 'success');
        }

        loadUserVehicles(); // Recharger la liste des véhicules après suppression
    } catch (error) {
        console.error("Erreur lors de la suppression du véhicule:", error);
        displayMessage(`Erreur lors de la suppression du véhicule: ${error.message}`, 'danger');
    }
};


/**
 * Charge les véhicules de l'utilisateur depuis l'API et les affiche.
 */
const loadUserVehicles = async () => {
    console.log('loadUserVehicles: Attempting to load user vehicles.');
    if (!vehicleListContainer) {
        console.error('loadUserVehicles: .vehicule-list container not found.');
        return;
    }

    const userToken = localStorage.getItem('userToken');
    console.log('loadUserVehicles: User Token:', userToken ? 'Present' : 'Missing');

    if (!userToken) {
        displayMessage("Vous devez être connecté pour voir vos véhicules.", 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    try {
        console.log('loadUserVehicles: Fetching vehicles from API: /api/all-cars'); // CHANGÉ : Nouvelle route
        const vehiclesData = await fetchApi(
            `${API_BASE_URL}/api/all-cars`, // CHANGÉ : Utilise la nouvelle route
            'GET',
            null,
            { 'X-AUTH-TOKEN': userToken }
        );

        console.log('loadUserVehicles: Raw Vehicles Data from API:', vehiclesData);

        vehicleListContainer.innerHTML = ''; // Vide le conteneur avant d'ajouter de nouveaux véhicules

        if (vehiclesData && vehiclesData.length > 0) {
            vehiclesData.forEach(data => {
                const vehicle = new Car(data); // Crée une instance du modèle Car
                const vehicleCardElement = vehicle.toCarCardElement(displayMessage, deleteVehicle);
                vehicleListContainer.appendChild(vehicleCardElement);
            });
            displayMessage(`Chargement de ${vehiclesData.length} véhicule(s) réussi.`, 'success');
        } else {
            displayMessage("Vous n'avez pas encore de véhicule enregistré.", 'info');
        }

    } catch (error) {
        console.error("Erreur lors du chargement des véhicules de l'utilisateur:", error);
        if (error.message.includes('401') || error.message.includes('Unauthorized') || error.message.includes('Missing credentials')) {
            displayMessage("Votre session a expiré ou n'est plus valide. Veuillez vous reconnecter.", 'danger');
            setTimeout(() => { window.location.href = '/login'; }, 3000);
        } else {
            displayMessage(`Impossible de charger vos véhicules: ${error.message}`, 'danger');
        }
    }
};

loadUserVehicles();

const backButton = document.getElementById('back-button');
if (backButton) {
    backButton.addEventListener('click', (event) => {
        event.preventDefault();
        window.history.back(); // Revenir à la page précédente
    });
}
