import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';

// =============================================================================
// I. Constants and DOM Elements
// =============================================================================

// User-facing messages (kept in French)
const MESSAGES = {
    TOKEN_MISSING_DRIVER_MODE: "Jeton utilisateur manquant pour mettre à jour le mode chauffeur. Veuillez vous reconnecter.",
    UPDATE_DRIVER_SUCCESS: (isDriver) => isDriver ? "Mode chauffeur activé avec succès." : "Mode chauffeur désactivé avec succès.",
    UPDATE_DRIVER_ERROR_GENERIC: "Une erreur est survenue lors de la mise à jour du mode chauffeur.",
    TOKEN_MISSING_PROFILE: "Token utilisateur manquant. Redirection vers la page de connexion.",
    PROFILE_LOAD_ERROR_AUTH: "Votre session a expiré ou n'est plus valide. Veuillez vous reconnecter.",
    PROFILE_LOAD_ERROR_GENERIC: "Impossible de charger vos informations de profil. Veuillez réessayer.",
    AVATAR_LOAD_ERROR: "Impossible de charger votre photo de profil.",
    UPLOAD_AUTH_REQUIRED: "Vous devez être connecté pour télécharger une photo de profil.",
    UPLOAD_ERROR_GENERIC: (message) => `Impossible de télécharger votre photo : ${message}`,
    UPLOAD_SUCCESS: "Votre photo de profil a été mise à jour !",
    AVATAR_UPLOAD_ERROR_NETWORK: (status, statusText, errorText) => `Erreur lors du chargement de l'avatar: ${status} ${statusText} - ${errorText}`,
    DEFAULT_USERNAME: 'Utilisateur',
    DEFAULT_CREDITS: '0'
};

// DOM elements
const userNameDisplay = document.getElementById('userNameDisplay');
const userCreditsDisplay = document.getElementById('userCreditsDisplay');
const addPhotoBtn = document.getElementById('addPhotoBtn');
const avatarInput = document.getElementById('avatarInput');
const profileAvatarPlaceholder = document.querySelector('.profile-avatar-placeholder');
const messageDisplay = document.getElementById('messageDisplay');
const driverSwitch = document.getElementById('driverSwitch');
const driverModeMessage = document.getElementById('driverModeMessage');
const enterJourneyFormBtn = document.getElementById('enterJourneyFormBtn');
const enterVehicleFormBtn = document.getElementById('enterVehicleFormBtn');
const myVehiclesLink = document.querySelector('ul.list-group-flush > li:nth-child(2)');
const myJourneysLink = document.querySelector('ul.list-group-flush > li:nth-child(1)');
const myHistoryLink = document.querySelector('ul.list-group-flush > li:nth-child(3)');

// Global variable to store the previous object URL for avatar, to revoke it and avoid memory leaks.
let currentAvatarObjectURL = null;

// =============================================================================
// II. Utility Functions
// =============================================================================

/**
 * Displays a temporary message in the dedicated box.
 * @param {string} message - The text message to display.
 * @param {'success' | 'danger'} type - The type of message ('success' for green, 'danger' for red).
 * @param {HTMLElement} targetDisplay - The DOM element to display the message (defaults to messageDisplay).
 */
const displayMessage = (message, type, targetDisplay = messageDisplay) => {
    if (!targetDisplay) {
        console.error('displayMessage: targetDisplay is null or undefined. Message:', message);
        return;
    }

    targetDisplay.classList.remove('alert-success', 'alert-danger', 'd-none');
    targetDisplay.innerHTML = '';

    let iconClass = '';
    if (type === 'success') {
        targetDisplay.classList.add('alert-success');
        iconClass = 'bi bi-check-circle-fill';
    } else if (type === 'danger') {
        targetDisplay.classList.add('alert-danger');
        iconClass = 'bi bi-x-circle-fill';
    }

    targetDisplay.innerHTML = `<i class="${iconClass} me-2"></i>${message}`;
    targetDisplay.classList.remove('d-none');

    setTimeout(() => {
        targetDisplay.classList.add('d-none');
        targetDisplay.innerHTML = '';
    }, 5000);
};

/**
 * Updates the visibility of "Enter Journey" and "Enter Vehicle" buttons based on driver status.
 * @param {boolean} isDriver - The current driver mode status.
 */
const updateDriverButtonsVisibility = (isDriver) => {
    [enterJourneyFormBtn, enterVehicleFormBtn].forEach(btn => {
        if (btn) {
            if (isDriver) {
                btn.classList.remove('d-none');
            } else {
                btn.classList.add('d-none');
            }
        }
    });
};

/**
 * Updates the user's driver status via API.
 * @param {boolean} isDriverStatus - The new driver status (true/false).
 */
const updateDriverStatus = async (isDriverStatus) => {
    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        displayMessage(MESSAGES.TOKEN_MISSING_DRIVER_MODE, 'danger', driverModeMessage);
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    try {
        await fetchApi(
            `${API_BASE_URL}/api/account/me/driver-status`,
            'PATCH',
            { isDriver: isDriverStatus },
            { 'X-AUTH-TOKEN': userToken }
        );
        displayMessage(MESSAGES.UPDATE_DRIVER_SUCCESS(isDriverStatus), 'success', driverModeMessage);
        updateDriverButtonsVisibility(isDriverStatus);
    } catch (error) {
        console.error("Error updating driver mode:", error);
        const errorMessage = error.message || MESSAGES.UPDATE_DRIVER_ERROR_GENERIC;
        displayMessage(errorMessage, 'danger', driverModeMessage);
        if (driverSwitch) {
            driverSwitch.checked = !isDriverStatus; // Revert switch state on error
        }
    }
};

/**
 * Loads the user profile from the API and displays information, including the avatar and driver status.
 */
const loadUserProfile = async () => {
    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        displayMessage(MESSAGES.TOKEN_MISSING_PROFILE, 'danger');
        if (driverSwitch) driverSwitch.disabled = true;
        updateDriverButtonsVisibility(false);
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    try {
        const user = await fetchApi(
            `${API_BASE_URL}/api/account/me`,
            'GET',
            null,
            { 'X-AUTH-TOKEN': userToken }
        );

        // Update username and credits display.
        if (userNameDisplay) userNameDisplay.textContent = user.userName || MESSAGES.DEFAULT_USERNAME;
        if (userCreditsDisplay) userCreditsDisplay.textContent = typeof user.credits !== 'undefined' ? user.credits : MESSAGES.DEFAULT_CREDITS;

        // Manage avatar display.
        if (profileAvatarPlaceholder) {
            if (currentAvatarObjectURL) {
                URL.revokeObjectURL(currentAvatarObjectURL);
                currentAvatarObjectURL = null;
            }

            if (user.hasAvatar) {
                const avatarBlobUrl = `${API_BASE_URL}/api/account/me/avatar-blob?_t=${Date.now()}`;
                try {
                    const response = await fetch(avatarBlobUrl, {
                        method: 'GET',
                        headers: { 'X-AUTH-TOKEN': userToken }
                    });

                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error(MESSAGES.AVATAR_UPLOAD_ERROR_NETWORK(response.status, response.statusText, errorText));
                    }

                    const imageBlob = await response.blob();
                    currentAvatarObjectURL = URL.createObjectURL(imageBlob);

                    setTimeout(() => {
                        profileAvatarPlaceholder.innerHTML = `<img src="${currentAvatarObjectURL}" alt="Avatar" class="profile-avatar">`;
                    }, 50);
                } catch (imageError) {
                    console.error("Error loading avatar BLOB:", imageError);
                    profileAvatarPlaceholder.innerHTML = `<i class="bi bi-person-circle fs-1"></i>`;
                    displayMessage(MESSAGES.AVATAR_LOAD_ERROR, 'danger');
                }
            } else {
                profileAvatarPlaceholder.innerHTML = `<i class="bi bi-person-circle fs-1"></i>`;
            }
        }

        // Initialize driver switch and button visibility.
        if (driverSwitch) {
            if (user && typeof user.isDriver === 'boolean') {
                driverSwitch.checked = user.isDriver;
                updateDriverButtonsVisibility(user.isDriver);
            } else {
                driverSwitch.checked = false;
                updateDriverButtonsVisibility(false);
            }
        }
    } catch (error) {
        console.error("Error fetching user profile from API:", error);
        if (error.message.includes('401') || error.message.includes('Unauthorized') || error.message.includes('Missing credentials')) {
            displayMessage(MESSAGES.PROFILE_LOAD_ERROR_AUTH, 'danger');
            setTimeout(() => { window.location.href = '/login'; }, 3000);
        } else {
            displayMessage(MESSAGES.PROFILE_LOAD_ERROR_GENERIC, 'danger');
        }
        if (driverSwitch) driverSwitch.disabled = true;
        updateDriverButtonsVisibility(false);
    }
};

/**
 * Sends the avatar file to the server.
 * @param {File} file - The image file to upload.
 */
const uploadAvatar = async (file) => {
    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        displayMessage(MESSAGES.UPLOAD_AUTH_REQUIRED, 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    const formData = new FormData();
    formData.append('avatar', file);

    try {
        const uploadUrl = `${API_BASE_URL}/api/account/me/avatar`;
        const response = await fetch(uploadUrl, {
            method: 'POST',
            headers: { 'X-AUTH-TOKEN': userToken },
            body: formData,
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || `Error uploading avatar: ${response.status}`);
        }

        displayMessage(MESSAGES.UPLOAD_SUCCESS, 'success');
        loadUserProfile(); // Reload profile to display the new avatar.
    } catch (error) {
        console.error("Error uploading avatar:", error);
        displayMessage(MESSAGES.UPLOAD_ERROR_GENERIC(error.message), 'danger');
        if (profileAvatarPlaceholder) {
            if (currentAvatarObjectURL) {
                URL.revokeObjectURL(currentAvatarObjectURL);
                currentAvatarObjectURL = null;
            }
            profileAvatarPlaceholder.innerHTML = `<i class="bi bi-person-circle fs-1"></i>`;
        }
    }
};

// =============================================================================
// III. Event Listeners and Initialization
// =============================================================================

// Event listener for adding/changing photo.
if (addPhotoBtn && avatarInput) {
    addPhotoBtn.addEventListener('click', () => avatarInput.click());
}

// Event listener when a file is selected for avatar upload.
if (avatarInput) {
    avatarInput.addEventListener('change', async (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                if (profileAvatarPlaceholder) {
                    if (currentAvatarObjectURL) {
                        URL.revokeObjectURL(currentAvatarObjectURL);
                        currentAvatarObjectURL = null;
                    }
                    profileAvatarPlaceholder.innerHTML = `<img src="${e.target.result}" alt="Preview" class="profile-avatar-preview">`;
                }
            };
            reader.readAsDataURL(file);
            await uploadAvatar(file);
        }
    });
}

// Event listener for driver mode switch.
if (driverSwitch) {
    driverSwitch.addEventListener('change', (event) => {
        updateDriverStatus(event.target.checked);
    });
}

// Event listeners for navigation buttons/links.
if (enterVehicleFormBtn) {
    enterVehicleFormBtn.addEventListener('click', () => {
        window.location.href = '/car-form';
    });
}
if (enterJourneyFormBtn) {
    enterJourneyFormBtn.addEventListener('click', () => {
        window.location.href = '/journey-form';
    });
}
if (myVehiclesLink) {
    myVehiclesLink.addEventListener('click', (event) => {
        event.preventDefault();
        window.location.href = '/my-vehicles';
    });
}
if (myJourneysLink) {
    myJourneysLink.addEventListener('click', (event) => {
        event.preventDefault();
        window.location.href = '/my-journeys';
    });
}
if (myHistoryLink) {
    myHistoryLink.addEventListener('click', (event) => {
        event.preventDefault();
        window.location.href = '/my-history';
    });
}

// Clean up object URL before the page unloads to prevent memory leaks.
window.addEventListener('beforeunload', () => {
    if (currentAvatarObjectURL) {
        URL.revokeObjectURL(currentAvatarObjectURL);
    }
});

// Initial load of user profile when the script runs.
loadUserProfile();