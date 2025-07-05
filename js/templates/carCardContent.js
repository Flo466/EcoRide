/**
 * Génère le contenu HTML pour le corps d'une carte de véhicule.
 * Cette fonction est un template pur, elle ne gère pas les écouteurs d'événements.
 *
 * @param {object} vehicle - Les données du véhicule (doit être une instance de Car ou un objet similaire).
 * @returns {string} - Le contenu HTML du corps de la carte.
 */
export const getCarCardHtml = (vehicle) => {
    // Détermine le texte pour "Animaux acceptés"
    // S'assure que petsAllowed est un booléen pour éviter les affichages inattendus
    const petsAllowedText = vehicle.petsAllowed === true ? 'Oui' : 'Non';

    // Détermine la date d'immatriculation
    // Utilise la méthode getFormattedRegistrationDate du modèle Car
    const firstRegistrationDate = vehicle.getFormattedRegistrationDate
        ? vehicle.getFormattedRegistrationDate()
        : 'Non spécifiée'; // Fallback si la méthode n'existe pas ou retourne une valeur vide

    return `
        <div class="card-body">
            <h5 class="card-title">${vehicle.brand?.label || 'Marque inconnue'} ${vehicle.model || 'Modèle inconnu'}</h5>
            <h6 class="card-subtitle mb-2 text-muted">${vehicle.licencePlate || 'Plaque non spécifiée'}</h6>
            <p class="card-text">
                <strong>Couleur:</strong> ${vehicle.color || 'Non spécifiée'}<br>
                <strong>Énergie:</strong> ${vehicle.energy || 'Non spécifiée'}<br>
                <strong>Places:</strong> ${vehicle.seats !== null && vehicle.seats !== undefined ? vehicle.seats : 'Non spécifié'}<br>
                <strong>Première immatriculation:</strong> ${firstRegistrationDate}<br>
                <strong>Animaux acceptés:</strong> ${petsAllowedText}
            </p>
            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-sm btn-outline-primary edit-vehicle-btn" data-id="${vehicle.id}">
                    <i class="bi bi-pencil"></i> Modifier
                </button>
                <button class="btn btn-sm btn-outline-danger delete-vehicle-btn" data-id="${vehicle.id}">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
            </div>
        </div>
    `;
};
