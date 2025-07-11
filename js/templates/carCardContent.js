/**
 *
 * @param {object} vehicle
 * @returns {string}
 */
export const getCarCardHtml = (vehicle) => {
    const petsAllowedText = vehicle.petsAllowed === true ? 'Oui' : 'Non';
    const firstRegistrationDate = vehicle.getFormattedRegistrationDate
        ? vehicle.getFormattedRegistrationDate()
        : 'Non spécifiée';

    const fullCarName = vehicle.getFullName();

    return `
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
            <div class="d-flex justify-content-center"> <button class="btn btn-sm btn-outline-danger delete-vehicle-btn" data-id="${vehicle.id}">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
            </div>
        </div>
    `;
};