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

    this.car = data.car || null; // Car associated with the carpooling
    this.status = data.status;

    this.driver = null; // Will be an instance of User
    this.passengers = []; // Will be an array of User instances
    this.availableSeats = this.seatCount; // Initial available seats, before calculating actual passengers
    this.isCurrentUserDriver = false; // Flag to check if current user is the driver

    // Process carpoolingUsers array to identify driver and passengers
    if (data.carpoolingUsers && Array.isArray(data.carpoolingUsers)) {
      // Find the driver from carpoolingUsers
      const driverData = data.carpoolingUsers.find(cu => cu.isDriver);
      if (driverData && driverData.user) {
        this.driver = new User(driverData.user);
        // Check if the current user is the driver
        if (currentUserId !== null && this.driver.id === currentUserId) {
          this.isCurrentUserDriver = true;
        }
      }

      // Filter and map passengers (exclude driver and cancelled users)
      this.passengers = data.carpoolingUsers
        .filter(cu => !cu.isDriver && !cu.isCancelled)
        .map(cu => new User(cu.user));

      // Calculate available seats based on actual passengers
      this.availableSeats = this.seatCount - this.passengers.length;

    } else {
      // If no carpoolingUsers or invalid, assume no passengers initially
      this.passengers = [];
      this.availableSeats = this.seatCount; // All seats are available
    }
  }

  /**
   * Generates an HTML card element for a carpooling listing.
   * @returns {HTMLElement} - The HTML element for the carpooling card.
   */
  toCardElement() {
    return createCarpoolCardElement(this, formatTime);
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