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

  toDetailCarpooling() {
    const wrapper = document.createElement('div');
    wrapper.className = 'mb-4 w-100 px-2 ms-0 w-md-60';

    const card = document.createElement('div');
    card.className = 'detail-card card shadow w-100';

    card.innerHTML = `
      <div class="card-body">
        <div class="mb-3 text-start">
          <p class="mb-0 ms-2 date">${this.formatDateToFrench(this.departureDate)}</p>
        </div>
        
        <div class="d-flex justify-content-between">
          <div class="d-flex">
            <div class="mt-1">
              <img src="assets/images/Arrow 6.png" alt="Trajet" class="detail-route-image me-2">
            </div>
            <div>
              <p class="fw-bold">${this.departurePlace} - ${this.formatTime(this.departureTime)}</p>
              <p class="fw-bold mt-4">${this.arrivalPlace} - ${this.formatTime(this.arrivalTime)}</p>
            </div>
          </div>
          <div class="d-flex justify-content-end align-items-center">
            <div class="detail-price">${this.pricePerPerson.toFixed(2)}</div>
            <div class="detail-currency-icon"><i class="bi bi-coin"></i></div>
          </div>
        </div>

        <div class="d-flex align-items-center mb-3">
          ${this.isEco ? `<div class="detail-eco-icon">🍃</div><span class="ms-3 detail-text">En choisissant ce trajet, 
            vous contribuez à une planète plus verte</span>` : 
            "Ce trajet ne profite pas d'une empreinte carbone réduite. Prenez le temps de les comparer :)"}
        </div>
      </div>
    `;


    wrapper.appendChild(card);
      return wrapper;
  } 

}
