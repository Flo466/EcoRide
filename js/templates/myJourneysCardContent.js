
// =============================================================================
// I. Constants and Messages
// =============================================================================

// User-facing messages
const MESSAGES = {
    DRIVER_BADGE: 'Chauffeur',
    PASSENGER_BADGE: 'Passager',
    DELETE_BUTTON_TEXT: 'Supprimer'
};

// =============================================================================
// II. Journey Card Creation Function
// =============================================================================

/**
 * Creates and returns a DOM element representing a carpooling journey card.
 * This card displays journey details, identifies the user's role (driver/passenger),
 * and includes a delete button.
 *
 * @param {object} carpooling - The carpooling data object.
 * @param {function(string): string} formatDateToFrench - Utility function to format a date to French string.
 * @param {function(string): string} formatTime - Utility function to format an ISO string to a time string.
 * @returns {HTMLElement} The wrapper div containing the journey card.
 */
export function createJourneyCardElement(carpooling, formatDateToFrench, formatTime) {
    // Console logs for debugging the carpooling object and user role.
    console.log('Carpooling object received in createJourneyCardElement:', carpooling);
    console.log('Value of isCurrentUserDriver:', carpooling.isCurrentUserDriver);

    // Create the main wrapper for the card, ensuring proper layout.
    const wrapper = document.createElement('div');
    wrapper.className = 'col-12 col-md-6 mb-4 d-flex justify-content-center';

    // Create the card element itself with styling.
    const card = document.createElement('div');
    card.className = 'detail-card card shadow-sm animate-fade-in';
    card.style.width = '90%';

    // Format date and time strings using the provided utility functions.
    const departureDateFormatted = formatDateToFrench(carpooling.departureDate);
    const departureTimeFormatted = formatTime(carpooling.departureTime);
    const arrivalTimeFormatted = formatTime(carpooling.arrivalTime);

    // Determine the user's role (driver or passenger) and set the corresponding badge HTML.
    const driverIndicator = carpooling.isCurrentUserDriver ? `
        <span class="badge bg-primary driver-badge">
            ${MESSAGES.DRIVER_BADGE}
        </span>` : `
        <span class="badge bg-white text-primary passenger-badge">
            ${MESSAGES.PASSENGER_BADGE}
        </span>`;

    // Set the inner HTML of the card using a template literal for easy content insertion.
    // This includes formatted dates/times, driver/passenger indicator, and action buttons.
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
                    <i class="bi bi-trash"></i> ${MESSAGES.DELETE_BUTTON_TEXT}
                </button>
            </div>
        </div>
    `;

    // Append the created card to the wrapper and return the wrapper.
    wrapper.appendChild(card);
    return wrapper; // Event listeners for buttons will be attached externally.
}