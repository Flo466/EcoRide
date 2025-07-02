import { fetchApi } from '../api/fetch.js';
import { sanitizeInput } from '../utils/sanitizer.js';
import { API_BASE_URL } from '../config.js';

(async function () {
    'use strict';

    const form = document.getElementById('registrationForm');
    const pseudoInput = document.getElementById('userName');
    const emailInput = document.getElementById('email');
    const confirmEmailInput = document.getElementById('confirmEmail');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const firstNameInput = document.getElementById('firstName');
    const lastNameInput = document.getElementById('lastName');
    const apiMessageContainer = document.getElementById('apiMessage');

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

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearMessage(apiMessageContainer);

        form.classList.add('was-validated');

        validateEmails();
        validatePassword();
        validatePasswordAndConfirm();

        if (!form.checkValidity()) {
            console.log("Formulaire invalide côté client.");
            return;
        }

        const userData = {
            firstName: sanitizeInput(firstNameInput.value),
            lastName: sanitizeInput(lastNameInput.value),
            userName: sanitizeInput(pseudoInput.value),
            email: sanitizeInput(emailInput.value),
            password: passwordInput.value
        };

        try {
            const result = await fetchApi(`${API_BASE_URL}/api/registration`, 'POST', userData);

            displayMessage(apiMessageContainer, 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.', true);
            form.reset();
            form.classList.remove('was-validated');

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
