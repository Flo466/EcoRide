// src/templates/myJourneysCardContent.js

export function createJourneyCardElement(carpooling, formatDateToFrench, formatTime) {
  console.log('Objet Carpooling reçu dans createJourneyCardElement:', carpooling);
  console.log('Valeur de isCurrentUserDriver:', carpooling.isCurrentUserDriver);
  const wrapper = document.createElement('div');
  wrapper.className = 'col-12 col-md-6 mb-4 d-flex justify-content-center';

  const card = document.createElement('div');
  card.className = 'detail-card card shadow-sm animate-fade-in';
  card.style.width = '90%';

  const departureDateFormatted = formatDateToFrench(carpooling.departureDate);
  const departureTimeFormatted = formatTime(carpooling.departureTime);
  const arrivalTimeFormatted = formatTime(carpooling.arrivalTime);

  const driverIndicator = carpooling.isCurrentUserDriver ? `
      <span class="badge bg-primary driver-badge">
          Chauffeur
      </span>` : `
      <span class="badge bg-white text-primary passenger-badge">
          Passager
      </span>`;

  card.innerHTML = `
    <div class="card-body pb-0">
      <div class="mb-2 text-start d-flex align-items-center">
          <h1 class="mb-0 ms-2 date">${departureDateFormatted}</h1>
      </div>
      <div class="mb-2 ms-2">
        ${driverIndicator}
      </div>
      <div class="d-flex justify-content-between">
        <div class="d-flex">
          <div class="mt-1">
            <img src="assets/images/Arrow 6.png" alt="Trajet" class="detail-route-image me-2">
          </div>
          <div>
            <p class="fw-bold">${carpooling.departurePlace} - ${departureTimeFormatted}</p>
            <p class="fw-bold mt-4">${carpooling.arrivalPlace} - ${arrivalTimeFormatted}</p>
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-center mt-2 mb-3">
        <button class="btn btn-sm btn-outline-danger rounded-pill delete-item-btn" data-id="${carpooling.id}" data-type="journey">
          <i class="bi bi-trash"></i> Supprimer
        </button>
      </div>
    </div>
  `;

  wrapper.appendChild(card);
  return wrapper; // No event listener attached here
}