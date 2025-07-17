import Car from './Car.js';
// Assume Review class exists and is imported if reviews are mapped
// import Review from './Review.js'; 

/**
 * Represents a User entity with properties initialized from provided data.
 */
class User {
    /**
     * @param {object} data - The data object to construct a User.
     */
    constructor(data = {}) {
        this.id = data.id ?? null;
        this.email = data.email ?? null;
        this.roles = data.roles ?? ['ROLE_USER'];
        this.password = data.password ?? null;
        this.lastName = data.lastName ?? null;
        this.firstName = data.firstName ?? null;
        this.phone = data.phone ?? null;
        this.address = data.address ?? null;
        this.birthDate = data.birthDate ? new Date(data.birthDate) : null;
        this.photo = data.photo ?? null;
        this.photoBase64 = data.photoBase64 ?? null;
        this.userName = data.userName ?? null;
        this.credits = data.credits ?? 0;
        this.createdAt = data.createdAt ? new Date(data.createdAt) : new Date();
        this.updatedAt = data.updatedAt ? new Date(data.updatedAt) : null;
        this.apiToken = data.apiToken ?? null;

        // Relationships and nested objects
        this.configurations = data.configurations ?? [];
        // Ensure reviews are instances of Review, if Review class is available
        this.reviews = Array.isArray(data.reviews) ? data.reviews.map(reviewData => new Review(reviewData)) : [];
        this.carpooling = data.carpooling ?? []; // Assuming this refers to carpooling instances
        this.cars = data.cars ?? []; // Assuming this refers to car instances

        this.carUsed = data.carUsed ? new Car(data.carUsed) : null; // Instance of Car model
    }

    /**
     * Calculates the average rating from user's reviews.
     * @returns {number} - The average rating, or 0 if no reviews.
     */
    get averageRating() {
        if (!this.reviews || this.reviews.length === 0) {
            return 0;
        }
        const totalRating = this.reviews.reduce((sum, review) => sum + review.rating, 0);
        return totalRating / this.reviews.length;
    }
}

export { User };