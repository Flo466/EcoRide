// src/templates/carpoolingDriverCardContent.js

const DEFAULT_PROFILE_IMAGE = 'assets/images/profil.jpg';
const FALLBACK_DRIVER_NAME = 'Conducteur';

export function createCarpoolDriverCardElement(data) {
    if (!data.driver) {
        return null;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'mb-4 w-100 px-2 ms-0 w-md-60';

    const driverCard = document.createElement('div');
    driverCard.className = 'detail-card card shadow w-100';

    const carBrandLabel = data.car && data.car.brand ? data.car.brand.label : "Non renseignée";
    const carModel = data.car ? data.car.model : "";
    const carEnergy = data.car ? data.car.energy : "";
    let ratingHtml = '';
    if (data.driver.averageRating && data.driver.averageRating > 0) {
        ratingHtml = `<span class="fs-"6>${data.driver.averageRating.toFixed(1)}</span> <i class="bi bi-star-fill"></i>`;
    } else {
        ratingHtml = "Ce chauffeur n'a pas encore de note";
    }

    driverCard.innerHTML = `
        <div class="card-body pb-0">
          <h2 class="mb-3 text-start user-card-title">Trajet proposé par</h2>
          <div class="d-flex align-items-center mb-3 driver-info-row">
              <img class="driver-img-detail" src="${data.driver.photoBase64 || DEFAULT_PROFILE_IMAGE}" alt="${data.driver.userName || FALLBACK_DRIVER_NAME}">
              <div class="driver-name-rating">
                  <p class="mb-0 fs-5">${data.driver.userName}</p>
                   <p class="mb-0 driver-rating">${ratingHtml} </p>
              </div>
          </div>
          <div class="driver-car-details mb-4">
              <div class="d-flex align-items-center mb-2 driver-car-model">
                  <i class="bi bi-car-front-fill ms-2 me-2"></i>
                  <p class="mb-0 car-label">${carBrandLabel} ${carModel}</p>
              </div>
              <p class="ms-2 car-label driver-car-energy">Véhicule ${carEnergy}</p>
          </div>
        </div>
    `;

    wrapper.appendChild(driverCard);
    return wrapper;
}