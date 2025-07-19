// =============================================================================
// I. Constants and Messages
// =============================================================================

const DEFAULT_PROFILE_IMAGE = 'assets/images/profil.jpg';

const MESSAGES = {
    FALLBACK_DRIVER_NAME: 'Conducteur',
    SEATS_REMAINING: (count) => `${count} place${count > 1 ? 's' : ''} restante${count > 1 ? 's' : ''}`
};

// =============================================================================
// II. Carpooling Card Creation Function
// =============================================================================

export function createCarpoolCardElement(data, formatDateToFrench, formatTime) {
    const wrapper = document.createElement('div');
    wrapper.className = 'mb-4 w-100 px-2';

    const card = document.createElement('div');
    card.className = 'carpool-card card shadow w-100';

    const departureDateFormatted = formatDateToFrench(data.departureDate);
    const depTimeFormatted = formatTime(data.departureTime) || '';
    const arrTimeFormatted = formatTime(data.arrivalTime) || '';
    const depPlaceFormatted = data.departurePlace || '';
    const arrPlaceFormatted = data.arrivalPlace || '';

    let ratingHtml = '';
    if (data.driver && typeof data.driver.averageRating === 'number' && data.driver.averageRating !== null) {
        ratingHtml = `<span class="fs-6 align-baseline">${data.driver.averageRating}</span> <i class="bi bi-star-fill text-warning align-baseline"></i>`;
    } else {
        ratingHtml = `<span class="fs-6 text-muted">Non noté</span>`;
    }

    wrapper.dataset.id = data.id;
    wrapper.addEventListener('click', () => {
        window.location.href = `detail-carpooling?id=${data.id}`;
    });

    card.innerHTML = `
    <div class="card-body pb-0">
        <div class=" text-start">
            <h1 class="mb-0 ms-2 date">${departureDateFormatted}</h1>
        </div>
        <div class="card-body d-flex justify-content-between">
            <div class="d-flex">
                <div class="mt-1">
                    <img src="assets/images/Arrow 6.png" alt="Trajet" class="route-image me-2">
                </div>
                <div>
                    <p class="fw-bold">${depPlaceFormatted} - ${depTimeFormatted}</p>
                    <p class="fw-bold mt-4">${arrPlaceFormatted} - ${arrTimeFormatted}</p>
                </div>
            </div>
            <div class="d-flex justify-content-end align-items-center">
                <div class="price">${data.pricePerPerson}</div>
                <div class="currency-icon"><i class="bi bi-coin"></i></div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center px-4">
            <p class="text-muted mb-0">${MESSAGES.SEATS_REMAINING(data.availableSeats)}</p>
            ${data.isEco ? `<div class="eco-icon">🍃</div>` : ''}
        </div>

        <div class="driver-section d-flex align-items-center">
            <img class="driver-img" src="${data.driver.photoBase64 || DEFAULT_PROFILE_IMAGE}" alt="${data.driver.userName || MESSAGES.FALLBACK_DRIVER_NAME}">
            <div class="driver-name-rating d-flex flex-column flex-md-row align-items-baseline ms-2">
                <p class="mb-2 fs-6 lh-1">${data.driver.userName || MESSAGES.FALLBACK_DRIVER_NAME}</p>
                <p class="mb-0 fs-6 lh-1 ms-md-2">${ratingHtml}</p> </div>
        </div>
    </div>
    `;

    wrapper.appendChild(card);
    return wrapper;
}