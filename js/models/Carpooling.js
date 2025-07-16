// src/models/Carpooling.js

import { User } from './User.js';
import { createCarpoolCardElement } from '../templates/carpoolingCardContent.js';
import { createCarpoolDetailCardElement } from '../templates/carpoolingDetailCardContent.js';
import { createCarpoolDriverCardElement } from '../templates/carpoolingDriverCardContent.js';
import { createJourneyCardElement } from '../templates/myJourneysCardContent.js';
import { formatTime, formatDateToFrench } from '../utils/formatters.js';

export class Carpooling {
  constructor(data, currentUserId = null) {
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
    this.isCurrentUserDriver = false;

    if (data.carpoolingUsers && Array.isArray(data.carpoolingUsers)) {
      const driverData = data.carpoolingUsers.find(cu => cu.isDriver);
      if (driverData && driverData.user) {
        this.driver = new User(driverData.user)

        if (currentUserId !== null && this.driver !== null && this.driver.id === currentUserId) {
          this.isCurrentUserDriver = true;
        }
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

  toJourneyCardElement() {
    return createJourneyCardElement(this, formatDateToFrench, formatTime);
  }
}