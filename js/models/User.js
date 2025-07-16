import Car from './Car.js';

class User {
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

        this.configurations = data.configurations ?? [];
        this.reviews = data.reviews ?? [];
        this.reviews = Array.isArray(data.reviews) ? data.reviews.map(reviewData => new Review(reviewData)) : [];
        this.carpooling = data.carpooling ?? [];
        this.cars = data.cars ?? [];

        this.carUsed = data.carUsed ? new Car(data.carUsed) : null;
    }

    get averageRating() {
        if (!this.reviews || this.reviews.length === 0) {
            return 0;
        }
        const totalRating = this.reviews.reduce((sum, review) => sum + review.rating, 0);
        return totalRating / this.reviews.length;
    }
}
export { User };
