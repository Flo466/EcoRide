import { getCarCardHtml } from '../templates/carCardContent.js';

class Car {
  constructor(data = {}) {
    this.id = data.id ?? null;
    this.model = data.model ?? '';
    this.color = data.color ?? null;
    this.licencePlate = data.licencePlate ?? '';
    this.energy = data.energy ?? '';
    this.firstRegistrationDate = data.firstRegistrationDate ?? null;
    this.seats = data.seats ?? null;
    this.petsAllowed = data.petsAllowed ?? false;

    this.brand = data.brand ?? null;
    this.user = data.user ?? null;

    this.carpoolings = data.carpoolings ?? [];

    this.createdAt = data.createdAt ? new Date(data.createdAt) : new Date();
    this.updatedAt = data.updatedAt ? new Date(data.updatedAt) : null;
  }

  getFullName() {
    const brandName = typeof this.brand === 'object' && this.brand !== null ? this.brand.label : this.brand;
    return `${brandName ?? ''} ${this.model}`.trim();
  }

  /**
   * @returns {string}
   */
  getFormattedRegistrationDate() {
    // Vérifie si la date existe et n'est pas une chaîne vide
    if (this.firstRegistrationDate) {
      try {
        const date = new Date(this.firstRegistrationDate);
        // Vérifie si l'objet Date est valide (getTime() retourne NaN pour une date invalide)
        if (!isNaN(date.getTime())) {
          return date.toLocaleDateString('fr-FR');
        }
      } catch (e) {
        // Log l'erreur de parsing pour le débogage
        console.error("Erreur lors du parsing de la date d'immatriculation:", this.firstRegistrationDate, e);
      }
    }
    return 'Non spécifiée'; // Retourne 'Non spécifiée' si la date est nulle, vide ou invalide
  }

  /**
   * Crée un élément DOM HTML représentant la carte du véhicule.
   * Attache les écouteurs d'événements pour les boutons Modifier et Supprimer.
   *
   * @param {function} displayMessageCallback - Fonction de rappel pour afficher des messages.
   * @param {function} deleteVehicleCallback - Fonction de rappel pour supprimer un véhicule.
   * @returns {HTMLElement} - L'élément div HTML de la carte du véhicule.
   */
  toCarCardElement(displayMessageCallback, deleteVehicleCallback) {
    const vehicleCard = document.createElement('div');
    vehicleCard.classList.add('card', 'mb-3', 'shadow-sm', 'animate-fade-in'); // Ajoute des classes Bootstrap pour le style

    // Utilise le template pour générer le HTML interne
    vehicleCard.innerHTML = getCarCardHtml(this); // 'this' fait référence à l'instance de Car

    // Ajoute les écouteurs d'événements pour les boutons Modifier et Supprimer
    const editButton = vehicleCard.querySelector('.edit-vehicle-btn');
    if (editButton) {
      editButton.addEventListener('click', () => {
        console.log(`Modifier le véhicule avec l'ID: ${this.id}`);
        // Logique de modification ici, par exemple redirection vers un formulaire d'édition
        // window.location.href = `/edit-car/${this.id}`;
        displayMessageCallback(`Fonctionnalité de modification pour le véhicule ${this.id} à implémenter.`, 'info');
      });
    }

    const deleteButton = vehicleCard.querySelector('.delete-vehicle-btn');
    if (deleteButton) {
      deleteButton.addEventListener('click', async () => {
        console.log(`Supprimer le véhicule avec l'ID: ${this.id}`);
        if (confirm(`Êtes-vous sûr de vouloir supprimer le véhicule ${this.getFullName()} ?`)) {
          await deleteVehicleCallback(this.id);
        }
      });
    }

    return vehicleCard;
  }
}

export default Car;
