import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';

// --- DOM Elements ---
const userNameDisplay = document.getElementById('userNameDisplay');
const userCreditsDisplay = document.getElementById('userCreditsDisplay');
const addPhotoBtn = document.getElementById('addPhotoBtn');
const avatarInput = document.getElementById('avatarInput');
const profileAvatarPlaceholder = document.querySelector('.profile-avatar-placeholder');
const messageDisplay = document.getElementById('messageDisplay'); // Pour les messages généraux (ex: avatar)
const driverSwitch = document.getElementById('driverSwitch'); // ID de l'interrupteur du mode chauffeur
const driverModeMessage = document.getElementById('driverModeMessage'); // Élément pour afficher les messages du mode chauffeur

// Boutons liés au mode chauffeur
const enterJourneyFormBtn = document.getElementById('enterJourneyFormBtn');
const enterVehicleFormBtn = document.getElementById('enterVehicleFormBtn');

// AJOUTÉ : Élément pour le lien "Mes véhicules"
// Sélectionne le 2ème <li> avec la classe .profile-list-item
const myVehiclesLink = document.querySelector('ul.list-group-flush > li:nth-child(2)');


// --- DÉBOGAGE : Vérification des éléments DOM au chargement du script ---
console.log('DOM Element Check: userNameDisplay', userNameDisplay);
console.log('DOM Element Check: userCreditsDisplay', userCreditsDisplay);
console.log('DOM Element Check: addPhotoBtn', addPhotoBtn);
console.log('DOM Element Check: avatarInput', avatarInput);
console.log('DOM Element Check: profileAvatarPlaceholder', profileAvatarPlaceholder);
console.log('DOM Element Check: messageDisplay', messageDisplay);
console.log('DOM Element Check: driverSwitch', driverSwitch);
console.log('DOM Element Check: driverModeMessage', driverModeMessage);
console.log('DOM Element Check: enterJourneyFormBtn', enterJourneyFormBtn);
console.log('DOM Element Check: enterVehicleFormBtn', enterVehicleFormBtn);
console.log('DOM Element Check: myVehiclesLink', myVehiclesLink); // DÉBOGAGE : Vérification du lien Mes véhicules


// Global variable to store the previous object URL, to revoke it and avoid memory leaks
let currentAvatarObjectURL = null;

/**
 * Displays a temporary message in the dedicated box.
 * @param {string} message The text message to display.
 * @param {'success' | 'danger'} type The type of message ('success' for green, 'danger' for red).
 * @param {HTMLElement} targetDisplay L'élément DOM où afficher le message (par défaut messageDisplay).
 */
const displayMessage = (message, type, targetDisplay = messageDisplay) => {
    if (targetDisplay) {
        // Remove previous classes and clear content (including old icon)
        targetDisplay.classList.remove('alert-success', 'alert-danger', 'd-none');
        targetDisplay.innerHTML = ''; // Clear existing content

        let iconClass = '';
        if (type === 'success') {
            targetDisplay.classList.add('alert-success');
            iconClass = 'bi bi-check-circle-fill'; // Bootstrap Icons success icon
        } else if (type === 'danger') {
            targetDisplay.classList.add('alert-danger');
            iconClass = 'bi bi-x-circle-fill'; // Bootstrap Icons error icon
        }

        // Add icon and message
        targetDisplay.innerHTML = `<i class="${iconClass} me-2"></i>${message}`;
        targetDisplay.classList.remove('d-none'); // Show the element

        // Hide the message after 5 seconds
        setTimeout(() => {
            targetDisplay.classList.add('d-none');
            targetDisplay.innerHTML = ''; // Clear content when hidden
        }, 5000);
    } else {
        console.error('displayMessage: targetDisplay est null ou undefined. Message:', message);
    }
};

/**
 * Met à jour la visibilité des boutons "Saisir un voyage" et "Saisir un véhicule".
 * @param {boolean} isDriver - Le statut actuel du mode chauffeur.
 */
const updateDriverButtonsVisibility = (isDriver) => {
    console.log(`updateDriverButtonsVisibility: isDriver = ${isDriver}`);
    if (enterJourneyFormBtn) {
        if (isDriver) {
            enterJourneyFormBtn.classList.remove('d-none');
            console.log('enterJourneyFormBtn visible');
        } else {
            enterJourneyFormBtn.classList.add('d-none');
            console.log('enterJourneyFormBtn caché');
        }
    } else {
        console.warn('enterJourneyFormBtn non trouvé.');
    }

    if (enterVehicleFormBtn) {
        if (isDriver) {
            enterVehicleFormBtn.classList.remove('d-none');
            console.log('enterVehicleFormBtn visible');
        } else {
            enterVehicleFormBtn.classList.add('d-none');
            console.log('enterVehicleFormBtn caché');
        }
    } else {
        console.warn('enterVehicleFormBtn non trouvé.');
    }
};

/**
 * Met à jour le statut de chauffeur de l'utilisateur via l'API.
 * @param {boolean} isDriverStatus - Le nouveau statut de chauffeur (true/false).
 */
const updateDriverStatus = async (isDriverStatus) => {
    const userToken = localStorage.getItem('userToken');
    console.log('updateDriverStatus: User Token:', userToken ? 'Present' : 'Missing');

    if (!userToken) {
        displayMessage("Token utilisateur manquant pour mettre à jour le mode chauffeur. Veuillez vous reconnecter.", 'danger', driverModeMessage);
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    try {
        console.log('updateDriverStatus: Sending PATCH request to /api/account/me/driver-status with isDriver:', isDriverStatus);
        const response = await fetchApi(
            `${API_BASE_URL}/api/account/me/driver-status`,
            'PATCH',
            { isDriver: isDriverStatus },
            { 'X-AUTH-TOKEN': userToken }
        );

        console.log('updateDriverStatus: API Response:', response);
        const responseData = response || {};
        const message = responseData.message || (isDriverStatus ? "Mode chauffeur activé avec succès." : "Mode chauffeur désactivé avec succès.");
        displayMessage(message, 'success', driverModeMessage);

        updateDriverButtonsVisibility(isDriverStatus); // Met à jour la visibilité des boutons après succès

    } catch (error) {
        console.error("Erreur lors de la mise à jour du mode chauffeur:", error);
        const errorMessage = error.message || "Une erreur est survenue lors de la mise à jour du mode chauffeur.";
        displayMessage(errorMessage, 'danger', driverModeMessage);
        // En cas d'erreur, remet l'interrupteur à son état précédent pour cohérence
        if (driverSwitch) {
            console.log('updateDriverStatus: Reverting switch state due to error.');
            driverSwitch.checked = !isDriverStatus;
        }
    }
};


/**
 * Loads the user profile from the API and displays the information, including the avatar.
 */
const loadUserProfile = async () => {
    console.log('loadUserProfile: Attempting to load user profile.');

    const userToken = localStorage.getItem('userToken');
    console.log('loadUserProfile: User Token:', userToken ? 'Present' : 'Missing');

    if (!userToken) {
        console.warn("Token utilisateur manquant. Redirection vers la page de connexion.");
        displayMessage("Token utilisateur manquant. Redirection vers la page de connexion.", 'danger');
        // Désactiver l'interrupteur et masquer les boutons si pas de token
        if (driverSwitch) driverSwitch.disabled = true;
        updateDriverButtonsVisibility(false);
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    try {
        console.log('loadUserProfile: Fetching user info from API: /api/account/me');
        const user = await fetchApi(
            `${API_BASE_URL}/api/account/me`, // L'URL pour récupérer les infos de l'utilisateur courant
            'GET',
            null,
            { 'X-AUTH-TOKEN': userToken }
        );

        console.log('loadUserProfile: User info from API:', user);

        // Update username and credits
        if (userNameDisplay) {
            userNameDisplay.textContent = user.userName || 'Utilisateur';
            console.log('loadUserProfile: userNameDisplay mis à jour:', userNameDisplay.textContent);
            console.log('loadUserProfile: userNameDisplay outerHTML après mise à jour:', userNameDisplay.outerHTML); // NOUVEAU LOG
        }
        if (userCreditsDisplay) {
            userCreditsDisplay.textContent = typeof user.credits !== 'undefined' ? user.credits : '0';
            console.log('loadUserProfile: userCreditsDisplay mis à jour:', userCreditsDisplay.textContent);
            console.log('loadUserProfile: userCreditsDisplay outerHTML après mise à jour:', userCreditsDisplay.outerHTML); // NOUVEAU LOG
        }

        // Manage avatar display based on 'hasAvatar'
        if (profileAvatarPlaceholder) {
            // Revoke old object URL if it exists to prevent memory leaks
            if (currentAvatarObjectURL) {
                URL.revokeObjectURL(currentAvatarObjectURL);
                currentAvatarObjectURL = null;
                console.log('loadUserProfile: Ancien avatar URL révoqué.');
            }

            if (user.hasAvatar) {
                const avatarBlobUrl = `${API_BASE_URL}/api/account/me/avatar-blob?_t=${Date.now()}`;
                console.log('loadUserProfile: Fetching avatar blob from:', avatarBlobUrl);
                try {
                    const response = await fetch(avatarBlobUrl, {
                        method: 'GET',
                        headers: {
                            'X-AUTH-TOKEN': userToken
                        }
                    });

                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error(`Erreur lors du chargement de l'avatar: ${response.status} ${response.statusText} - ${errorText}`);
                    }

                    const imageBlob = await response.blob();
                    currentAvatarObjectURL = URL.createObjectURL(imageBlob);

                    // Utiliser un petit délai pour voir si cela aide à la persistance
                    setTimeout(() => { // NOUVEAU : Ajout d'un setTimeout
                        profileAvatarPlaceholder.innerHTML = `<img src="${currentAvatarObjectURL}" alt="Avatar" class="profile-avatar">`;
                        console.log("Avatar chargé et affiché depuis le BLOB.");
                        console.log('loadUserProfile: profileAvatarPlaceholder outerHTML après mise à jour:', profileAvatarPlaceholder.outerHTML); // NOUVEAU LOG
                    }, 50); // Petit délai de 50ms

                } catch (imageError) {
                    console.error("Erreur lors du chargement de l'avatar BLOB:", imageError);
                    profileAvatarPlaceholder.innerHTML = `<i class="bi bi-person-circle fs-1"></i>`;
                    displayMessage("Impossible de charger votre photo de profil.", 'danger');
                    console.log("Avatar par défaut affiché suite à une erreur de chargement.");
                    console.log('loadUserProfile: profileAvatarPlaceholder outerHTML après erreur:', profileAvatarPlaceholder.outerHTML); // NOUVEAU LOG
                }
            } else {
                console.log("loadUserProfile: User has no avatar, displaying default icon.");
                profileAvatarPlaceholder.innerHTML = `<i class="bi bi-person-circle fs-1"></i>`;
                console.log("Avatar par défaut affiché (pas d'avatar utilisateur).");
                console.log('loadUserProfile: profileAvatarPlaceholder outerHTML (pas d\'avatar):', profileAvatarPlaceholder.outerHTML); // NOUVEAU LOG
            }
        } else {
            console.warn("loadUserProfile: profileAvatarPlaceholder element not found.");
        }

        // Initialiser l'état du switch chauffeur et la visibilité des boutons
        if (driverSwitch) {
            if (user && typeof user.isDriver === 'boolean') {
                driverSwitch.checked = user.isDriver;
                console.log('loadUserProfile: Driver switch set to:', user.isDriver);
                updateDriverButtonsVisibility(user.isDriver); // Mettre à jour la visibilité des boutons
            } else {
                console.warn("loadUserProfile: Statut isDriver non trouvé ou non booléen pour l'utilisateur. Défini sur false par défaut.");
                driverSwitch.checked = false;
                updateDriverButtonsVisibility(false); // Masquer les boutons
            }
        } else {
            console.warn("loadUserProfile: driverSwitch element not found.");
        }

    } catch (error) {
        console.error("Erreur lors de la récupération du profil utilisateur via API /api/account/me :", error);
        if (error.message.includes('401') || error.message.includes('Unauthorized') || error.message.includes('Missing credentials')) {
            displayMessage("Votre session a expiré ou n'est plus valide. Veuillez vous reconnecter.", 'danger');
            setTimeout(() => { window.location.href = '/login'; }, 3000);
        } else {
            displayMessage("Impossible de charger vos informations de profil. Veuillez réessayer.", 'danger');
        }
        // En cas d'erreur de chargement du profil, désactiver le switch et masquer les boutons
        if (driverSwitch) driverSwitch.disabled = true;
        updateDriverButtonsVisibility(false);
    }
};

// Event listener for the "Ajouter ou modifier une photo" button
if (addPhotoBtn && avatarInput) {
    addPhotoBtn.addEventListener('click', () => {
        console.log("Clic sur le bouton 'Ajouter ou modifier une photo'.");
        avatarInput.click();
    });
} else {
    console.warn("addPhotoBtn ou avatarInput non trouvé.");
}

// Event listener when the user selects a file
if (avatarInput) {
    avatarInput.addEventListener('change', async (event) => {
        console.log("Événement 'change' détecté sur l'input file.");
        const file = event.target.files[0];
        if (file) {
            console.log("Fichier sélectionné:", file.name, file.type, file.size, "bytes");
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
        } else {
            console.log("Aucun fichier sélectionné.");
        }
    });
}

/**
 * Sends the avatar file to the server.
 * @param {File} file - The image file to upload.
 */
const uploadAvatar = async (file) => {
    console.log("Début de la fonction uploadAvatar.");

    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        console.warn("Token utilisateur manquant pour l'upload d'avatar.");
        displayMessage("Vous devez être connecté pour télécharger une photo de profil.", 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    const formData = new FormData();
    formData.append('avatar', file);
    console.log("FormData préparé avec le fichier avatar.");

    try {
        const uploadUrl = `${API_BASE_URL}/api/account/me/avatar`;
        console.log(`Envoi de la requête POST à: ${uploadUrl}`);

        const response = await fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'X-AUTH-TOKEN': userToken
            },
            body: formData,
        });

        console.log("Réponse de la requête d'upload reçue.");

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || `Erreur lors du téléchargement de l'avatar: ${response.status}`);
        }

        const result = await response.json();
        console.log("Avatar téléchargé avec succès:", result);
        displayMessage("Votre photo de profil a été mise à jour !", 'success');

        loadUserProfile(); // Reload the profile to display the new photo from the API

    } catch (error) {
        console.error("Erreur lors du téléchargement de l'avatar:", error);
        displayMessage(`Impossible de télécharger votre photo : ${error.message}`, 'danger');
        if (profileAvatarPlaceholder) {
            if (currentAvatarObjectURL) {
                URL.revokeObjectURL(currentAvatarObjectURL);
                currentAvatarObjectURL = null;
            }
            profileAvatarPlaceholder.innerHTML = `<i class="bi bi-person-circle fs-1"></i>`;
        }
    }
};

// --- Initialisation du script ---
loadUserProfile(); // Chargement initial du profil utilisateur, y compris le statut du mode chauffeur

// Écouteur d'événement pour le changement de l'interrupteur du mode chauffeur
if (driverSwitch) {
    driverSwitch.addEventListener('change', (event) => {
        updateDriverStatus(event.target.checked);
    });
}

// Event listener for the "Saisir un véhicule" button
if (enterVehicleFormBtn) {
    enterVehicleFormBtn.addEventListener('click', () => {
        console.log("Redirection vers la page du formulaire de véhicule.");
        window.location.href = '/car-form';
    });
}
// Event listener for the "Saisir un voyage" button
if (enterJourneyFormBtn) {
    enterJourneyFormBtn.addEventListener('click', () => {
        console.log("Redirection vers la page du formulaire de voyage.");
        // Assurez-vous que cette URL est correcte pour votre formulaire de voyage
        window.location.href = '/journey-form';
    });
}

// AJOUTÉ : Écouteur d'événement pour le lien "Mes véhicules"
if (myVehiclesLink) {
    myVehiclesLink.addEventListener('click', (event) => {
        event.preventDefault(); // Empêche le comportement par défaut du lien si c'est un <a>
        console.log("Clic sur 'Mes véhicules'. Redirection vers /my-vehicules.");
        window.location.href = '/my-vehicules'; // Redirige vers la page des véhicules
    });
}


window.addEventListener('beforeunload', () => {
    if (currentAvatarObjectURL) {
        URL.revokeObjectURL(currentAvatarObjectURL);
    }
});
