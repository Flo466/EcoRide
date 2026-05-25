import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';

// =============================================================================
// I. Constantes et Éléments DOM
// =============================================================================

const MESSAGES = {
    TOKEN_MISSING_DRIVER_MODE: "Jeton manquant. Veuillez vous reconnecter.",
    UPDATE_DRIVER_SUCCESS: (isDriver) => isDriver ? "Mode chauffeur activé." : "Mode chauffeur désactivé.",
    UPDATE_DRIVER_ERROR_GENERIC: "Erreur lors de la mise à jour du mode chauffeur.",
    TOKEN_MISSING_PROFILE: "Session expirée. Redirection...",
    PROFILE_LOAD_ERROR_GENERIC: "Impossible de charger le profil.",
    UPLOAD_SUCCESS: "Photo de profil mise à jour !",
    UPLOAD_ERROR_GENERIC: (msg) => `Erreur upload : ${msg}`,
    DEFAULT_USERNAME: 'Utilisateur',
    DEFAULT_CREDITS: '0'
};

const userNameDisplay = document.getElementById('userNameDisplay');
const userCreditsDisplay = document.getElementById('userCreditsDisplay');
const addPhotoBtn = document.getElementById('addPhotoBtn');
const avatarInput = document.getElementById('avatarInput');
const profileAvatarPlaceholder = document.querySelector('.profile-avatar-placeholder');
const messageDisplay = document.getElementById('messageDisplay');
const driverSwitch = document.getElementById('driverSwitch');
const driverModeMessage = document.getElementById('driverModeMessage');

// =============================================================================
// II. Fonctions Utilitaires
// =============================================================================

const displayMessage = (message, type, targetDisplay = messageDisplay) => {
    if (!targetDisplay) return;
    targetDisplay.classList.remove('alert-success', 'alert-danger', 'd-none');
    targetDisplay.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
    targetDisplay.innerHTML = `<i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-x-circle'} me-2"></i>${message}`;
    setTimeout(() => targetDisplay.classList.add('d-none'), 5000);
};

const updateDriverButtonsVisibility = (isDriver) => {
    const enterJourneyFormBtn = document.getElementById('enterJourneyFormBtn');
    const enterVehicleFormBtn = document.getElementById('enterVehicleFormBtn');
    const btns = [enterJourneyFormBtn, enterVehicleFormBtn];
    btns.forEach(btn => btn && btn.classList.toggle('d-none', !isDriver));
};

// =============================================================================
// III. Actions API
// =============================================================================

/**
 * Met à jour le statut chauffeur
 */
const updateDriverStatus = async (isDriverStatus) => {
    const token = localStorage.getItem('userToken');
    try {
        await fetchApi(`${API_BASE_URL}/api/account/me/driver-status`, 'PATCH', { isDriver: isDriverStatus }, { 'X-AUTH-TOKEN': token });
        displayMessage(MESSAGES.UPDATE_DRIVER_SUCCESS(isDriverStatus), 'success', driverModeMessage);
        updateDriverButtonsVisibility(isDriverStatus);
    } catch (error) {
        displayMessage(MESSAGES.UPDATE_DRIVER_ERROR_GENERIC, 'danger', driverModeMessage);
        if (driverSwitch) driverSwitch.checked = !isDriverStatus;
    }
};

/**
 * Charge le profil utilisateur
 */
const loadUserProfile = async () => {
    const token = localStorage.getItem('userToken');
    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {
        const user = await fetchApi(`${API_BASE_URL}/api/account/me`, 'GET', null, { 'X-AUTH-TOKEN': token });

        if (userNameDisplay) userNameDisplay.textContent = user.userName || MESSAGES.DEFAULT_USERNAME;
        if (userCreditsDisplay) userCreditsDisplay.textContent = user.credits ?? MESSAGES.DEFAULT_CREDITS;

        if (profileAvatarPlaceholder) {
            if (user.photo) {
                const avatarUrl = `${API_BASE_URL}/uploads/${user.photo}?t=${Date.now()}`;
                profileAvatarPlaceholder.innerHTML = `<img src="${avatarUrl}" alt="Avatar" class="profile-avatar">`;
            } else {
                profileAvatarPlaceholder.innerHTML = `<i class="bi bi-person-circle fs-1"></i>`;
            }
        }

        if (driverSwitch) {
            driverSwitch.checked = !!user.isDriver;
            updateDriverButtonsVisibility(!!user.isDriver);
        }
    } catch (error) {
        console.error("Erreur chargement profil:", error);
        if (error.message.includes('401')) window.location.href = '/login';
    }
};

/**
 * Upload de l'avatar vers l'API
 */
const uploadAvatar = async (file) => {
    const token = localStorage.getItem('userToken');
    const formData = new FormData();
    formData.append('avatar', file);

    try {
        const response = await fetch(`${API_BASE_URL}/api/account/me/avatar`, {
            method: 'POST',
            headers: { 'X-AUTH-TOKEN': token },
            body: formData
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || "Erreur serveur");
        }

        displayMessage(MESSAGES.UPLOAD_SUCCESS, 'success');
        await loadUserProfile();
    } catch (error) {
        console.error("Erreur Upload:", error);
        displayMessage(MESSAGES.UPLOAD_ERROR_GENERIC(error.message), 'danger');
    }
};

// =============================================================================
// IV. Listeners (Uniquement Photo de profil et Éléments de Formulaires)
// =============================================================================

document.addEventListener('click', (e) => {
    const addPhotoBtnClick = e.target.closest('#addPhotoBtn');
    
    // Déclenchement du clic sur l'input file masqué
    if (addPhotoBtnClick) {
        e.preventDefault();
        const input = document.getElementById('avatarInput');
        if (input) input.click();
    }
});

document.addEventListener('change', (e) => {
    // Gestion du switch Chauffeur
    if (e.target && e.target.id === 'driverSwitch') {
        updateDriverStatus(e.target.checked);
    }
    
    // Traitement du fichier image uploadé
    if (e.target && e.target.id === 'avatarInput') {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                const placeholder = document.querySelector('.profile-avatar-placeholder');
                if (placeholder) {
                    placeholder.innerHTML = `<img src="${event.target.result}" alt="Preview" class="profile-avatar-preview">`;
                }
            };
            reader.readAsDataURL(file);
            uploadAvatar(file);
        }
    }
});

// =============================================================================
// V. Initialisation
// =============================================================================

loadUserProfile();