
// =============================================================================
// I. Constants and Messages
// =============================================================================

const DEFAULT_PROFILE_IMAGE = 'assets/images/profil.jpg';
const FALLBACK_DRIVER_NAME = 'Conducteur';

const MESSAGES = {
    JOURNEY_PROPOSED_BY: 'Trajet proposé par',
    CAR_BRAND_NOT_SPECIFIED: 'Non renseignée',
    NO_RATING: 'Aucune note',
    VEHICLE: 'Véhicule'
};

// =============================================================================
// II. Carpooling Driver Card Creation Function
// =============================================================================

/**
 * Creates and returns a DOM element representing the carpooling driver's card.
 * This card displays information about the driver and their vehicle.
 *
 * @param {object} data - The carpooling data object, containing driver and car details.
 * @returns {HTMLElement|null} The wrapper div containing the driver card, or null if no driver data.
 */
export function createCarpoolDriverCardElement(data) {
    // Return null if driver data is missing.
    if (!data.driver) {
        return null;
    }

    // Create the main wrapper for the driver card.
    const wrapper = document.createElement('div');
    wrapper.className = 'mb-4 w-100 px-2 ms-0 w-md-60';

    // Create the card element with styling.
    const driverCard = document.createElement('div');
    driverCard.className = 'detail-card card shadow w-100';

    // Extract car details, providing default values if not available.
    const carBrandLabel = data.car && data.car.brand ? data.car.brand.label : MESSAGES.CAR_BRAND_NOT_SPECIFIED;
    const carModel = data.car ? data.car.model : '';
    const carEnergy = data.car ? data.car.energy : '';

    // Determine the rating HTML based on driver's average rating.
    let ratingHtml = '';
    if (data.driver.averageRating && data.driver.averageRating > 0) {
        ratingHtml = `<span class="fs-"6>${data.driver.averageRating.toFixed(1)}</span> <i class="bi bi-star-fill"></i>`;
    } else {
        ratingHtml = MESSAGES.NO_RATING;
    }

    // Set the inner HTML of the driver card using a template literal.
    driverCard.innerHTML = `
        <div class="card-body pb-0">
            <h2 class="mb-3 text-start user-card-title">${MESSAGES.JOURNEY_PROPOSED_BY}</h2>
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
                <p class="ms-2 car-label driver-car-energy">${MESSAGES.VEHICLE} ${carEnergy}</p>
            </div>
        </div>
    `;

    // Append the created driver card to the wrapper and return it.
    wrapper.appendChild(driverCard);
    return wrapper;
}