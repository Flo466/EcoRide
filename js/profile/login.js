// /js/account/login.js

import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';

console.log("Script login.js chargé.");

const loginForm = document.querySelector('.needs-validation');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const submitButton = loginForm ? loginForm.querySelector('button[type="submit"]') : null;
const errorMessageDiv = document.getElementById('errorMessage');

// Fonction pour afficher un message d'erreur
const showErrorMessage = (message) => {
    if (errorMessageDiv) {
        errorMessageDiv.textContent = message;
        errorMessageDiv.style.display = 'block'; // Affiche l'élément
    }
};

// Fonction pour cacher le message d'erreur
const hideErrorMessage = () => {
    if (errorMessageDiv) {
        errorMessageDiv.textContent = '';
        errorMessageDiv.style.display = 'none'; // Cache l'élément
    }
};


if (loginForm && submitButton && emailInput && passwordInput) {
    console.log('Formulaire de connexion trouvé. Attachement de l\'écouteur.');
    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        event.stopPropagation();

        hideErrorMessage(); // Cache tout message d'erreur précédent

        loginForm.classList.add('was-validated');

        if (!loginForm.checkValidity()) {
            // Si la validation HTML/Bootstrap échoue, Bootstrap gère déjà les messages sous les champs.
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Connexion en cours...';

        const email = emailInput.value;
        const password = passwordInput.value;

        try {
            const loginUrl = `${API_BASE_URL}/api/login`;
            console.log(`Tentative de connexion à ${loginUrl} avec email: ${email}`);
            const data = await fetchApi(loginUrl, 'POST', { email, password });

            console.log('Connexion réussie:', data);
            localStorage.setItem('userToken', data.apiToken);
            localStorage.setItem('userId', data.id);
            localStorage.setItem('userEmail', data.email);
            localStorage.setItem('userRoles', JSON.stringify(data.roles));

            console.log('Informations utilisateur stockées. Redirection vers /profile.');
            window.location.href = '/profile';

        } catch (error) {
            console.error('Erreur de connexion:', error.message);
            let userFriendlyMessage = 'Une erreur est survenue lors de la connexion. Veuillez réessayer.';

            // On vérifie le message d'erreur spécifique de l'API
            if (error.message.includes('Incorrect credentials')) {
                userFriendlyMessage = 'Email ou mot de passe incorrect. Veuillez vérifier vos informations.';
            } else if (error.message.includes('Network request failed') || error.message.includes('Failed to fetch')) {
                userFriendlyMessage = 'Impossible de se connecter au serveur. Vérifiez votre connexion internet ou réessayez plus tard.';
            }
            // Tu peux ajouter d'autres conditions si tu as d'autres messages d'erreur spécifiques de ton API.

            showErrorMessage(userFriendlyMessage); // Affiche le message plus convivial
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Se connecter';
        }
    });
} else {
    console.warn("Formulaire de connexion ou éléments associés non trouvés sur cette page. La logique de connexion ne sera pas activée.");
}