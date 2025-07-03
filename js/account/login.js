import { fetchApi } from '../api/fetch.js';
import { API_BASE_URL } from '../config.js';

const handleLogout = () => {
    localStorage.removeItem('userToken');
    localStorage.removeItem('userId');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userRoles');

    console.log('Utilisateur déconnecté.');
    window.location.href = '/';
};

(async () => {
    const loginForm = document.querySelector('.needs-validation');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const submitButton = loginForm ? loginForm.querySelector('button[type="submit"]') : null;

    const logoutLink = document.getElementById('logoutLink');

    if (loginForm && submitButton && emailInput && passwordInput) {
        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            event.stopPropagation();

            loginForm.classList.add('was-validated');

            if (!loginForm.checkValidity()) {
                return;
            }

            submitButton.disabled = true;
            submitButton.textContent = 'Connexion en cours...';

            const email = emailInput.value;
            const password = passwordInput.value;

            try {
                const loginUrl = `${API_BASE_URL}/api/login`;
                const data = await fetchApi(loginUrl, 'POST', { email, password });

                console.log('Connexion réussie:', data);
                localStorage.setItem('userToken', data.apiToken);
                localStorage.setItem('userId', data.id);
                localStorage.setItem('userEmail', data.email);
                localStorage.setItem('userRoles', JSON.stringify(data.roles));

                window.location.href = '/my-account';

            } catch (error) {
                console.error('Erreur de connexion:', error.message);
                alert('Erreur de connexion: ' + error.message);
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Se connecter';
            }
        });
    } else {
        console.warn("Formulaire de connexion ou éléments associés non trouvés. La logique de connexion ne sera pas activée.");
    }

    if (logoutLink) {
        logoutLink.addEventListener('click', (event) => {
            event.preventDefault();
            handleLogout();
        });
    } else {
        console.error("Erreur: Le lien de déconnexion (ID: 'logoutLink') n'a pas été trouvé dans le DOM. Veuillez vous assurer que l'élément HTML existe et que son ID est correct, et que le script est chargé après l'élément.");
    }
})();
