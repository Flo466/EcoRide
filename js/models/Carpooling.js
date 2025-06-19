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

  toCardElement() {
    const card = document.createElement('div');
    card.className = 'card mb-3 shadow-sm';

    card.innerHTML = `
      <div class="card-body">
        <h5 class="card-title">${this.departurePlace} → ${this.arrivalPlace}</h5>
        <p class="card-text mb-1">
          <strong>Date :</strong> ${this.departureDate} à ${this.departureTime}
        </p>
        <p class="card-text mb-1">
          <strong>Prix :</strong> ${this.pricePerPerson.toFixed(2)} €
        </p>
        <p class="card-text mb-1">
          <strong>Places :</strong> ${this.seatCount}
        </p>
        ${this.isEco ? `<span class="badge bg-success">Trajet éco</span>` : ''}
        <p class="card-text mt-2"><small class="text-muted">Statut : ${this.status}</small></p>
      </div>
    `;

    return card;
  }
}
