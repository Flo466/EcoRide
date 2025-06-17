import { fetchApi } from '../api/fetch.js';

export function createCarpoolingSearchComponent(targetElementId) {
    const targetElement = document.getElementById(targetElementId);
    if (!targetElement) {
        console.error(`Element with ID "${targetElementId}" hasn't been found.`);
        return;
    }

    const htmlContent = `
    <div class="row justify-content-center">
      <div class="col-9 col-md-7 col-lg-9 col-xl-9">
        <form class="search-form shadow bg-white overflow-hidden">
          <div class="d-flex flex-column flex-lg-row align-items-stretch w-100">
              <input type="text" class="form-control text-center border-switch b-s-t" id="departurePlace" placeholder="Départ">
              <input type="text" class="form-control text-center border-switch" id="arrivalPlace" placeholder="Destination">
              <input type="date" class="form-control text-center border-switch border-0" id="departureDate">
              <button type="submit" class="btn text-white fw-semibold w-100 w-lg-auto btn-search">
                  Rechercher un trajet
              </button>
          </div>
        </form>
      </div>
    </div>
  `;

    targetElement.innerHTML = htmlContent;

    const form = targetElement.querySelector('.search-form');
    const departurePlaceInput = targetElement.querySelector('#departurePlace');
    const arrivalPlaceInput = targetElement.querySelector('#arrivalPlace');
    const departureDateInput = targetElement.querySelector('#departureDate');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        
        const departurePlace = departurePlaceInput.value;
        const arrivalPlace = arrivalPlaceInput.value;
        const departureDate = departureDateInput.value;

        const params = new URLSearchParams();
        if (departurePlace) params.append('departurePlace', departurePlace);
        if (arrivalPlace) params.append('arrivalPlace', arrivalPlace);
        if (departureDate) params.append('departureDate', departureDate);

        const url = `/api/carpoolings/search?${params.toString()}`;

        try {
            const result = await fetchApi(url);
            console.log('Search results:', result);
            // Here to display results
        } catch (error) {
            alert('Error searching carpooling : ' + error.message);
        }
    });
}
