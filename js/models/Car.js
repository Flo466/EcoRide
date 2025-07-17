import { getCarCardElement } from '../templates/carCardContent.js';

/**
 * Represents a Car entity with properties initialized from provided data.
 */
class Car {
  /**
   * @param {object} data - The data object to construct a Car.
   */
  constructor(data = {}) {
    this.id = data.id ?? null;
    this.model = data.model ?? '';
    this.color = data.color ?? null;
    this.licencePlate = data.licencePlate ?? '';
    this.energy = data.energy ?? '';
    this.firstRegistrationDate = data.firstRegistrationDate ?? null;
    this.seats = data.seats ?? null;
    this.petsAllowed = data.petsAllowed ?? false;

    // Ensure 'brand' is a non-array object.
    this.brand = (typeof data.brand === 'object' && data.brand !== null && !Array.isArray(data.brand))
      ? data.brand
      : null;

    this.user = data.user ?? null; // The user owning the car.
    this.carpoolings = data.carpoolings ?? []; // Associated carpooling instances.

    this.createdAt = data.createdAt ? new Date(data.createdAt) : new Date();
    this.updatedAt = data.updatedAt ? new Date(data.updatedAt) : null;
  }

  /**
   * Returns the full name of the car (Brand Model).
   * @returns {string} - The full name of the car.
   */
  getFullName() {
    const brandName = this.brand?.label || 'Marque inconnue';
    return `${brandName} ${this.model || 'Modèle inconnu'}`.trim();
  }

  /**
   * Returns the formatted first registration date in French locale.
   * @returns {string} - The formatted date string, or 'Non spécifiée' if invalid.
   */
  getFormattedRegistrationDate() {
    if (this.firstRegistrationDate) {
      try {
        const date = new Date(this.firstRegistrationDate);
        if (!isNaN(date.getTime())) {
          return date.toLocaleDateString('fr-FR');
        }
      } catch (e) {
        console.error("Error parsing registration date:", this.firstRegistrationDate, e);
      }
    }
    return 'Non spécifiée';
  }

  /**
   * Delegates the creation of the DOM card element to a template function.
   * @param {function} displayMessageCallback - Callback function to display messages.
   * @param {function} deleteVehicleCallback - Callback function to handle vehicle deletion.
   * @returns {HTMLElement} - The HTML element representing the car card.
   */
  toCarCardElement(displayMessageCallback, deleteVehicleCallback) {
    return getCarCardElement(this, displayMessageCallback, deleteVehicleCallback);
  }
}

export default Car;