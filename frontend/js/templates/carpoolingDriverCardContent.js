// =============================================================================
// I. Constants and Messages
// =============================================================================

const DEFAULT_PROFILE_IMAGE = 'assets/images/profil.jpg';
const UPLOADS_BASE_PATH = '/uploads/'; // Chemin vers ton volume Docker
const FALLBACK_DRIVER_NAME = 'Conducteur';

const MESSAGES = {
    JOURNEY_PROPOSED_BY: 'Trajet proposé par',
    CAR_BRAND_NOT_SPECIFIED: 'Non renseignée',
    NO_RATING: 'Non noté',
    VEHICLE: 'Véhicule'
};

// =============================================================================
// II. Carpooling Driver Card Creation Function
// =============================================================================

/**
 * Creates and returns a DOM element representing the carpooling driver's card.
 */
export function createCarpoolDriverCardElement(data) {
    // Sécurité de base : si data est null, on évite le crash
    if (!data) return null;

    // Create the main wrapper for the driver card.
    const wrapper = document.createElement('div');
    wrapper.className = 'mb-4 w-100 px-2 ms-0 w-md-60';

    // Create the card element with styling.
    const driverCard = document.createElement('div');
    driverCard.className = 'detail-card card shadow w-100 animate-fade-in-up';

    // 1. Sécurisation des détails du véhicule (Utilisation de ?. et ??)
    const carBrandLabel = data.car?.brand?.label ?? MESSAGES.CAR_BRAND_NOT_SPECIFIED;
    const carModel = data.car?.model ?? '';
    const carEnergy = data.car?.energy ?? '';

    // 2. Sécurisation du rating (Utilisation de ?. pour le driver)
    let ratingHtml = '';
    const avgRating = data.driver?.averageRating;
    if (typeof avgRating === 'number' && avgRating !== null) {
        ratingHtml = `<span class="fs-6">${avgRating}</span> <i class="bi bi-star-fill text-warning"></i>`;
    } else {
        ratingHtml = `<span class="fs-6 text-muted">${MESSAGES.NO_RATING}</span>`;
    }

    // 3. Sécurisation des infos conducteur (Fix pour le chemin d'image)
    let driverPhoto = DEFAULT_PROFILE_IMAGE;
    // On check photoBase64 ou photo selon ce que ton API renvoie
    const rawPhoto = data.driver?.photoBase64 || data.driver?.photo;

    if (rawPhoto) {
        // Si c'veut dire que c'est un chemin (ex: avatars/man.jpg), on ajoute /uploads/
        // Si ça commence par http ou data:, on laisse tel quel.
        driverPhoto = (rawPhoto.startsWith('http') || rawPhoto.startsWith('data:image'))
            ? rawPhoto
            : UPLOADS_BASE_PATH + rawPhoto;
    }

    const driverName = data.driver?.userName || FALLBACK_DRIVER_NAME;

    // Set the inner HTML of the driver card using a template literal.
    driverCard.innerHTML = `
        <div class="card-body pb-0">
            <h2 class="mb-3 text-start user-card-title">${MESSAGES.JOURNEY_PROPOSED_BY}</h2>
            <div class="d-flex align-items-center mb-3 driver-info-row">
                <img class="driver-img-detail" 
                     src="${driverPhoto}" 
                     alt="${driverName}"
                     onerror="this.src='${DEFAULT_PROFILE_IMAGE}'">
                <div class="driver-name-rating">
                    <p class="mb-0 fs-5">${driverName}</p>
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

    wrapper.appendChild(driverCard);
    return wrapper;
}