import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';

console.log("Script de la page Mon Compte (my-account.js) chargé.");

const userNameDisplay = document.getElementById('userNameDisplay');
const userCreditsDisplay = document.getElementById('userCreditsDisplay');

const loadUserProfile = async () => {
    const userToken = localStorage.getItem('userToken');
    const userId = localStorage.getItem('userId');

    if (!userToken) {
        console.warn("Token utilisateur manquant. Redirection vers la page de connexion.");
        window.location.href = '/login';
        return;
    }

    try {
        const userProfileUrl = `${API_BASE_URL}/api/account/me`;

        const userData = await fetchApi(
            userProfileUrl,
            'GET',
            null,
            { 'X-AUTH-TOKEN': userToken }
        );

        console.log("Données de profil utilisateur de l'API /api/account/me :", userData);

        if (userNameDisplay) {
            userNameDisplay.textContent = userData.userName || 'Utilisateur';
        }

        if (userCreditsDisplay) {
            userCreditsDisplay.textContent = typeof userData.credits !== 'undefined' ? userData.credits : '0';
        }

    } catch (error) {
        console.error("Erreur lors de la récupération du profil utilisateur via API /api/account/me :", error);
        if (error.message.includes('401') || error.message.includes('Unauthorized') || error.message.includes('Missing credentials')) {
            alert("Votre session a expiré ou n'est plus valide. Veuillez vous reconnecter.");
            window.location.href = '/login';
        } else {
            alert("Impossible de charger vos informations de profil. Veuillez réessayer.");
        }
    }
};

loadUserProfile();
