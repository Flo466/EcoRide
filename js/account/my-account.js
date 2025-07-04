import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';

console.log("Script de la page Mon Compte (my-account.js) chargé.");

const userNameDisplay = document.getElementById('userNameDisplay');
const userCreditsDisplay = document.getElementById('userCreditsDisplay');
const addPhotoBtn = document.getElementById('addPhotoBtn');
const avatarInput = document.getElementById('avatarInput');
const profileAvatarPlaceholder = document.querySelector('.profile-avatar-placeholder');
const messageDisplay = document.getElementById('messageDisplay');
const driverSwitch = document.getElementById('driverSwitch');
const enterVehicleButtonParent = document.getElementById('enterVehicleFormBtn')?.parentNode;
const enterVehicleFormBtn = document.getElementById('enterVehicleFormBtn');
const enterJourneyButtonParent = document.getElementById('enterJourneyFormBtn')?.parentNode;
const enterJourneyFormBtn = document.getElementById('enterVehicleFormBtn');

// Global variable to store the previous object URL, to revoke it and avoid memory leaks
let currentAvatarObjectURL = null;

/**
 * Displays a temporary message in the dedicated box.
 * @param {string} message The text message to display.
 * @param {'success' | 'danger'} type The type of message ('success' for green, 'danger' for red).
 */
const displayMessage = (message, type) => {
    if (messageDisplay) {
        // Remove previous classes and clear content (including old icon)
        messageDisplay.classList.remove('alert-success', 'alert-danger', 'd-none');
        messageDisplay.innerHTML = ''; // Clear existing content

        let iconClass = '';
        if (type === 'success') {
            messageDisplay.classList.add('alert-success');
            iconClass = 'bi bi-check-circle-fill'; // Bootstrap Icons success icon
        } else if (type === 'danger') {
            messageDisplay.classList.add('alert-danger');
            iconClass = 'bi bi-x-circle-fill'; // Bootstrap Icons error icon
        }

        // Add icon and message
        messageDisplay.innerHTML = `<i class="${iconClass} me-2"></i>${message}`;
        messageDisplay.classList.remove('d-none'); // Show the element

        // Hide the message after 5 seconds
        setTimeout(() => {
            messageDisplay.classList.add('d-none');
            messageDisplay.innerHTML = ''; // Clear content when hidden
        }, 5000);
    }
};

/**
 * Manages the visibility of the "Saisir un véhicule" button based on the driver switch state.
 */
const toggleEnterTripButtonVisibility = () => {
    if (driverSwitch && enterVehicleButtonParent && enterJourneyButtonParent) {
        if (driverSwitch.checked) {
            enterVehicleButtonParent.classList.remove('d-none'); // Show the button's container
            enterJourneyButtonParent.classList.remove('d-none'); // Show the button's container
        } else {
            enterVehicleButtonParent.classList.add('d-none'); // Hide the button's container
            enterJourneyButtonParent.classList.add('d-none'); // Hide the button's container
        }
    }
};


/**
 * Loads the user profile from the API and displays the information, including the avatar.
 */
const loadUserProfile = async () => {
    const userToken = localStorage.getItem('userToken'); // Corrected: Use localStorage.getItem directly
    const userId = localStorage.getItem('userId'); // userId is not used here, but kept for consistency if needed elsewhere

    if (!userToken) {
        console.warn("Token utilisateur manquant. Redirection vers la page de connexion.");
        displayMessage("Token utilisateur manquant. Redirection vers la page de connexion.", 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000);
        return;
    }

    try {
        const userProfileUrl = `${API_BASE_URL}/api/account/me`;

        // Retrieve user profile data (including 'hasAvatar')
        const userData = await fetchApi(
            userProfileUrl,
            'GET',
            null,
            { 'X-AUTH-TOKEN': userToken } // Use the X-AUTH-TOKEN header
        );

        console.log("Données de profil utilisateur de l'API /api/account/me :", userData);

        // Update username and credits
        if (userNameDisplay) {
            userNameDisplay.textContent = userData.userName || 'Utilisateur';
        }

        if (userCreditsDisplay) {
            userCreditsDisplay.textContent = typeof userData.credits !== 'undefined' ? userData.credits : '0';
        }

        // Manage avatar display based on 'hasAvatar'
        if (profileAvatarPlaceholder) {
            // Revoke old object URL if it exists to prevent memory leaks
            if (currentAvatarObjectURL) {
                URL.revokeObjectURL(currentAvatarObjectURL);
                currentAvatarObjectURL = null;
            }

            if (userData.hasAvatar) {
                // Add a cache-busting parameter to the avatar URL
                const avatarBlobUrl = `${API_BASE_URL}/api/account/me/avatar-blob?_t=${Date.now()}`;
                try {
                    // Make a fetch request for the image with the authentication header
                    const response = await fetch(avatarBlobUrl, {
                        method: 'GET',
                        headers: {
                            'X-AUTH-TOKEN': userToken
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`Erreur lors du chargement de l'avatar: ${response.status} ${response.statusText}`);
                    }

                    const imageBlob = await response.blob(); // Get the response as a Blob
                    currentAvatarObjectURL = URL.createObjectURL(imageBlob); // Create a temporary URL for the blob

                    // Update the HTML to display the image
                    profileAvatarPlaceholder.innerHTML = `<img src="${currentAvatarObjectURL}" alt="Avatar" class="profile-avatar">`;
                    console.log("Avatar chargé et affiché depuis le BLOB.");

                } catch (imageError) {
                    console.error("Erreur lors du chargement de l'avatar BLOB:", imageError);
                    // In case of image loading error, display the default icon
                    profileAvatarPlaceholder.innerHTML = `<i class="bi bi-person-circle fs-1"></i>`;
                    displayMessage("Impossible de charger votre photo de profil.", 'danger');
                }
            } else {
                // If the user has no avatar, display the default icon
                profileAvatarPlaceholder.innerHTML = `<i class="bi bi-person-circle fs-1"></i>`;
            }
        }

        // Initialize the driver switch state and button visibility
        // If you have an 'isDriver' property in userData (e.g., userData.isDriver), you can use it here:
        // if (driverSwitch && typeof userData.isDriver !== 'undefined') {
        //     driverSwitch.checked = userData.isDriver;
        // }
        toggleEnterTripButtonVisibility(); // Call the function to set initial visibility


    } catch (error) {
        console.error("Erreur lors de la récupération du profil utilisateur via API /api/account/me :", error);
        if (error.message.includes('401') || error.message.includes('Unauthorized') || error.message.includes('Missing credentials')) {
            displayMessage("Votre session a expiré ou n'est plus valide. Veuillez vous reconnecter.", 'danger');
            setTimeout(() => { window.location.href = '/login'; }, 3000); // Redirect after message display
        } else {
            displayMessage("Impossible de charger vos informations de profil. Veuillez réessayer.", 'danger');
        }
    }
};

// Event listener for the "Ajouter ou modifier une photo" button
if (addPhotoBtn && avatarInput) {
    addPhotoBtn.addEventListener('click', () => {
        console.log("Clic sur le bouton 'Ajouter ou modifier une photo'."); // DEBUG LOG
        avatarInput.click(); // Simulate a click on the hidden file input to open the dialog
    });
}

// Event listener when the user selects a file
if (avatarInput) {
    avatarInput.addEventListener('change', async (event) => {
        console.log("Événement 'change' détecté sur l'input file."); // DEBUG LOG
        const file = event.target.files[0]; // Get the first selected file
        if (file) {
            console.log("Fichier sélectionné:", file.name, file.type, file.size, "bytes"); // DEBUG LOG
            // Optional: Display a preview of the selected image before upload
            const reader = new FileReader();
            reader.onload = (e) => {
                if (profileAvatarPlaceholder) {
                    // Revoke old object URL if it exists to prevent memory leaks
                    if (currentAvatarObjectURL) {
                        URL.revokeObjectURL(currentAvatarObjectURL);
                        currentAvatarObjectURL = null;
                    }
                    // Use a different class for the preview if you want temporary styling
                    profileAvatarPlaceholder.innerHTML = `<img src="${e.target.result}" alt="Preview" class="profile-avatar-preview">`;
                }
            };
            reader.readAsDataURL(file); // Read the file as a data URL

            // Call the function to send the photo to the server
            await uploadAvatar(file);
        } else {
            console.log("Aucun fichier sélectionné."); // DEBUG LOG
        }
    });
}

/**
 * Sends the avatar file to the server.
 * @param {File} file - The image file to upload.
 */
const uploadAvatar = async (file) => {
    console.log("Début de la fonction uploadAvatar."); // DEBUG LOG

    const userToken = localStorage.getItem('userToken'); // Corrected: Use localStorage.getItem directly
    if (!userToken) {
        console.warn("Token utilisateur manquant pour l'upload d'avatar.");
        displayMessage("Vous devez être connecté pour télécharger une photo de profil.", 'danger');
        setTimeout(() => { window.location.href = '/login'; }, 3000); // Redirect after message display
        return;
    }

    const formData = new FormData();
    formData.append('avatar', file); // 'avatar' is the field name expected by the Symfony server
    console.log("FormData préparé avec le fichier avatar."); // DEBUG LOG

    try {
        const uploadUrl = `${API_BASE_URL}/api/account/me/avatar`; // API route for upload
        console.log(`Envoi de la requête POST à: ${uploadUrl}`); // DEBUG LOG

        // Use fetch directly for FormData (no 'Content-Type' manually set, browser handles it)
        const response = await fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'X-AUTH-TOKEN': userToken // Your authentication header
            },
            body: formData,
        });

        console.log("Réponse de la requête d'upload reçue."); // DEBUG LOG

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || `Erreur lors du téléchargement de l'avatar: ${response.status}`);
        }

        const result = await response.json();
        console.log("Avatar téléchargé avec succès:", result);
        displayMessage("Votre photo de profil a été mise à jour !", 'success');

        // Reload the profile to display the new photo from the API (which will now fetch the BLOB)
        loadUserProfile();

    } catch (error) {
        console.error("Erreur lors du téléchargement de l'avatar:", error);
        displayMessage(`Impossible de télécharger votre photo : ${error.message}`, 'danger');
        // In case of failure, revert to default icon or old image
        if (profileAvatarPlaceholder) {
            // Revoke old object URL if it exists to prevent memory leaks
            if (currentAvatarObjectURL) {
                URL.revokeObjectURL(currentAvatarObjectURL);
                currentAvatarObjectURL = null;
            }
            profileAvatarPlaceholder.innerHTML = `<i class="bi bi-person-circle fs-1"></i>`;
        }
    }
};

loadUserProfile();

if (driverSwitch) {
    driverSwitch.addEventListener('change', toggleEnterTripButtonVisibility);
}

// Event listener for the "Saisir un véhicule" button
if (enterVehicleFormBtn) {
    enterVehicleFormBtn.addEventListener('click', () => {
        console.log("Redirection vers la page du formulaire de véhicule.");
        // Ensure this path is correct for your vehicle form HTML file
        window.location.href = '/car-form.html';
    });
}

window.addEventListener('beforeunload', () => {
    if (currentAvatarObjectURL) {
        URL.revokeObjectURL(currentAvatarObjectURL);
    }
});
