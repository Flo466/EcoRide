// Dans public/js/detail-carpooling.js

import { fetchApi } from './api/fetch.js';
import { API_BASE_URL } from './config.js';
import { Carpooling } from './models/Carpooling.js';

(async () => {
    const container = document.getElementById('detail-carpooling-container');
    const backButton = document.getElementById('back-button');

    if (backButton) {
        backButton.addEventListener('click', (e) => {
            e.preventDefault();
            if (document.referrer && document.referrer.includes(window.location.host)) { // Vérifier si le référent est du même site
                window.history.back();
            } else {
                window.location.href = '/covoiturages'; // Redirige vers la liste si pas de référent pertinent
            }
        });
    }

    if (!container) {
        console.error('Container not found!');
        return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');

    if (!id) {
        container.innerHTML = '<p class="text-danger">Aucun covoiturage sélectionné.</p>';
        return;
    }

    const apiUrl = `${API_BASE_URL}/api/carpooling/${id}`;

    try {
        const result = await fetchApi(apiUrl);
        console.log('Results JSON carpooling', result);

        const carpooling = new Carpooling(result);

        container.innerHTML = ''; // Vide le conteneur

        // Ajoute la carte du covoiturage
        container.appendChild(carpooling.toDetailCarpooling());

        // Si un conducteur existe, ajoute sa carte séparée
        if (carpooling.driver) {
            container.appendChild(carpooling.toDriverCardElement());
        } else {
            const noDriverInfo = document.createElement('div');
            noDriverInfo.className = 'col-12 col-md-8 mx-auto mt-4'; // Pour l'alignement et la marge
            noDriverInfo.innerHTML = '<div class="card shadow p-4"><p class="text-danger mb-0">Aucune information sur le conducteur disponible.</p></div>';
            container.appendChild(noDriverInfo);
        }

        const currentUrlParams = new URLSearchParams(window.location.search);
        currentUrlParams.set('page', 'detail');
        window.history.replaceState(null, '', `?${currentUrlParams.toString()}`);

    } catch (error) {
        console.error(error);
        container.innerHTML = '<p class="text-danger">Erreur lors du chargement du covoiturage.</p>';
    }
})();