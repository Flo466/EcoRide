// =============================================================================
// I. Constants and Messages
// =============================================================================

const MESSAGES = {
    LICENCE_PLATE_UNSPECIFIED: 'Plaque non spécifiée',
    COLOR_UNSPECIFIED: 'Non spécifiée',
    ENERGY_UNSPECIFIED: 'Non spécifiée',
    SEATS_UNSPECIFIED: 'Non spécifié',
    YES: 'Oui',
    NO: 'Non',
    DELETE_BUTTON_TEXT: 'Supprimer',
    CONFIRM_DELETE: (carName) => `Êtes-vous sûr de vouloir supprimer le véhicule ${carName} ?`
};

// =============================================================================
// II. Car Card Creation Function
// =============================================================================

/**
 * Creates a complete DOM element for a car card.
 *
 * @param {Car} vehicle - The Car object containing vehicle details.
 * @param {function} displayMessageCallback - Callback function to display messages to the user.
 * @param {function} deleteVehicleCallback - Callback function to handle vehicle deletion.
 * @returns {HTMLElement} The wrapper div containing the car card.
 */
export function getCarCardElement(vehicle, displayMessageCallback, deleteVehicleCallback) {
    // Get formatted car name and registration date from the vehicle object.
    const fullCarName = vehicle.getFullName();
    const firstRegistrationDate = vehicle.getFormattedRegistrationDate();
    // Determine text for pets allowed status.
    const petsAllowedText = vehicle.petsAllowed ? MESSAGES.YES : MESSAGES.NO;

    // Create the main wrapper for the car card.
    const wrapper = document.createElement('div');
    wrapper.classList.add('col-12', 'col-md-6', 'mb-4', 'd-flex', 'justify-content-center');

    // Create the card element with styling.
    const card = document.createElement('div');
    card.classList.add('card', 'shadow-sm', 'animate-fade-in');
    card.style.width = '100%';
    card.style.maxWidth = '550px';

    // Set the inner HTML of the card using a template literal.
    card.innerHTML = `
        <div class="card-body">
            <h5 class="card-title">${fullCarName}</h5>
            <h6 class="card-subtitle mb-2 text-muted">${vehicle.licencePlate || MESSAGES.LICENCE_PLATE_UNSPECIFIED}</h6>
            <p class="card-text">
                <strong>Couleur:</strong> ${vehicle.color || MESSAGES.COLOR_UNSPECIFIED}<br>
                <strong>Énergie:</strong> ${vehicle.energy || MESSAGES.ENERGY_UNSPECIFIED}<br>
                <strong>Places:</strong> ${vehicle.seats !== null && vehicle.seats !== undefined ? vehicle.seats : MESSAGES.SEATS_UNSPECIFIED}<br>
                <strong>Première immatriculation:</strong> ${firstRegistrationDate}<br>
                <strong>Animaux acceptés:</strong> ${petsAllowedText}
            </p>
            <div class="d-flex justify-content-center">
                <button class="btn btn-sm btn-outline-danger rounded-pill delete-vehicle-btn" data-id="${vehicle.id}">
                    <i class="bi bi-trash"></i> ${MESSAGES.DELETE_BUTTON_TEXT}
                </button>
            </div>
        </div>
    `;

    // Attach event listener to the delete button.
    const deleteButton = card.querySelector('.delete-vehicle-btn');
    if (deleteButton) {
        deleteButton.addEventListener('click', async () => {
            // Confirm deletion with the user before proceeding.
            if (confirm(MESSAGES.CONFIRM_DELETE(fullCarName))) {
                await deleteVehicleCallback(vehicle.id);
            }
        });
    }

    // Append the created card to the wrapper and return it.
    wrapper.appendChild(card);
    return wrapper;
}