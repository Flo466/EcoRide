/**
 * Crée un élément DOM complet pour une voiture.
 *
 * @param {Car} vehicle
 * @param {function} displayMessageCallback
 * @param {function} deleteVehicleCallback
 * @returns {HTMLElement}
 */
export function getCarCardElement(vehicle, displayMessageCallback, deleteVehicleCallback) {
    const fullCarName = vehicle.getFullName();
    const firstRegistrationDate = vehicle.getFormattedRegistrationDate();
    const petsAllowedText = vehicle.petsAllowed ? 'Oui' : 'Non';

    const wrapper = document.createElement('div');
    wrapper.classList.add('col-12', 'col-md-6', 'mb-4', 'd-flex', 'justify-content-center');

    const card = document.createElement('div');
    card.classList.add('card', 'shadow-sm', 'animate-fade-in');
    card.style.width = '100%';
    card.style.maxWidth = '550px';

    card.innerHTML = `
    <div class="card-body">
        <h5 class="card-title">${fullCarName}</h5>
        <h6 class="card-subtitle mb-2 text-muted">${vehicle.licencePlate || 'Plaque non spécifiée'}</h6>
        <p class="card-text">
            <strong>Couleur:</strong> ${vehicle.color || 'Non spécifiée'}<br>
            <strong>Énergie:</strong> ${vehicle.energy || 'Non spécifiée'}<br>
            <strong>Places:</strong> ${vehicle.seats !== null && vehicle.seats !== undefined ? vehicle.seats : 'Non spécifié'}<br>
            <strong>Première immatriculation:</strong> ${firstRegistrationDate}<br>
            <strong>Animaux acceptés:</strong> ${petsAllowedText}
        </p>
        <div class="d-flex justify-content-center">
            <button class="btn btn-sm btn-outline-danger rounded-pill delete-vehicle-btn" data-id="${vehicle.id}">
                <i class="bi bi-trash"></i> Supprimer
            </button>
        </div>
    </div>
  `;

    const deleteButton = card.querySelector('.delete-vehicle-btn');
    if (deleteButton) {
        deleteButton.addEventListener('click', async () => {
            if (confirm(`Êtes-vous sûr de vouloir supprimer le véhicule ${fullCarName} ?`)) {
                await deleteVehicleCallback(vehicle.id);
            }
        });
    }

    wrapper.appendChild(card);
    return wrapper;
}
