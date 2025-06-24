import { fetchApi } from './api/fetch.js'; 
import { API_BASE_URL } from './config.js'; 
import { displaySearchResults } from './components/displayResults.js'; 

(async () => {
    const form = document.querySelector('.search-form');
    const departurePlaceInput = document.getElementById('departurePlace');
    const arrivalPlaceInput = document.getElementById('arrivalPlace');
    const departureDateInput = document.getElementById('departureDate');
    const resultsContainer = document.getElementById('carpooling-results');

    if (!form) return;

    async function executeSearch(depart, arrivee, date) {
        resultsContainer.innerHTML = '<p class="no-results">Recherche en cours...</p>'; 

        const searchParams = new URLSearchParams();
        if (depart) searchParams.append('departurePlace', depart);
        if (arrivee) searchParams.append('arrivalPlace', arrivee);
        if (date) searchParams.append('departureDate', date);

        if (!depart && !arrivee && !date) {
            resultsContainer.innerHTML = '<p class="no-results">Veuillez entrer des critères de recherche pour trouver un covoiturage.</p>';
            return;
        }

        const apiUrl = `${API_BASE_URL}/api/carpooling/search?${searchParams.toString()}`;

        try {
            const result = await fetchApi(apiUrl);
            displaySearchResults(result, 'carpooling-results'); 

            const currentUrlParams = new URLSearchParams(window.location.search);
            currentUrlParams.set('page', 'carpooling');
            if (depart) currentUrlParams.set('departurePlace', depart); else currentUrlParams.delete('departurePlace');
            if (arrivee) currentUrlParams.set('arrivalPlace', arrivee); else currentUrlParams.delete('arrivalPlace');
            if (date) currentUrlParams.set('departureDate', date); else currentUrlParams.delete('departureDate');

            window.history.pushState(null, '', `?${currentUrlParams.toString()}`);
        } catch (error) {
            resultsContainer.innerHTML = `<p class="no-results" style="color: red;">Désolé, une erreur est survenue : ${error.message}.</p>`;
        }
    }

    const paramsFromUrl = new URLSearchParams(window.location.search);
    const initialDeparturePlace = paramsFromUrl.get('departurePlace');
    const initialArrivalPlace = paramsFromUrl.get('arrivalPlace');
    const initialDepartureDate = paramsFromUrl.get('departureDate');

    if (departurePlaceInput && initialDeparturePlace) departurePlaceInput.value = initialDeparturePlace;
    if (arrivalPlaceInput && initialArrivalPlace) arrivalPlaceInput.value = initialArrivalPlace;
    if (departureDateInput && initialDepartureDate) departureDateInput.value = initialDepartureDate;

    if (initialDeparturePlace || initialArrivalPlace || initialDepartureDate) {
        await executeSearch(initialDeparturePlace, initialArrivalPlace, initialDepartureDate);
    } else {
        resultsContainer.innerHTML = `<p class="no-results">Veuillez utiliser le formulaire ci-dessus pour trouver des covoiturages.</p>`;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const newDeparturePlace = departurePlaceInput.value;
        const newArrivalPlace = arrivalPlaceInput.value;
        const newDepartureDate = departureDateInput.value;
        await executeSearch(newDeparturePlace, newArrivalPlace, newDepartureDate);
    });
})();
