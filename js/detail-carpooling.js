import { fetchApi } from './api/fetch.js';
import { API_BASE_URL } from './config.js';
import { Carpooling } from './models/Carpooling.js';
import { Review } from './models/Review.js';

// =============================================================================
// I. Constants and DOM Elements
// =============================================================================

// Messages for the banner and user interactions
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
    CONFIRM_PARTICIPATION: (creditsNeeded) => `Voulez-vous vraiment participer à ce covoiturage pour ${creditsNeeded} crédits ? Cette action est irréversible.`,
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


// Retrieving DOM elements
const carpoolingContainer = document.getElementById('detail-carpooling-container');
const userContainer = document.getElementById('detail-user-container');
const reviewContainer = document.getElementById('detail-review-container');
const participateButton = document.getElementById('participate-button');
const detailMessageBannerPlaceholder = document.getElementById('detail-message-banner-placeholder');
const backButton = document.getElementById('back-button');

// Message banner element
const mainMessageBanner = document.createElement('div');
mainMessageBanner.id = 'detail-page-message-banner';
mainMessageBanner.className = 'alert text-center';
mainMessageBanner.setAttribute('role', 'alert');
mainMessageBanner.style.display = 'none';

// Global variables for carpooling and user state
let currentCarpoolingInstance = null;
let currentUser = null;
let isAuthenticated = false;

// =============================================================================
// II. Utility and Initialization Functions
// =============================================================================

/**
 * Updates the message banner on the page.
 * @param {string} message - The message to display.
 * @param {'info'|'success'|'warning'|'danger'} type - The alert type (bootstrap).
 * @param {boolean} isVisible - Indicates if the banner should be visible.
 * @param {boolean} autoHide - Indicates if the banner should disappear after 5 seconds.
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

/**
 * Initializes the message banner by placing it in the DOM.
 */
const initializeMessageBanner = () => {
    if (detailMessageBannerPlaceholder) {
        detailMessageBannerPlaceholder.appendChild(mainMessageBanner);
    } else {
        document.body.prepend(mainMessageBanner);
        console.warn(MESSAGES.WARN_BANNER_PLACEHOLDER_MISSING);
    }
};

/**
 * Sets up the back button functionality.
 */
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
 * Checks for the presence of necessary DOM elements for proper page functioning.
 * @returns {boolean} - True if all elements are present, false otherwise.
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
 * Retrieves the carpooling ID from the URL.
 * @returns {string|null} - The carpooling ID or null if not found.
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
 * Checks user authentication via the local token.
 */
const checkAuthentication = async () => {
    const token = localStorage.getItem('userToken');
    console.log('DEBUG: Token in localStorage at start:', token ? 'Present (starts with ' + token.substring(0, 10) + '...)' : 'Absent');

    if (token) {
        try {
            console.log('DEBUG: Calling /api/account/me API to check token...');
            const authCheckResponse = await fetchApi(
                `${API_BASE_URL}/api/account/me`,
                'GET',
                null,
                { 'X-AUTH-TOKEN': token }
            );
            console.log('DEBUG: Response from /api/account/me:', authCheckResponse);

            if (authCheckResponse && authCheckResponse.id) {
                isAuthenticated = true;
                currentUser = authCheckResponse;
                console.log('DEBUG: User logged in. currentUser:', currentUser);
                console.log('DEBUG: User credits:', currentUser.credits);
            } else {
                localStorage.removeItem('userToken');
                console.log('DEBUG: Invalid or expired token, cleared from localStorage.');
            }
        } catch (error) {
            console.error('DEBUG: Error during authentication check:', error);
            localStorage.removeItem('userToken');
            console.log('DEBUG: Network error or invalid token, cleared from localStorage.');
        }
    } else {
        console.log('DEBUG: No userToken found in localStorage, user not logged in.');
    }
};

// =============================================================================
// III. Main Loading and Display Logic
// =============================================================================

/**
 * Fetches carpooling data and renders it on the page.
 * Also handles displaying driver information and reviews.
 */
async function fetchCarpoolingDataAndRender() {
    console.log('DEBUG: Starting fetchCarpoolingDataAndRender()...');
    const id = getCarpoolingIdFromUrl();
    if (!id) return; // If no ID, stop execution

    try {
        const result = await fetchApi(`${API_BASE_URL}/api/carpoolings/${id}`);
        console.log('DEBUG: Raw carpooling data received from API:', result);

        currentCarpoolingInstance = new Carpooling(result, currentUser ? currentUser.id : null);
        console.log('DEBUG: Carpooling instance created:', currentCarpoolingInstance);

        carpoolingContainer.innerHTML = '';
        userContainer.innerHTML = '';
        reviewContainer.innerHTML = '';

        carpoolingContainer.appendChild(currentCarpoolingInstance.toDetailCarpooling());

        if (currentCarpoolingInstance.driver) {
            userContainer.appendChild(currentCarpoolingInstance.toDriverCardElement());
            reviewContainer.innerHTML = `<h4 class="ms-3 mb-3">Avis</h4><div id="driver-reviews-list"></div>`;

            const driverReviewsList = document.getElementById('driver-reviews-list');
            try {
                console.log('DEBUG: Attempting to retrieve driver reviews...');
                const reviewsApiUrl = `${API_BASE_URL}/api/review/user/${currentCarpoolingInstance.driver.id}/target`;
                const reviewsResult = await fetchApi(reviewsApiUrl);
                console.log('DEBUG: Driver reviews received:', reviewsResult);

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
                console.error('DEBUG: Error loading driver reviews:', reviewsError);
                const reviewsErrorInfo = document.createElement('div');
                reviewsErrorInfo.className = 'p-4';
                reviewsErrorInfo.innerHTML = `<p class="text-danger mb-0">${MESSAGES.ERROR_FETCH_REVIEWS}</p>`;
                driverReviewsList.appendChild(reviewsErrorInfo);
            }
        } else {
            userContainer.innerHTML = `<div class="p-4"><p class="text-danger mb-0">${MESSAGES.INFO_NO_DRIVER_INFO}</p></div>`;
            reviewContainer.innerHTML = `<div class="p-4"><p class="text-info mb-0">${MESSAGES.INFO_NO_REVIEWS_NO_DRIVER}</p></div>`;
        }

        console.log('DEBUG: Calling updateParticipateButton() with Carpooling instance...');
        updateParticipateButton(currentCarpoolingInstance);

    } catch (error) {
        console.error('DEBUG: Error loading or rendering carpooling:', error);
        carpoolingContainer.innerHTML = `<p class="text-danger">Erreur lors du chargement du covoiturage.</p>`;
        updateBanner(MESSAGES.ERROR_FETCH_CARPOOLING, 'danger', true, false);
    }
}

// =============================================================================
// IV. Participation Button Management
// =============================================================================

/**
 * Updates the state and text of the participation button based on the carpooling and logged-in user.
 * @param {Carpooling} carpooling - The current carpooling instance.
 */
function updateParticipateButton(carpooling) {
    console.log('DEBUG: In updateParticipateButton. isAuthenticated:', isAuthenticated, 'currentUser:', currentUser);
    console.log('DEBUG: Carpooling instance received:', carpooling);

    if (!participateButton) return;

    const availableSeats = carpooling.availableSeats;
    const requiredCredits = carpooling.pricePerPerson;
    const passengers = carpooling.passengers;

    console.log('DEBUG: Available seats (from Carpooling instance):', availableSeats, 'Required credits (from Carpooling instance):', requiredCredits);

    // Reset button state
    participateButton.disabled = false;
    participateButton.classList.remove('btn-secondary', 'btn-primary');
    participateButton.removeEventListener('click', handleParticipateClick);
    participateButton.onclick = null; // Remove any previous onclick function

    if (!isAuthenticated) {
        console.log('DEBUG: User not authenticated. Button redirects to login.');
        participateButton.textContent = MESSAGES.BUTTON_CONNECT_SIGNUP;
        participateButton.classList.add('btn-secondary');
        participateButton.onclick = () => { window.location.href = '/login'; };
        updateBanner(MESSAGES.INFO_CONNECT_TO_PARTICIPATE, 'info', true, true);
        return;
    }

    if (carpooling.isCurrentUserDriver) {
        console.log('DEBUG: User is the driver. Button disabled.');
        participateButton.textContent = MESSAGES.BUTTON_YOU_ARE_DRIVER;
        participateButton.disabled = true;
        participateButton.classList.add('btn-secondary');
        updateBanner(MESSAGES.WARN_DRIVER_CANNOT_PARTICIPATE, 'warning', true, false);
        return;
    }

    const isAlreadyPassenger = passengers.some(passenger => passenger.id === currentUser.id);
    if (isAlreadyPassenger) {
        console.log('DEBUG: User is already a passenger. Button disabled.');
        participateButton.textContent = MESSAGES.BUTTON_ALREADY_PARTICIPATING;
        participateButton.disabled = true;
        participateButton.classList.add('btn-secondary');
        updateBanner(MESSAGES.INFO_ALREADY_PARTICIPATING, 'info', true, false);
        return;
    }

    if (availableSeats <= 0) {
        console.log('DEBUG: Carpooling is full. Button disabled.');
        participateButton.textContent = MESSAGES.BUTTON_FULL;
        participateButton.disabled = true;
        participateButton.classList.add('btn-secondary');
        updateBanner(MESSAGES.WARN_CARPOOLING_FULL, 'warning', true, false);
        return;
    }

    if (currentUser && currentUser.credits < requiredCredits) {
        console.log('DEBUG: Insufficient credits. Button disabled.');
        participateButton.textContent = MESSAGES.BUTTON_INSUFFICIENT_CREDITS(requiredCredits);
        participateButton.disabled = true;
        participateButton.classList.add('btn-secondary');
        updateBanner(MESSAGES.WARN_INSUFFICIENT_CREDITS(requiredCredits), 'warning', true, false);
        return;
    }

    console.log('DEBUG: Participation conditions met. Button active.');
    participateButton.textContent = MESSAGES.BUTTON_PARTICIPATE(requiredCredits);
    participateButton.classList.add('btn-primary');
    participateButton.addEventListener('click', handleParticipateClick);
    updateBanner('', 'info', false); // Hide banner if everything is okay
}

/**
 * Handles the click event on the participate button.
 * Asks for confirmation and sends the participation request to the API.
 */
async function handleParticipateClick() {
    console.log('DEBUG: handleParticipateClick triggered.');
    if (!currentCarpoolingInstance || !currentUser) {
        console.error('DEBUG: currentCarpoolingInstance or currentUser missing during participation click.');
        updateBanner(MESSAGES.ERROR_GENERIC, 'danger', true, false);
        return;
    }

    const requiredCredits = currentCarpoolingInstance.pricePerPerson;

    const confirmation = confirm(MESSAGES.CONFIRM_PARTICIPATION(requiredCredits));

    if (confirmation) {
        updateBanner(MESSAGES.INFO_PARTICIPATION_IN_PROGRESS, 'info', true, false);
        participateButton.disabled = true;

        try {
            console.log('DEBUG: Sending participation request to API...');
            const response = await fetchApi(
                `${API_BASE_URL}/api/carpoolings/${currentCarpoolingInstance.id}/participate`,
                'POST',
                { userId: currentUser.id },
                { 'X-AUTH-TOKEN': localStorage.getItem('userToken') }
            );
            console.log('DEBUG: Response from participation API:', response);

            if (response.success) {
                updateBanner(MESSAGES.SUCCESS_PARTICIPATION, 'success', true, true);
                if (currentUser && response.newCredits !== undefined) {
                    currentUser.credits = response.newCredits;
                    console.log('DEBUG: New user credits:', currentUser.credits);
                } else if (currentUser) {
                    currentUser.credits -= requiredCredits; // Local deduction if API doesn't return newCredits
                    console.log('DEBUG: Credits deducted locally:', currentUser.credits);
                }
                await fetchCarpoolingDataAndRender(); // Reload to update state and button
            } else {
                updateBanner(MESSAGES.ERROR_PARTICIPATION_FAILED(response.message), 'danger', true, false);
                participateButton.disabled = false;
            }
        } catch (error) {
            console.error('DEBUG: Error during carpooling participation (fetchApi):', error);
            updateBanner(MESSAGES.ERROR_NETWORK_PARTICIPATION, 'danger', true, false);
            participateButton.disabled = false;
        }
    } else {
        updateBanner(MESSAGES.INFO_PARTICIPATION_CANCELLED, 'info', true, true);
    }
}

// =============================================================================
// V. Main Execution
// =============================================================================

(async () => {
    console.log('--- Starting detail-carpooling.js ---');

    initializeMessageBanner();
    setupBackButton();

    if (!checkRequiredDOMElements()) {
        console.log('Initialization stopped: missing DOM elements.');
        return;
    }

    await checkAuthentication();
    await fetchCarpoolingDataAndRender();
    console.log('DEBUG: Detail page initialization finished.');
})();