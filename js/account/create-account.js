import { fetchApi } from '../api/fetch.js';
import { sanitizeInput } from '../utils/sanitizer.js';
import { API_BASE_URL } from '../config.js';

(async function () {
    'use strict';

    const form = document.getElementById('registrationForm');
    const userNameInput = document.getElementById('userName'); // Renommé de pseudoInput à userNameInput
    const emailInput = document.getElementById('email');
    const confirmEmailInput = document.getElementById('confirmEmail');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const apiMessageContainer = document.getElementById('apiMessage');

    // Nouveaux éléments pour afficher les statuts de live check
    const userNameStatus = document.getElementById('userNameStatus');
    const emailStatus = document.getElementById('emailStatus');

    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+={}\[\]|\\:;"'<>,.?/~`])[A-Za-z\d!@#$%^&*()_+={}\[\]|\\:;"'<>,.?/~`]{8,}$/;

    function displayMessage(container, message, isSuccess = false) {
        container.innerHTML = `<div class="alert alert-${isSuccess ? 'success' : 'danger'}" role="alert">${message}</div>`;
        container.style.display = 'block';
    }

    function clearMessage(container) {
        container.innerHTML = '';
        container.style.display = 'none';
    }

    function setAuthToken(token) {
        localStorage.setItem('apiToken', token);
        console.log('Jeton d\'API sauvegardé.');
    }

    function getAuthToken() {
        return localStorage.getItem('apiToken');
    }

    function removeAuthToken() {
        localStorage.removeItem('apiToken');
        console.log('Jeton d\'API supprimé.');
    }

    // --- Fonctions de validation existantes ---
    emailInput.addEventListener('input', validateEmails);
    confirmEmailInput.addEventListener('input', validateEmails);
    passwordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', validatePasswordAndConfirm);
    passwordInput.addEventListener('input', validatePasswordAndConfirm);

    function validateEmails() {
        if (emailInput.value !== confirmEmailInput.value) {
            confirmEmailInput.setCustomValidity("Les adresses e-mail ne correspondent pas.");
        } else {
            confirmEmailInput.setCustomValidity("");
        }
    }

    function validatePassword() {
        if (!passwordPattern.test(passwordInput.value)) {
            passwordInput.setCustomValidity("Le mot de passe ne respecte pas les normes de sécurité.");
        } else {
            passwordInput.setCustomValidity("");
        }
        validatePasswordAndConfirm();
    }

    function validatePasswordAndConfirm() {
        if (passwordInput.value !== confirmPasswordInput.value) {
            confirmPasswordInput.setCustomValidity("Les mots de passe ne correspondent pas.");
        } else {
            if (passwordPattern.test(passwordInput.value)) {
                confirmPasswordInput.setCustomValidity("");
            } else {
                confirmPasswordInput.setCustomValidity("Le mot de passe ne respecte pas les normes de sécurité.");
            }
        }
    }

    function debounce(func, delay) {
        let timeout;
        return function (...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), delay);
        };
    }

    const checkUserName = debounce(async (userName) => {
        const sanitizedUserName = sanitizeInput(userName);
        if (sanitizedUserName.length < 3) {
            userNameStatus.textContent = '';
            userNameStatus.style.color = 'initial';
            return;
        }

        userNameStatus.textContent = 'Vérification...';
        userNameStatus.style.color = 'orange';

        try {
            const result = await fetchApi(`${API_BASE_URL}/api/check-userName`, 'POST', { userName: sanitizedUserName });

            if (result && typeof result.isAvailable !== 'undefined') {
                if (result.isAvailable) {
                    userNameStatus.textContent = 'Nom d\'utilisateur disponible !';
                    userNameStatus.style.color = 'green';
                } else {
                    userNameStatus.textContent = 'Nom d\'utilisateur déjà pris.';
                    userNameStatus.style.color = 'red';
                }
            } else {
                userNameStatus.textContent = 'Erreur de vérification (réponse inattendue).';
                userNameStatus.style.color = 'gray';
            }
        } catch (error) {
            console.error('Erreur lors de la vérification du nom d\'utilisateur :', error);
            userNameStatus.textContent = `Erreur de vérification: ${error.message || 'réseau'}.`;
            userNameStatus.style.color = 'gray';
        }
    }, 500);

    const checkEmailLive = debounce(async (email) => {
        const sanitizedEmail = sanitizeInput(email);

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(sanitizedEmail)) {
            emailStatus.textContent = 'Format d\'email invalide.';
            emailStatus.style.color = 'red';
            return;
        }

        emailStatus.textContent = 'Vérification...';
        emailStatus.style.color = 'orange';

        try {
            const result = await fetchApi(`${API_BASE_URL}/api/check-email`, 'POST', { email: sanitizedEmail });

            if (result && typeof result.isAvailable !== 'undefined') {
                if (result.isAvailable) {
                    emailStatus.textContent = 'Email disponible !';
                    emailStatus.style.color = 'green';
                } else {
                    emailStatus.textContent = 'Email déjà utilisé.';
                    emailStatus.style.color = 'red';
                }
            } else {
                emailStatus.textContent = 'Erreur de vérification (réponse inattendue).';
                emailStatus.style.color = 'gray';
            }
        } catch (error) {
            console.error('Erreur lors de la vérification de l\'email :', error);
            emailStatus.textContent = `Erreur de vérification: ${error.message || 'réseau'}.`;
            emailStatus.style.color = 'gray';
        }
    }, 500);

    userNameInput.addEventListener('keyup', (e) => checkUserName(e.target.value));
    emailInput.addEventListener('keyup', (e) => checkEmailLive(e.target.value));


    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearMessage(apiMessageContainer);

        form.classList.add('was-validated');

        validateEmails();
        validatePassword();
        validatePasswordAndConfirm();

        if (userNameStatus.style.color === 'red' || emailStatus.style.color === 'red') {
            displayMessage(apiMessageContainer, 'Veuillez corriger les erreurs dans le formulaire.', false);
            return;
        }

        if (!form.checkValidity()) {
            console.log("Formulaire invalide côté client.");
            return;
        }

        const userData = {
            userName: sanitizeInput(userNameInput.value),
            email: sanitizeInput(emailInput.value),
            password: passwordInput.value
        };

        try {
            const result = await fetchApi(`${API_BASE_URL}/api/registration`, 'POST', userData);

            displayMessage(apiMessageContainer, 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.', true);
            form.reset();
            form.classList.remove('was-validated');

            // Clear live check statuses on successful registration
            userNameStatus.textContent = '';
            emailStatus.textContent = '';


            if (result.apiToken) {
                setAuthToken(result.apiToken);
            }

            setTimeout(() => {
                window.location.href = '/connection';
            }, 3000);
        } catch (error) {
            const errorMessage = error.message || 'Une erreur est survenue lors de l\'inscription.';
            displayMessage(apiMessageContainer, `Erreur: ${errorMessage}`);
            console.error('Erreur API ou réseau:', error);
        }
    }, false);

    let currentPath = window.location.pathname;
    if (currentPath.endsWith('/') && currentPath.length > 1) {
        currentPath = currentPath.slice(0, -1);
    }

    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    navLinks.forEach(link => {
        let linkPath = link.getAttribute('href');

        if (linkPath === '/') {
            if (currentPath === '/' || currentPath === '') {
                link.classList.add('active');
                link.setAttribute('aria-current', 'page');
            } else {
                link.classList.remove('active');
            }
        } else if (currentPath.includes(linkPath) && linkPath !== '/') {
            navLinks.forEach(otherLink => otherLink.classList.remove('active'));
            link.classList.add('active');
            link.setAttribute('aria-current', 'page');
        }
    });
})();