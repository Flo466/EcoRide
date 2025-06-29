import { User } from './User.js';
import { createCarpoolCardElement } from '../templates/carpoolingCardContent.js';
import { createCarpoolDetailCardElement } from '../templates/carpoolingDetailCardContent.js';
import { createCarpoolDriverCardElement } from '../templates/carpoolingDriverCardContent.js';

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

  static formatTime(isoString) {
    const date = new Date(isoString);
    return date.toLocaleTimeString('fr-FR', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: false
    });
  }

  static formatDateToFrench(dateInput) {
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
    return createCarpoolCardElement(this, Carpooling.formatTime);
  }

  toDetailCarpooling() {
    return createCarpoolDetailCardElement(this, Carpooling.formatDateToFrench, Carpooling.formatTime);
  }

  toDriverCardElement() {
    return createCarpoolDriverCardElement(this);
  }
}