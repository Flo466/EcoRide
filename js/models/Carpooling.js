import { User } from './User.js';

export class Carpooling {
  constructor(data) {
    this.id = data.id;
    this.departureDate = data.departureDate
    this.departureTime = data.departureTime
    this.departurePlace = data.departurePlace;
    this.arrivalDate = data.arrivalDate
    this.arrivalTime = data.arrivalTime
    this.arrivalPlace = data.arrivalPlace;
    this.seatCount = data.seatCount;
    this.pricePerPerson = data.pricePerPerson;
    this.isEco = data.isEco;


    this.car = data.car || null;

    this.user = data.user || [];

    this.status = data.status;

    this.driver = null;
    if (data.carpoolingUsers && Array.isArray(data.carpoolingUsers)) {
      const driverData = data.carpoolingUsers.find(cu => cu.isDriver);
      if (driverData && driverData.user) {
        this.driver = new User(driverData.user);
      }
    }
  }

  formatTime(isoString) {
    const date = new Date(isoString);
    return date.toLocaleTimeString('fr-FR', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: false
    });
  }

  formatDateToFrench(dateInput) {
    const date = new Date(dateInput);
    const formattedDate = date.toLocaleDateString('fr-FR', {
      weekday: 'long',
      day: 'numeric',
      month: 'long'
    });

    const [weekday, day, month] = formattedDate.split(' ');
    const capitalizedWeekday = weekday.charAt(0).toUpperCase() + weekday.slice(1);

    return [capitalizedWeekday, day, month].join(' ');
  }


  toCardElement() {
    const wrapper = document.createElement('div');
    wrapper.className = 'mb-4 w-100 px-2';

    const card = document.createElement('div');
    card.className = 'carpool-card card shadow w-100';

    wrapper.dataset.id = this.id;
    wrapper.addEventListener('click', () => {
      window.location.href = `detail-carpooling?id=${this.id}`;
    });

    card.innerHTML = `
    <div class="card-body d-flex justify-content-between">
      <div class="d-flex">
        <div class="mt-1">
          <img src="assets/images/Arrow 6.png" alt="Trajet" class="route-image me-2">
        </div>
        <div>
          <p class="fw-bold">${this.departurePlace} - ${this.formatTime(this.departureTime)}</p>
          <p class="fw-bold mt-4">${this.arrivalPlace} - ${this.formatTime(this.arrivalTime)}</p>
        </div>
    </div>
    <div class="d-flex justify-content-end align-items-center">
        <div class="price">${this.pricePerPerson.toFixed(2)}</div>
        <div class="currency-icon"><i class="bi bi-coin"></i></div>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center px-4">
      <p class="text-muted mb-0">${this.seatCount} place${this.seatCount > 1 ? 's' : ''} restante${this.seatCount > 1 ? 's' : ''}</p>
      ${this.isEco ? `<div class="eco-icon">🍃</div>` : ''}
    </div>


    <div class="driver-section">
      <img class="driver-img-detail" src="${this.driver.photo || 'assets/images/profil.jpg'}" alt="${this.driver.userName || 'Conducteur'}">
      <div>
        <p class="mb-0 fs-5">${this.driver.userName}</p>
        <div>☆ 5</div>
      </div>
    </div>
  `;

    wrapper.appendChild(card);
    return wrapper;
  }

  toDetailCarpooling() {
    const wrapper = document.createElement('div');
    wrapper.className = 'mb-4 w-100 px-2 ms-0 w-md-70';

    const card = document.createElement('div');
    card.className = 'detail-card card shadow w-100';

    // Formatage des dates et heures
    const departureDateFormatted = this.formatDateToFrench(this.departureDate);
    const departureTimeFormatted = this.formatTime(this.departureTime);
    const arrivalTimeFormatted = this.formatTime(this.arrivalTime);


    card.innerHTML = `
    <div class="card-body pb-0"> 
      <div class="mb-3 text-start">
          <h1 class="mb-0 ms-2 date">${departureDateFormatted}</h1>
      </div>

      <div class="d-flex justify-content-between">
          <div class="d-flex">
              <div class="mt-1">
                  <img src="assets/images/Arrow 6.png" alt="Trajet" class="detail-route-image me-2">
              </div>
              <div>
                  <p class="fw-bold">${this.departurePlace} - ${departureTimeFormatted}</p>
                  <p class="fw-bold mt-4">${this.arrivalPlace} - ${arrivalTimeFormatted}</p>
              </div>
          </div>
          <div class="d-flex justify-content-end align-items-center">
              <div class="detail-price">${this.pricePerPerson.toFixed(2)}</div>
              <div class="detail-currency-icon"><i class="bi bi-coin"></i></div>
          </div>
      </div>

      <div class="d-flex align-items-center mb-3">
          ${this.isEco ? `
              <div class="detail-eco-icon">🍃</div>
              <span class="ms-3 detail-text">En choisissant ce trajet, vous contribuez à une planète plus verte</span>
          ` : `
              <span class="ms-3 detail-text">Ce trajet ne profite pas d'une empreinte carbone réduite. <br>Prenez le temps de les comparer :)</br></span>
          `}
      </div>
    </div>
    `;

    wrapper.appendChild(card);
    return wrapper;
  }

  toDriverCardElement() {
    if (!this.driver) {
      return null; // Pas de carte conducteur si pas de conducteur
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'mb-4 w-100 px-2 ms-0 w-md-70';

    const driverCard = document.createElement('div');
    driverCard.className = 'detail-card card shadow w-100';

    const carBrandLabel = this.car && this.car.brand ? this.car.brand.label : "Non renseignée";
    const carModel = this.car ? this.car.model : "";
    const carEnergy = this.car ? this.car.energy : "";

    driverCard.innerHTML = `
      <div class="card-body pb-0">
        <h2 class="mb-3 text-start user-card-title">Trajet proposé par</h2>
        <div class="d-flex align-items-center mb-3 driver-info-row">
            <img class="driver-img-detail" src="${this.driver.photo || 'assets/images/profil.jpg'}" alt="${this.driver.userName || 'Conducteur'}">
            <div class="driver-name-rating">
                <p class="mb-0 fs-5">${this.driver.userName}</p>
                <p class="mb-0 driver-rating"><span class="fs-5">☆ 5</span></p>
            </div>
        </div>
        <div class="driver-car-details mb-4">
            <div class="d-flex align-items-center mb-2 driver-car-model">
                <i class="bi bi-car-front-fill ms-2 me-2"></i>
                <p class="mb-0 car-label">${carBrandLabel} ${carModel}</p>
            </div>
            <p class="ms-2 car-label driver-car-energy">Véhicule ${carEnergy}</p>
        </div>
      </div> 
    `;

    wrapper.appendChild(driverCard);
    return wrapper;
  }
}