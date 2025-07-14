export function createJourneyCardElement(data, formatDateToFrench, formatTime) {
    const wrapper = document.createElement('div');
    wrapper.className = 'col-12 col-md-6 mb-4 d-flex justify-content-center';

    const card = document.createElement('div');
    card.className = 'detail-card card shadow animated-fade-in';

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
      </div>
    </div>
  `;

    wrapper.appendChild(card);
    return wrapper;
}
