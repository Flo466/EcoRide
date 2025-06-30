// src/templates/carpoolingDetailCardContent.js

export function createCarpoolDetailCardElement(data, formatDateToFrench, formatTime) {
    const wrapper = document.createElement('div');
    wrapper.className = 'mb-4 w-100 px-2 ms-0 w-md-60';

    const card = document.createElement('div');
    card.className = 'detail-card card shadow w-100';

    const departureDateFormatted = formatDateToFrench(data.departureDate);
    const departureTimeFormatted = formatTime(data.departureTime);
    const arrivalTimeFormatted = formatTime(data.arrivalTime);

    card.innerHTML = `
        <div class="card-body pb-0"> 
          <div class="mb-3 text-start">
              <h1 class="mb-0 ms-2 date">${departureDateFormatted}</h1>
          </div>

          <div class="d-flex justify-content-between">
              <div class="d-flex">
                  <div class="mt-1">
                      <img src="assets/images/Arrow 6.png" alt="Trajet" class="detail-route-image me-2">
                  </div>
                  <div>
                      <p class="fw-bold">${data.departurePlace} - ${departureTimeFormatted}</p>
                      <p class="fw-bold mt-4">${data.arrivalPlace} - ${arrivalTimeFormatted}</p>
                  </div>
              </div>
              <div class="d-flex justify-content-end align-items-center">
                  <div class="detail-price">${data.pricePerPerson.toFixed(2)}</div>
                  <div class="detail-currency-icon"><i class="bi bi-coin"></i></div>
              </div>
          </div>

          <div class="d-flex align-items-center mb-3 ms-3">
            Il reste ${data.seatCount} place${data.seatCount > 1 ? 's' : ''} pour ce covoiturage.
          </div>

          <div class="d-flex align-items-center mb-3">
              ${data.isEco ? `
                  <div class="detail-eco-icon">🍃</div>
                  <span class="ms-3 detail-text">En choisissant ce trajet, vous contribuez à une planète plus verte</span>
              ` : `
                  <span class="ms-3 detail-text">Ce trajet ne profite pas d'une empreinte carbone réduite. <br>Prenez le temps de les comparer :)</br></span>
              `}
          </div>
        </div>
    `;

    wrapper.appendChild(card);
    return wrapper;
}