
// =============================================================================
// I. Constants and Messages
// =============================================================================

const MESSAGES = {
    SEATS_AVAILABLE: (count) => `Il reste ${count} place${count > 1 ? 's' : ''} pour ce covoiturage.`,
    ECO_FRIENDLY_TEXT: 'En choisissant ce trajet, vous contribuez à une planète plus verte',
    NON_ECO_TEXT: 'Ce trajet ne profite pas d\'une empreinte carbone réduite. <br>Prenez le temps de les comparer :)</br>'
};

// =============================================================================
// II. Carpooling Detail Card Creation Function
// =============================================================================

/**
 * Creates and returns a DOM element representing the detailed carpooling card.
 * This card displays comprehensive journey information including dates, times, places,
 * available seats, price, and eco-friendliness.
 *
 * @param {object} data - The carpooling data object.
 * @param {function(string): string} formatDateToFrench - Utility function to format a date to French string.
 * @param {function(string): string} formatTime - Utility function to format an ISO string to a time string.
 * @returns {HTMLElement} The wrapper div containing the detailed carpooling card.
 */
export function createCarpoolDetailCardElement(data, formatDateToFrench, formatTime) {
    // Create the main wrapper for the card.
    const wrapper = document.createElement('div');
    wrapper.className = 'mb-4 w-100 px-2 ms-0 w-md-60';

    // Create the card element with styling.
    const card = document.createElement('div');
    card.className = 'detail-card card shadow w-100';

    // Format departure and arrival dates/times.
    const departureDateFormatted = formatDateToFrench(data.departureDate);
    const departureTimeFormatted = formatTime(data.departureTime);
    const arrivalTimeFormatted = formatTime(data.arrivalTime);

    // Set the inner HTML of the card using a template literal.
    // This includes formatted details, available seats, price, and eco-friendly information.
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
                ${MESSAGES.SEATS_AVAILABLE(data.availableSeats)}
            </div>

            <div class="d-flex align-items-center mb-3">
                ${data.isEco ? `
                    <div class="detail-eco-icon">🍃</div>
                    <span class="ms-3 detail-text">${MESSAGES.ECO_FRIENDLY_TEXT}</span>
                ` : `
                    <span class="ms-3 detail-text">${MESSAGES.NON_ECO_TEXT}</span>
                `}
            </div>
        </div>
    `;

    // Append the created card to the wrapper and return it.
    wrapper.appendChild(card);
    return wrapper;
}