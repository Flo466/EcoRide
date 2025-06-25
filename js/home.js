// home.js
import { sanitizeInput } from '../js/utils/sanitizer.js';
import { clearMessages, displayMessage } from '../js/utils/alert.js';

(async () => {
    const homeForm = document.querySelector('.search-form'); 
    const formMessagesContainer = document.getElementById('form-messages');

    if (homeForm) {
        homeForm.addEventListener('submit', (event) => {
            event.preventDefault();

            clearMessages(formMessagesContainer);

            const departurePlace = sanitizeInput(document.getElementById('departurePlace').value);
            const arrivalPlace = sanitizeInput(document.getElementById('arrivalPlace').value);
            const departureDate = sanitizeInput(document.getElementById('departureDate').value);


            if (!departurePlace && !arrivalPlace) {
                displayMessage(formMessagesContainer, "Veuillez entrer au moins un point de départ et une destination.");
                return;
            }

            const searchParams = new URLSearchParams();
            if (departurePlace) {
                searchParams.append('departurePlace', departurePlace);
            }
            if (arrivalPlace) {
                searchParams.append('arrivalPlace', arrivalPlace);
            }
            if (departureDate) {
                searchParams.append('departureDate', departureDate);
            }

            const redirectToUrl = `/covoiturages?${searchParams.toString()}`;

            console.log("URL de redirection construite dans home.js :", redirectToUrl);

            window.location.href = redirectToUrl;
        });
    } else {
        console.error("Formulaire de recherche non trouvé dans home.html. Le script ne peut pas s'attacher.");
    }
})();