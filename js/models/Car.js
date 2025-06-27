class Car {
  constructor(data = {}) {
    this.id = data.id ?? null;
    this.model = data.model ?? '';
    this.color = data.color ?? null;
    this.licencePlate = data.licencePlate ?? '';
    this.energy = data.energy ?? '';
    this.firstRegistrationDate = data.firstRegistrationDate ?? null;

    this.brand = data.brand ?? null; 
    this.user = data.user ?? null;

    this.carpoolings = data.carpoolings ?? [];

    this.createdAt = data.createdAt ? new Date(data.createdAt) : new Date();
    this.updatedAt = data.updatedAt ? new Date(data.updatedAt) : null;
  }

  getFullName() {
    const brandName = typeof this.brand === 'object' ? this.brand.name : this.brand;
    return `${brandName ?? ''} ${this.model}`.trim();
  }

  getFormattedRegistrationDate() {
    return this.firstRegistrationDate ?? 'Date inconnue';
  }
}

export default Car;
