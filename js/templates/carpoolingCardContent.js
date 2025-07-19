
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

/**
 * Creates and returns a DOM element representing a carpooling search result card.
 * This card displays essential journey details and driver information.
 *
 * @param {object} data - The carpooling data object.
 * @param {function(string): string} formatTime - Utility function to format an ISO string to a time string.
 * @returns {HTMLElement} The wrapper div containing the carpooling card.
 */
export function createCarpoolCardElement(data, formatDateToFrench, formatTime) {
    // Create the main wrapper for the card.
    const wrapper = document.createElement('div');
    wrapper.className = 'mb-4 w-100 px-2';

    // Create the card element with styling.
    const card = document.createElement('div');
    card.className = 'carpool-card card shadow w-100';

    // Format departure and arrival times and places, providing defaults if needed.
    const departureDateFormatted = formatDateToFrench(data.departureDate);
    const depTimeFormatted = formatTime(data.departureTime) || '';
    const arrTimeFormatted = formatTime(data.arrivalTime) || '';
    const depPlaceFormatted = data.departurePlace || '';
    const arrPlaceFormatted = data.arrivalPlace || '';

    // Generate HTML for driver's rating, if available.
    let ratingHtml = '';
    if (data.driver && data.driver.averageRating && data.driver.averageRating > 0) {
        ratingHtml = `<span class="fs-5">${data.driver.averageRating.toFixed(1)}</span> <i class="bi bi-star-fill text-warning"></i>`;
    }

    // Set a data attribute with the carpooling ID and attach a click listener for redirection.
    wrapper.dataset.id = data.id;
    wrapper.addEventListener('click', () => {
        window.location.href = `detail-carpooling?id=${data.id}`;
    });

    // Set the inner HTML of the card using a template literal.
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
                <div class="price">${data.pricePerPerson.toFixed(2)}</div>
                <div class="currency-icon"><i class="bi bi-coin"></i></div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center px-4">
            <p class="text-muted mb-0">${MESSAGES.SEATS_REMAINING(data.availableSeats)}</p>
            ${data.isEco ? `<div class="eco-icon">🍃</div>` : ''}
        </div>

        <div class="driver-section">
            <img class="driver-img" src="${data.driver.photoBase64 || DEFAULT_PROFILE_IMAGE}" alt="${data.driver.userName || MESSAGES.FALLBACK_DRIVER_NAME}">
            <div>
                <p class="mb-0 fs-6">${data.driver.userName || MESSAGES.FALLBACK_DRIVER_NAME}</p>
                <p class="driver-rating">${ratingHtml}</p>
            </div>
        </div>
    </div>
    `;

    // Append the created card to the wrapper and return it.
    wrapper.appendChild(card);
    return wrapper;
}