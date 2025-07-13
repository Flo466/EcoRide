// js/journey-form.js

import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';

// --- DOM Elements ---
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

// --- Global variable to store fetched vehicles ---
let userVehicles = [];

// --- Constants ---
const MESSAGES = {
    FORM_VALIDATION_ERROR: "Veuillez corriger les erreurs dans le formulaire.",
    PRICE_RANGE_ERROR: "Le prix doit être un chiffre entre 1 et 50.",
    FETCH_CITIES_ERROR: "Impossible de charger les villes. Veuillez réessayer.",
    FETCH_VEHICLES_ERROR: "Impossible de charger vos véhicules. Veuillez vous reconnecter.",
    NO_VEHICLES_REGISTERED: "Vous n'avez pas de véhicule enregistré. Veuillez d'abord en ajouter un.",
    JOURNEY_CREATED_SUCCESS: "Voyage proposé avec succès ! Redirection...",
    JOURNEY_CREATION_ERROR: (msg) => `Erreur lors de la proposition du voyage : ${msg}`,
    AUTH_REQUIRED: "Vous devez être connecté pour proposer un voyage. Redirection...",
    TOKEN_MISSING: "Jeton utilisateur manquant. Redirection vers la page de connexion.",
    SERVER_ERROR: "Erreur serveur. Veuillez réessayer plus tard."
};

// --- Fonctions utilitaires ---

/**
 * Affiche un message temporaire dans la boîte dédiée.
 * @param {string} message Le message texte à afficher.
 * @param {'success' | 'danger' | 'info'} type The type of message ('success' for green, 'danger' for red, 'info' for blue).
 * @param {HTMLElement} targetDisplay L'élément DOM où afficher le message (par défaut messageDisplay).
 */
const displayMessage = (message, type, targetDisplay = messageDisplay) => {
    if (targetDisplay) {
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
    } else {
        console.error('displayMessage: targetDisplay est null ou undefined. Message:', message);
    }
};

// --- Fonctions de chargement des données ---

/**
 * Récupère les villes depuis le fichier JSON local et peuple les éléments de sélection.
 */
const fetchAndPopulateCities = async () => {
    try {
        const response = await fetch('/js/cities/cities.json');
        if (!response.ok) {
            throw new Error(`Erreur HTTP ! Statut: ${response.status}`);
        }
        let cities = await response.json(); // Utilise 'let' car nous allons modifier ce tableau

        // --- Tri alphabétique des villes ---
        cities.sort((a, b) => {
            const nameA = a.nom.toUpperCase(); // Convertit en majuscules pour un tri insensible à la casse
            const nameB = b.nom.toUpperCase();
            if (nameA < nameB) {
                return -1;
            }
            if (nameA > nameB) {
                return 1;
            }
            return 0; // Noms identiques
        });

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
            console.warn("Aucune ville trouvée ou format de réponse invalide.");
            displayMessage(MESSAGES.FETCH_CITIES_ERROR, 'danger');
        }
    } catch (error) {
        console.error("Erreur lors du chargement des villes:", error);
        displayMessage(MESSAGES.FETCH_CITIES_ERROR, 'danger');
    }
};

/**
 * Récupère les véhicules enregistrés par l'utilisateur et peuple l'élément de sélection des véhicules.
 * Active/désactive le sélecteur et le bouton en fonction de la disponibilité des véhicules.
 * Stocke les véhicules récupérés dans `userVehicles`.
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

        userVehicles = vehicles;

        if (userVehicles && userVehicles.length > 0) {
            carSelect.disabled = false;
            enterVehicleFormBtn.classList.add('d-none');

            carSelect.innerHTML = '<option value="" disabled selected>Sélectionnez un véhicule</option>';

            userVehicles.forEach(car => {
                const option = document.createElement('option');
                option.value = car.id;
                const brandName = car.brand && car.brand.label ? car.brand.label : 'Marque inconnue';
                option.textContent = `${brandName} ${car.model} (${car.licencePlate})`;
                carSelect.appendChild(option);
            });
            console.log("Véhicules chargés et sélecteur activé.");
        } else {
            carSelect.disabled = true;
            carSelect.innerHTML = '<option value="" disabled selected>Aucun véhicule enregistré</option>';
            enterVehicleFormBtn.classList.remove('d-none');
            displayMessage(MESSAGES.NO_VEHICLES_REGISTERED, 'info');
            console.log("Aucun véhicule enregistré. Sélecteur désactivé, bouton 'Saisir un véhicule' activé.");
        }
    } catch (error) {
        console.error("Erreur lors du chargement des véhicules:", error);
        displayMessage(MESSAGES.FETCH_VEHICLES_ERROR, 'danger');
        carSelect.disabled = true;
        enterVehicleFormBtn.classList.remove('d-none');
        if (error.message.includes('401') || error.message.includes('Unauthorized')) {
            setTimeout(() => { window.location.href = '/login'; }, 3000);
        }
    }
};

// --- Gestion du formulaire ---

/**
 * Gère la soumission du formulaire pour ajouter un nouveau voyage.
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

    // --- Validation explicite du prix ---
    const priceValue = parseFloat(priceInput.value);
    if (isNaN(priceValue) || priceValue < 1 || priceValue > 50) {
        // Applique les classes d'erreur de Bootstrap pour le champ prix
        priceInput.classList.add('is-invalid');
        priceInput.nextElementSibling.textContent = MESSAGES.PRICE_RANGE_ERROR; // Met à jour le message d'erreur
        displayMessage(MESSAGES.PRICE_RANGE_ERROR, 'danger');
        return;
    } else {
        // Réinitialise les classes d'erreur si la validation passe
        priceInput.classList.remove('is-invalid');
        priceInput.classList.add('is-valid');
    }
    // --- Fin validation explicite du prix ---


    const selectedCarId = parseInt(carSelect.value, 10);
    const selectedCar = userVehicles.find(car => car.id === selectedCarId);

    if (!selectedCar) {
        displayMessage("Veuillez sélectionner un véhicule valide.", 'danger');
        return;
    }

    const availableSeats = selectedCar.seats || selectedCar.numberOfSeats;
    if (typeof availableSeats === 'undefined' || availableSeats === null) {
        console.error("Le véhicule sélectionné n'a pas de propriété 'seats' ou 'numberOfSeats'.", selectedCar);
        displayMessage("Impossible de déterminer le nombre de places pour le véhicule sélectionné. Veuillez réessayer.", 'danger');
        return;
    }

    // --- MODIFICATION ICI : Envoi des dates et heures séparément ---
    const departureDate = departureDateInput.value; // Format YYYY-MM-DD
    const departureTime = `${departureTimeInput.value}:00`; // Format HH:MM:SS (ajoute les secondes)
    const arrivalDate = arrivalDateInput.value; // Format YYYY-MM-DD
    const arrivalTime = `${arrivalTimeInput.value}:00`; // Format HH:MM:SS (ajoute les secondes)

    const journeyData = {
        departurePlace: departureCitySelect.value,
        arrivalPlace: arrivalCitySelect.value,
        departureDate: departureDate, // Champ séparé
        departureTime: departureTime, // Champ séparé
        arrivalDate: arrivalDate,     // Champ séparé
        arrivalTime: arrivalTime,     // Champ séparé
        car: selectedCarId,
        pricePerPassenger: priceValue,
        availableSeats: availableSeats
    };

    console.log('Données du voyage soumises:', journeyData);

    try {
        const response = await fetchApi(
            `${API_BASE_URL}/api/carpoolings`, // Correction de l'URL si nécessaire (pas de slash final)
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
        console.error("Erreur lors de la création du voyage:", error);
        const apiErrorMessage = error.message || MESSAGES.SERVER_ERROR;
        displayMessage(MESSAGES.JOURNEY_CREATION_ERROR(apiErrorMessage), 'danger');
    }
};

// --- Gestion des dates ---

/**
 * Définit la date minimale pour le départ et l'arrivée à aujourd'hui.
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
 * Écouteur d'événement pour le changement de la date de départ afin de s'assurer que la date d'arrivée n'est pas antérieure à la date de départ.
 */
const handleDepartureDateChange = () => {
    arrivalDateInput.min = departureDateInput.value;
    if (arrivalDateInput.value < departureDateInput.value) {
        arrivalDateInput.value = departureDateInput.value;
    }
};

/**
 * Écouteur d'événement pour le changement de la date d'arrivée afin de s'assurer que la date de départ n'est pas postérieure à la date d'arrivée.
 */
const handleArrivalDateChange = () => {
    if (departureDateInput.value > arrivalDateInput.value) {
        departureDateInput.value = arrivalDateInput.value;
    }
    departureDateInput.max = arrivalDateInput.value;
};


// --- Initialisation ---
// Utilisation d'une fonction asynchrone auto-exécutante pour remplacer DOMContentLoaded
(async () => {
    setMinDates();
    await fetchAndPopulateCities(); // Utilise await car c'est une opération asynchrone
    await fetchAndPopulateVehicles(); // Utilise await car c'est une opération asynchrone

    registrationForm.addEventListener('submit', handleJourneySubmission);

    cancelButton.addEventListener('click', () => {
        window.location.href = '/profile';
    });

    enterVehicleFormBtn.addEventListener('click', () => {
        window.location.href = '/car-form';
    });

    departureDateInput.addEventListener('change', handleDepartureDateChange);
    arrivalDateInput.addEventListener('change', handleArrivalDateChange);
})();
