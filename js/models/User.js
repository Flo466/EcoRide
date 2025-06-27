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
        this.userName = data.userName ?? null;
        this.credits = data.credits ?? 0;
        this.createdAt = data.createdAt ? new Date(data.createdAt) : new Date();
        this.updatedAt = data.updatedAt ? new Date(data.updatedAt) : null;
        this.apiToken = data.apiToken ?? null;

        this.configurations = data.configurations ?? [];
        this.reviews = data.reviews ?? [];
        this.carpooling = data.carpooling ?? [];
        this.cars = data.cars ?? [];

        this.carUsed = data.carUsed ? new Car(data.carUsed) : null;
    }

    tuUserCardDetail() {
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-4 w-100 px-2 ms-0 w-md-60';

        const card = document.createElement('div');
        card.className = 'detail-card card shadow w-100';

        const carInfo = this.carUsed
            ? `<div class="mt-3">
                    <i class="bi bi-car-front me-1"></i>
                    <span>${this.carUsed.model} (${this.carUsed.brand?.name ?? 'marque inconnue'})</span>
                </div>`
            : '';

        card.innerHTML = `
            <div class="card-body">
                <div class="mb-3 text-start">
                    <p class="mb-0 ms-2 user-card-title">Trajet proposé par</p>
                </div>
                
                <div class="d-flex align-items-center"> <div class="flex-shrink-0 me-3"> <img src="${this.photo || 'assets/images/profil.jpg'}" alt="${this.userName}" class="driver-img rounded-circle"> </div>
                    <div>
                        <p class="mb-1 fw-bold">${this.userName || this.firstName + ' ' + this.lastName}</p> ${carInfo}
                    </div>
                </div>
            </div>
        `;

        wrapper.appendChild(card);
        return wrapper;
    }
}

export { User };