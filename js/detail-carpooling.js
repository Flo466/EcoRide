import { fetchApi } from './api/fetch.js'; 
import { API_BASE_URL } from './config.js';
import { Carpooling } from './models/Carpooling.js';

(async () => {
  const container = document.getElementById('detail-carpooling-container');
  const backButton = document.getElementById('back-button');

  if (backButton) {
    backButton.addEventListener('click', (e) => {
      e.preventDefault();
      if (document.referrer) {
        window.history.back();
      } else {
        window.location.href = '/covoiturages';
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

    const carpooling = new Carpooling(result);

    container.innerHTML = '';
    container.appendChild(carpooling.toDetailCarpooling());

    const currentUrlParams = new URLSearchParams(window.location.search);
    currentUrlParams.set('page', 'detail');
    window.history.replaceState(null, '', `?${currentUrlParams.toString()}`);

  } catch (error) {
    console.error(error);
    container.innerHTML = '<p class="text-danger">Erreur lors du chargement du covoiturage.</p>';
  }
})();
