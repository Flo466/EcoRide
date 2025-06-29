import { User } from './User.js';
import { createCarpoolCardElement } from '../templates/carpoolingCardContent.js';
import { createCarpoolDetailCardElement } from '../templates/carpoolingDetailCardContent.js';
import { createCarpoolDriverCardElement } from '../templates/carpoolingDriverCardContent.js';
import { formatTime, formatDateToFrench } from '../utils/formatters.js';

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

    this.driver = null;
    if (data.carpoolingUsers && Array.isArray(data.carpoolingUsers)) {
      const driverData = data.carpoolingUsers.find(cu => cu.isDriver);
      if (driverData && driverData.user) {
        this.driver = new User(driverData.user);
      }
    }
  }

  toCardElement() {
    return createCarpoolCardElement(this, formatTime);
  }

  toDetailCarpooling() {
    return createCarpoolDetailCardElement(this, formatDateToFrench, formatTime);
  }

  toDriverCardElement() {
    return createCarpoolDriverCardElement(this);
  }
}