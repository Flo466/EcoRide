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

    this.brand = (typeof data.brand === 'object' && data.brand !== null && !Array.isArray(data.brand))
      ? data.brand
      : null;


    this.user = data.user ?? null;

    this.carpoolings = data.carpoolings ?? [];

    this.createdAt = data.createdAt ? new Date(data.createdAt) : new Date();
    this.updatedAt = data.updatedAt ? new Date(data.updatedAt) : null;
  }

  getFullName() {
    // Si this.brand est null (car on l'a nettoyé dans le constructeur), brandName sera vide.
    const brandName = this.brand?.label || 'Marque inconnue';
    return `${brandName} ${this.model || 'Modèle inconnu'}`.trim();
  }

  /**
   * @returns {string}
   */
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
   * Crée un élément DOM HTML représentant la carte du véhicule.
   * Attache les écouteurs d'événements pour les boutons Modifier et Supprimer.
   *
   * @param {function} displayMessageCallback - Fonction de rappel pour afficher des messages.
   * @param {function} deleteVehicleCallback - Fonction de rappel pour supprimer un véhicule.
   * @returns {HTMLElement} - L'élément div HTML de la carte du véhicule.
   */
  toCarCardElement(displayMessageCallback, deleteVehicleCallback) {
    const vehicleCardWrapper = document.createElement('div');
    // On garde col-12 pour mobile, col-md-6 pour desktop (deux par ligne)
    // mb-4 pour la marge verticale
    vehicleCardWrapper.classList.add('col-12', 'col-md-6', 'mb-4');
    // ✨ Important : Ajouter les classes de centrage flexbox ici pour centrer la carte dans sa colonne
    vehicleCardWrapper.classList.add('d-flex', 'justify-content-center');


    const vehicleCard = document.createElement('div');
    vehicleCard.classList.add('card', 'shadow-sm', 'animate-fade-in');
    vehicleCard.style.width = '100%';
    vehicleCard.style.maxWidth = '550px';


    vehicleCard.innerHTML = getCarCardHtml(this);

    const deleteButton = vehicleCard.querySelector('.delete-vehicle-btn');
    if (deleteButton) {
      deleteButton.addEventListener('click', async () => {
        console.log(`Supprimer le véhicule avec l'ID: ${this.id}`);
        if (confirm(`Êtes-vous sûr de vouloir supprimer le véhicule ${this.getFullName()} ?`)) {
          await deleteVehicleCallback(this.id);
        }
      });
    }

    vehicleCardWrapper.appendChild(vehicleCard);
    return vehicleCardWrapper;
  }
}

export default Car;