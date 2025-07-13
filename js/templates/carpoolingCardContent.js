const DEFAULT_PROFILE_IMAGE = 'assets/images/profil.jpg';
const FALLBACK_DRIVER_NAME = 'Conducteur';

export function createCarpoolCardElement(data, formatTime) {
    const wrapper = document.createElement('div');
    wrapper.className = 'mb-4 w-100 px-2';

    const card = document.createElement('div');
    card.className = 'carpool-card card shadow w-100';

    const depTimeFormatted = formatTime(data.departureTime) || "";
    const arrTimeFormatted = formatTime(data.arrivalTime) || "";
    const depPlaceFormatted = data.departurePlace || "";
    const arrPlaceFormatted = data.arrivalPlace || "";

    wrapper.dataset.id = data.id;
    wrapper.addEventListener('click', () => {
        window.location.href = `detail-carpooling?id=${data.id}`;
    });

    card.innerHTML = `
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
                <div class="price">${data.pricePerPerson.toFixed(2)}</div>
                <div class="currency-icon"><i class="bi bi-coin"></i></div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center px-4">
            <p class="text-muted mb-0">${data.seatCount} place${data.seatCount > 1 ? 's' : ''} restante${data.seatCount > 1 ? 's' : ''}</p>
            ${data.isEco ? `<div class="eco-icon">🍃</div>` : ''}
        </div>

        <div class="driver-section">
            <img class="driver-img-detail" src="${data.driver.photoBase64 || DEFAULT_PROFILE_IMAGE}" alt="${data.driver.userName || FALLBACK_DRIVER_NAME}">
            <div>
                <p class="mb-0 fs-5">${data.driver.userName || FALLBACK_DRIVER_NAME}</p>
                <div>☆ 5</div>
            </div>
        </div>
    `;

    wrapper.appendChild(card);
    return wrapper;
}