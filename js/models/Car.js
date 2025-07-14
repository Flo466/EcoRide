import { getCarCardElement } from '../templates/carCardContent.js';

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

    this.brand = (typeof data.brand === 'object' && data.brand !== null && !Array.isArray(data.brand))
      ? data.brand
      : null;

    this.user = data.user ?? null;
    this.carpoolings = data.carpoolings ?? [];

    this.createdAt = data.createdAt ? new Date(data.createdAt) : new Date();
    this.updatedAt = data.updatedAt ? new Date(data.updatedAt) : null;
  }

  getFullName() {
    const brandName = this.brand?.label || 'Marque inconnue';
    return `${brandName} ${this.model || 'Modèle inconnu'}`.trim();
  }

  getFormattedRegistrationDate() {
    if (this.firstRegistrationDate) {
      try {
        const date = new Date(this.firstRegistrationDate);
        if (!isNaN(date.getTime())) {
          return date.toLocaleDateString('fr-FR');
        }
      } catch (e) {
        console.error("Erreur lors du parsing de la date d'immatriculation:", this.firstRegistrationDate, e);
      }
    }
    return 'Non spécifiée';
  }

  /**
   * Délègue la création de la carte DOM à la vue.
   *
   * @param {function} displayMessageCallback
   * @param {function} deleteVehicleCallback
   * @returns {HTMLElement}
   */
  toCarCardElement(displayMessageCallback, deleteVehicleCallback) {
    return getCarCardElement(this, displayMessageCallback, deleteVehicleCallback);
  }
}

export default Car;
