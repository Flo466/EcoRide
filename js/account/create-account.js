
(function () {
    'use strict';

    // Récupérer le formulaire
    const form = document.querySelector('.needs-validation');

    // Ajouter un écouteur d'événement pour la soumission du formulaire
    form.addEventListener('submit', function (event) {
        // Vérifier la validité du formulaire Bootstrap
        if (!form.checkValidity()) {
            event.preventDefault(); // Empêche la soumission si non valide
            event.stopPropagation(); // Empêche la propagation de l'événement
        }

        // Validation personnalisée des emails
        const email = document.getElementById('email');
        const confirmEmail = document.getElementById('confirmEmail');
        if (email.value !== confirmEmail.value) {
            confirmEmail.setCustomValidity("Email mismatch"); // Message d'erreur personnalisé
            event.preventDefault();
            event.stopPropagation();
        } else {
            confirmEmail.setCustomValidity(""); // Réinitialise si ok
        }

        // Validation personnalisée des mots de passe
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+={ }\[\]|\\:;"'<>,.?/~`])[A-Za-z\d!@#$%^&*()_+={ }\[\]|\\:;"'<>,.?/~`]{8,}$/;

        if (!passwordPattern.test(password.value)) {
            password.setCustomValidity("Invalid password format");
            event.preventDefault();
            event.stopPropagation();
        } else {
            password.setCustomValidity("");
        }

        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Password mismatch");
            event.preventDefault();
            event.stopPropagation();
        } else {
            // S'assurer que le premier mot de passe est valide avant de vérifier la correspondance
            if (passwordPattern.test(password.value)) {
                confirmPassword.setCustomValidity(""); // Réinitialise si ok
            }
        }

        form.classList.add('was-validated'); // Active les styles de validation Bootstrap
    }, false);

    // Gestion de l'événement input pour réinitialiser les messages d'erreur si l'utilisateur corrige
    // Ceci est important pour que le message disparaisse dès que l'utilisateur commence à corriger
    document.getElementById('confirmEmail').addEventListener('input', function () {
        const email = document.getElementById('email');
        const confirmEmail = this;
        if (email.value === confirmEmail.value) {
            confirmEmail.setCustomValidity("");
        } else {
            confirmEmail.setCustomValidity("Email mismatch");
        }
    });

    document.getElementById('confirmPassword').addEventListener('input', function () {
        const password = document.getElementById('password');
        const confirmPassword = this;
        if (password.value === confirmPassword.value) {
            confirmPassword.setCustomValidity("");
        } else {
            confirmPassword.setCustomValidity("Password mismatch");
        }
    });

    document.getElementById('password').addEventListener('input', function () {
        const password = this;
        const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+={ }\[\]|\\:;"'<>,.?/~`])[A-Za-z\d!@#$%^&*()_+={ }\[\]|\\:;"'<>,.?/~`]{8,}$/;
        if (passwordPattern.test(password.value)) {
            password.setCustomValidity("");
        } else {
            password.setCustomValidity("Invalid password format");
        }
    });


})();
