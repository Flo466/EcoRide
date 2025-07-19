// =============================================================================
// I. Imports and Constants
// =============================================================================

import { fetchApi } from './api/fetch.js';
import { API_BASE_URL } from './config.js';
import { Carpooling } from './models/Carpooling.js';
import { Review } from './models/Review.js';

// User-facing messages
const MESSAGES = {
    ERROR_MISSING_ELEMENTS: '<strong>Erreur :</strong> Impossible de charger la page correctement. Éléments manquants.',
    ERROR_NO_CARPOOLING_ID: '<strong>Erreur :</strong> Aucun covoiturage spécifié dans l\'URL.',
    ERROR_FETCH_CARPOOLING: '<strong>Erreur :</strong> Impossible de charger les détails du covoiturage.',
    ERROR_FETCH_REVIEWS: 'Erreur lors du chargement des avis.',
    INFO_NO_REVIEWS: 'Aucun avis disponible pour ce conducteur pour le moment.',
    INFO_NO_APPROVED_REVIEWS: 'Aucun avis approuvé disponible pour ce conducteur pour le moment.',
    INFO_NO_DRIVER_INFO: 'Aucune information sur le conducteur disponible.',
    INFO_NO_REVIEWS_NO_DRIVER: 'Aucun avis à afficher car pas de conducteur.',
    INFO_CONNECT_TO_PARTICIPATE: 'Connectez-vous pour participer à ce covoiturage.',
    WARN_DRIVER_CANNOT_PARTICIPATE: 'Vous ne pouvez pas participer à votre propre covoiturage.',
    INFO_ALREADY_PARTICIPATING: 'Vous êtes déjà inscrit à ce covoiturage.',
    WARN_CARPOOLING_FULL: 'Ce covoiturage est complet.',
    WARN_INSUFFICIENT_CREDITS: (creditsNeeded) => `Vous n'avez pas assez de crédit pour participer. Il vous faut ${creditsNeeded} crédits.`,
    CONFIRM_PARTICIPATION: (creditsNeeded) => `Voulez-vous vraiment participer à ce covoiturage pour ${creditsNeeded} crédits ?`,
    INFO_PARTICIPATION_IN_PROGRESS: 'Participation en cours...',
    SUCCESS_PARTICIPATION: 'Vous avez bien participé à ce covoiturage ! 🎉',
    ERROR_PARTICIPATION_FAILED: (msg) => `<strong>Erreur :</strong> ${msg || 'Impossible de participer au covoiturage.'}`,
    ERROR_NETWORK_PARTICIPATION: '<strong>Erreur réseau :</strong> Impossible de participer au covoiturage. Veuillez vérifier votre connexion.',
    INFO_PARTICIPATION_CANCELLED: 'Participation annulée.',
    ERROR_GENERIC: 'Une erreur est survenue. Veuillez rafraîchir la page.',
    BUTTON_CONNECT_SIGNUP: 'Se connecter / S\'inscrire pour participer',
    BUTTON_YOU_ARE_DRIVER: 'Vous êtes le conducteur',
    BUTTON_ALREADY_PARTICIPATING: 'Vous participez déjà',
    BUTTON_FULL: 'Complet',
    BUTTON_INSUFFICIENT_CREDITS: (creditsNeeded) => `Crédit insuffisant (${creditsNeeded} requis)`,
    BUTTON_PARTICIPATE: (creditsNeeded) => `Participer (${creditsNeeded} crédits)`,
    WARN_BANNER_PLACEHOLDER_MISSING: 'Le placeholder #detail-message-banner-placeholder n\'a pas été trouvé. La bannière est insérée en haut du corps.'
};

// DOM Elements
const carpoolingContainer = document.getElementById('detail-carpooling-container');
const userContainer = document.getElementById('detail-user-container');
const reviewContainer = document.getElementById('detail-review-container');
const participateButton = document.getElementById('participate-button');
const detailMessageBannerPlaceholder = document.getElementById('detail-message-banner-placeholder');
const backButton = document.getElementById('back-button');

// Global message banner element
const mainMessageBanner = document.createElement('div');
mainMessageBanner.id = 'detail-page-message-banner';
mainMessageBanner.className = 'alert text-center';
mainMessageBanner.setAttribute('role', 'alert');
mainMessageBanner.style.display = 'none';

// Global state variables
let currentCarpoolingInstance = null;
let currentUser = null;
let isAuthenticated = false;

// =============================================================================
// II. Utility and Initialization Functions
// =============================================================================

/**
 * Updates the content and visibility of the main message banner.
 */
const updateBanner = (message, type = 'info', isVisible = true, autoHide = true) => {
    mainMessageBanner.innerHTML = message;
    mainMessageBanner.className = `alert alert-${type} text-center mt-3`;
    mainMessageBanner.style.display = isVisible ? 'block' : 'none';

    if (isVisible && autoHide && type !== 'danger') {
        setTimeout(() => {
            mainMessageBanner.style.display = 'none';
        }, 5000);
    }
};

/** Initializes the placement of the message banner in the DOM. */
const initializeMessageBanner = () => {
    if (detailMessageBannerPlaceholder) {
        detailMessageBannerPlaceholder.appendChild(mainMessageBanner);
    } else {
        document.body.prepend(mainMessageBanner);
        console.warn(MESSAGES.WARN_BANNER_PLACEHOLDER_MISSING);
    }
};

/** Sets up the event listener for the back button. */
const setupBackButton = () => {
    if (backButton) {
        backButton.addEventListener('click', (e) => {
            e.preventDefault();
            if (document.referrer && document.referrer.includes(window.location.host)) {
                window.history.back();
            } else {
                window.location.href = '/covoiturages';
            }
        });
    }
};

/**
 * Checks for the presence of required DOM elements.
 * @returns {boolean} True if all required elements are found, false otherwise.
 */
const checkRequiredDOMElements = () => {
    if (!carpoolingContainer || !userContainer || !reviewContainer || !participateButton) {
        console.error('One or more containers (carpooling, user, review) or the "Participate" button not found!');
        updateBanner(MESSAGES.ERROR_MISSING_ELEMENTS, 'danger', true, false);
        return false;
    }
    return true;
};

/**
 * Extracts the carpooling ID from the URL query parameters.
 * @returns {string|null} The carpooling ID or null if not found.
 */
const getCarpoolingIdFromUrl = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    if (!id) {
        carpoolingContainer.innerHTML = `<p class="text-danger">Aucun covoiturage sélectionné.</p>`;
        updateBanner(MESSAGES.ERROR_NO_CARPOOLING_ID, 'danger', true, false);
        return null;
    }
    return id;
};

/**
 * Checks user authentication status and retrieves user data if authenticated.
 */
const checkAuthentication = async () => {
    const token = localStorage.getItem('userToken');
    if (token) {
        try {
            const authCheckResponse = await fetchApi(
                `${API_BASE_URL}/api/account/me`,
                'GET',
                null,
                { 'X-AUTH-TOKEN': token }
            );
            if (authCheckResponse && authCheckResponse.id) {
                isAuthenticated = true;
                currentUser = authCheckResponse;
            } else {
                localStorage.removeItem('userToken');
            }
        } catch (error) {
            localStorage.removeItem('userToken');
            console.error("Authentication check failed:", error);
        }
    }
};

// =============================================================================
// III. Main Loading and Display Logic
// =============================================================================

/** Fetches carpooling data, renders it, and updates the participate button state. */
async function fetchCarpoolingDataAndRender() {
    const id = getCarpoolingIdFromUrl();
    if (!id) return;

    try {
        const result = await fetchApi(`${API_BASE_URL}/api/carpoolings/${id}`);
        currentCarpoolingInstance = new Carpooling(result, currentUser ? currentUser.id : null);

        carpoolingContainer.innerHTML = '';
        userContainer.innerHTML = '';
        reviewContainer.innerHTML = '';

        carpoolingContainer.appendChild(currentCarpoolingInstance.toDetailCarpooling());

        if (currentCarpoolingInstance.driver) {
            userContainer.appendChild(currentCarpoolingInstance.toDriverCardElement());
            reviewContainer.innerHTML = `<h4 class="ms-3 mb-3">Avis</h4><div id="driver-reviews-list"></div>`;

            const driverReviewsList = document.getElementById('driver-reviews-list');
            try {
                const reviewsApiUrl = `${API_BASE_URL}/api/review/user/${currentCarpoolingInstance.driver.id}/target`;
                const reviewsResult = await fetchApi(reviewsApiUrl);

                if (reviewsResult && reviewsResult.length > 0) {
                    let hasApprovedReviews = false;
                    reviewsResult.forEach(reviewData => {
                        if (reviewData.status.toLowerCase() === 'approved') {
                            const review = new Review(reviewData);
                            driverReviewsList.appendChild(review.toReviewCardElement());
                            hasApprovedReviews = true;
                        }
                    });

                    if (!hasApprovedReviews) {
                        const noApprovedReviewsInfo = document.createElement('div');
                        noApprovedReviewsInfo.className = 'p-4';
                        noApprovedReviewsInfo.innerHTML = `<p class="text-info mb-0">${MESSAGES.INFO_NO_APPROVED_REVIEWS}</p>`;
                        driverReviewsList.appendChild(noApprovedReviewsInfo);
                    }
                } else {
                    const noReviewsInfo = document.createElement('div');
                    noReviewsInfo.className = 'p-4';
                    noReviewsInfo.innerHTML = `<p class="text-info mb-0">${MESSAGES.INFO_NO_REVIEWS}</p>`;
                    driverReviewsList.appendChild(noReviewsInfo);
                }
            } catch (reviewsError) {
                const reviewsErrorInfo = document.createElement('div');
                reviewsErrorInfo.className = 'p-4';
                reviewsErrorInfo.innerHTML = `<p class="text-danger mb-0">${MESSAGES.ERROR_FETCH_REVIEWS}</p>`;
                driverReviewsList.appendChild(reviewsErrorInfo);
            }
        } else {
            userContainer.innerHTML = `<div class="p-4"><p class="text-danger mb-0">${MESSAGES.INFO_NO_DRIVER_INFO}</p></div>`;
            reviewContainer.innerHTML = `<div class="p-4"><p class="text-info mb-0">${MESSAGES.INFO_NO_REVIEWS_NO_DRIVER}</p></div>`;
        }

        updateParticipateButton(currentCarpoolingInstance);

    } catch (error) {
        console.error("Error fetching carpooling data:", error);
        carpoolingContainer.innerHTML = `<p class="text-danger">Erreur lors du chargement du covoiturage.</p>`;
        updateBanner(MESSAGES.ERROR_FETCH_CARPOOLING, 'danger', true, false);
    }
}

// =============================================================================
// IV. Participation Button Management
// =============================================================================

/**
 * Updates the state and text of the participate button based on carpooling and user status.
 */
function updateParticipateButton(carpooling) {
    if (!participateButton) return;

    const availableSeats = carpooling.availableSeats;
    const requiredCredits = carpooling.pricePerPerson;
    const passengers = carpooling.passengers;

    // Reset button state
    participateButton.disabled = false;
    participateButton.classList.remove('btn-secondary', 'btn-primary');
    participateButton.removeEventListener('click', handleParticipateClick);
    participateButton.onclick = null;

    if (!isAuthenticated) {
        participateButton.textContent = MESSAGES.BUTTON_CONNECT_SIGNUP;
        participateButton.classList.add('btn-secondary');
        participateButton.onclick = () => { window.location.href = '/login'; };
        updateBanner(MESSAGES.INFO_CONNECT_TO_PARTICIPATE, 'info', true, true);
        return;
    }

    if (carpooling.isCurrentUserDriver) {
        participateButton.textContent = MESSAGES.BUTTON_YOU_ARE_DRIVER;
        participateButton.disabled = true;
        participateButton.classList.add('btn-secondary');
        updateBanner(MESSAGES.WARN_DRIVER_CANNOT_PARTICIPATE, 'warning', true, false);
        return;
    }

    const isAlreadyPassenger = passengers.some(passenger => passenger.id === currentUser.id);
    if (isAlreadyPassenger) {
        participateButton.textContent = MESSAGES.BUTTON_ALREADY_PARTICIPATING;
        participateButton.disabled = true;
        participateButton.classList.add('btn-secondary');
        updateBanner(MESSAGES.INFO_ALREADY_PARTICIPATING, 'info', true, false);
        return;
    }

    if (availableSeats <= 0) {
        participateButton.textContent = MESSAGES.BUTTON_FULL;
        participateButton.disabled = true;
        participateButton.classList.add('btn-secondary');
        updateBanner(MESSAGES.WARN_CARPOOLING_FULL, 'warning', true, false);
        return;
    }

    if (currentUser && currentUser.credits < requiredCredits) {
        participateButton.textContent = MESSAGES.BUTTON_INSUFFICIENT_CREDITS(requiredCredits);
        participateButton.disabled = true;
        participateButton.classList.add('btn-secondary');
        updateBanner(MESSAGES.WARN_INSUFFICIENT_CREDITS(requiredCredits), 'warning', true, false);
        return;
    }

    // If all checks pass, enable the button for participation
    participateButton.textContent = MESSAGES.BUTTON_PARTICIPATE(requiredCredits);
    participateButton.classList.add('btn-primary');
    participateButton.addEventListener('click', handleParticipateClick);
    updateBanner('', 'info', false);
}

/** Handles the click event for the participate button, initiating the participation process. */
async function handleParticipateClick() {
    if (!currentCarpoolingInstance || !currentUser) {
        updateBanner(MESSAGES.ERROR_GENERIC, 'danger', true, false);
        return;
    }

    const requiredCredits = currentCarpoolingInstance.pricePerPerson;

    // Front-end double-check to prevent unnecessary API calls and improve UX
    if (currentCarpoolingInstance.isCurrentUserDriver ||
        currentCarpoolingInstance.passengers.some(passenger => passenger.id === currentUser.id) ||
        currentCarpoolingInstance.availableSeats <= 0 ||
        currentUser.credits < requiredCredits) {
        updateParticipateButton(currentCarpoolingInstance);
        return;
    }

    const confirmation = confirm(MESSAGES.CONFIRM_PARTICIPATION(requiredCredits));

    if (confirmation) {
        updateBanner(MESSAGES.INFO_PARTICIPATION_IN_PROGRESS, 'info', true, false);
        participateButton.disabled = true;

        try {
            const response = await fetchApi(
                `${API_BASE_URL}/api/carpoolings/${currentCarpoolingInstance.id}/participate`,
                'POST',
                { userId: currentUser.id },
                { 'X-AUTH-TOKEN': localStorage.getItem('userToken') }
            );

            if (response.success) {
                updateBanner(MESSAGES.SUCCESS_PARTICIPATION, 'success', true, true);
                if (currentUser && response.newCredits !== undefined) {
                    currentUser.credits = response.newCredits;
                }
                await fetchCarpoolingDataAndRender();
            } else {
                updateBanner(MESSAGES.ERROR_PARTICIPATION_FAILED(response.message), 'danger', true, false);
                participateButton.disabled = false;
            }
        } catch (error) {
            console.error("Network or API call error during participation:", error);
            let errorMessage = MESSAGES.ERROR_NETWORK_PARTICIPATION;
            if (error instanceof Error && error.message) {
                errorMessage = MESSAGES.ERROR_PARTICIPATION_FAILED(error.message);
            } else if (error && error.message) {
                errorMessage = MESSAGES.ERROR_PARTICIPATION_FAILED(error.message);
            }
            updateBanner(errorMessage, 'danger', true, false);
            participateButton.disabled = false;
        }
    } else {
        updateBanner(MESSAGES.INFO_PARTICIPATION_CANCELLED, 'info', true, true);
        participateButton.disabled = false;
    }
}

// =============================================================================
// V. Main Execution
// =============================================================================

/** Self-executing anonymous async function for initial setup and data loading. */
(async () => {
    initializeMessageBanner();
    setupBackButton();

    if (!checkRequiredDOMElements()) {
        return;
    }
    await checkAuthentication();
    await fetchCarpoolingDataAndRender();
})();