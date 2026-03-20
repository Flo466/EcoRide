import { User } from './User.js';
import { createCarpoolCardElement } from '../templates/carpoolingCardContent.js';
import { createCarpoolDetailCardElement } from '../templates/carpoolingDetailCardContent.js';
import { createCarpoolDriverCardElement } from '../templates/carpoolingDriverCardContent.js';
import { createJourneyCardElement } from '../templates/myJourneysCardContent.js';
import { formatTime, formatDateToFrench } from '../utils/formatters.js';

/**
 * Represents a Carpooling entity with properties derived from provided data.
 */
export class Carpooling {
  /**
   * @param {object} data - The data object to construct a Carpooling instance.
   * @param {number|null} currentUserId - The ID of the currently logged-in user, or null if not logged in.
   */
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

    if (data.duration) {
      this.duration = data.duration;
    } else {
      const departureDatePart = data.departureDate.split('T')[0];
      const departureTimePart = data.departureTime.split('T')[1];
      const arrivalDatePart = data.arrivalDate.split('T')[0];
      const arrivalTimePart = data.arrivalTime.split('T')[1];

      const departureDateTimeString = `${departureDatePart}T${departureTimePart}`;
      const arrivalDateTimeString = `${arrivalDatePart}T${arrivalTimePart}`;

      const departureDateTime = new Date(departureDateTimeString);
      const arrivalDateTime = new Date(arrivalDateTimeString);

      if (!isNaN(departureDateTime) && !isNaN(arrivalDateTime)) {
        const diffInMilliseconds = arrivalDateTime - departureDateTime;
        this.duration = diffInMilliseconds / (1000 * 60 * 60);
      } else {
        this.duration = Infinity;
      }
    }

    this.car = data.car || null;
    this.status = data.status;

    this.driver = null;
    this.passengers = [];

    this.isCurrentUserDriver = false;

    if (data.carpoolingUsers && Array.isArray(data.carpoolingUsers)) {
      const driverData = data.carpoolingUsers.find(cu => cu.isDriver);
      if (driverData && driverData.user) {
        this.driver = new User(driverData.user);
        if (currentUserId !== null && this.driver.id === currentUserId) {
          this.isCurrentUserDriver = true;
        }
      }

      this.passengers = data.carpoolingUsers
        .filter(cu => !cu.isDriver && !cu.isCancelled)
        .map(cu => new User(cu.user));

      this.availableSeats = this.seatCount;

    } else {
      this.passengers = [];
      this.availableSeats = this.seatCount;
    }
  }

  /**
   * Returns the status of the carpooling.
   * @returns {string} The status of the carpooling (e.g., 'OPEN', 'CANCELLED', 'COMPLETED').
   */
  getStatus() {
    return this.status;
  }

  /**
   * Returns the departure date of the carpooling.
   * @returns {string} The departure date in 'YYYY-MM-DD' format.
   */
  getDepartureDate() {
    return this.departureDate;
  }

  /**
   * Returns the departure time of the carpooling.
   * @returns {string} The departure time in 'HH:MM:SS' format.
   */
  getDepartureTime() {
    return this.departureTime;
  }

  /**
   * Checks if the carpooling meets a specific filter.
   * @param {object} filters - The filter criteria from the UI.
   * @returns {boolean} - True if the carpooling passes all filters, false otherwise.
   */
  passesFilters(filters) {
    const isElectricMatch = !filters.isElectricCarChecked || this.car?.energy === 'Électrique';
    const isPriceMatch = this.pricePerPerson <= filters.maxPrice;
    const isDurationMatch = (this.duration ?? Infinity) <= filters.maxDuration;
    const driverRating = this.driver?.averageRating || 0;
    const isRatingMatch = driverRating >= filters.minRating;

    return isElectricMatch && isPriceMatch && isDurationMatch && isRatingMatch;
  }

  /**
   * Generates an HTML card element for a carpooling listing.
   * @returns {HTMLElement} - The HTML element for the carpooling card.
   */
  toCardElement() {
    return createCarpoolCardElement(this, formatDateToFrench, formatTime);
  }

  /**
   * Generates an HTML element for carpooling details.
   * @returns {HTMLElement} - The HTML element for the carpooling detail card.
   */
  toDetailCarpooling() {
    return createCarpoolDetailCardElement(this, formatDateToFrench, formatTime);
  }

  /**
   * Generates an HTML card element specifically for the driver's view of a carpooling.
   * @returns {HTMLElement} - The HTML element for the driver's carpooling card.
   */
  toDriverCardElement() {
    return createCarpoolDriverCardElement(this);
  }

  /**
   * Generates an HTML card element for "My Journeys" section.
   * @returns {HTMLElement} - The HTML element for the journey card.
   */
  toJourneyCardElement() {
    return createJourneyCardElement(this, formatDateToFrench, formatTime);
  }
}