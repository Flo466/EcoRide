export class Carpooling {
  constructor(data) {
    this.id = data.id;
    this.departureDate = data.departureDate;
    this.departureTime = data.departureTime;
    this.departurePlace = data.departurePlace;
    this.arrivalDate = data.arrivalDate;
    this.arrivalTime = data.arrivalTime;
    this.arrivalPlace = data.arrivalPlace;
    this.seatCount = data.seatCount;
    this.pricePerPerson = data.pricePerPerson;
    this.isEco = data.isEco;
    this.car = data.car || null;
    this.user = data.user || [];
    this.status = data.status;
  }

  // Extract hours
  formatTime(isoString) {
    const date = new Date(isoString);
    return date.toLocaleTimeString('fr-FR', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: false
    });
  }

  toCardElement() {
    const wrapper = document.createElement('div');
    wrapper.className = 'mb-4 w-100 px-2';

    const card = document.createElement('div');
    card.className = 'carpool-card card shadow w-100';

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
      <img src="assets/images/profil.jpg" alt="Julie" class="driver-img">
      <div>
        <div class="fw-bold">Julie</div>
        <div>☆ 5</div>
      </div>
    </div>
  `;

    wrapper.appendChild(card);
    return wrapper;
  }
}
