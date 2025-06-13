export function createCarpoolingSearchComponent(targetElementId) {
    const targetElement = document.getElementById(targetElementId);
    if (!targetElement) {
        console.error(`L'élément avec l'ID "${targetElementId}" n'a pas été trouvé.`);
        return;
    }

   const htmlContent = `
  <div class="row justify-content-center">
    <div class="col-9 col-md-7 col-lg-9 col-xl-9">
      <form class="search-form shadow bg-white overflow-hidden">
        <div class="d-flex flex-column flex-lg-row align-items-stretch w-100">
          <input type="text" class="form-control text-center border-switch b-s-t" id="depart" placeholder="Départ">
          <input type="text" class="form-control text-center border-switch" id="destination" placeholder="Destination">
          <input type="date" class="form-control text-center border-switch border-0" id="date">
          <button type="submit" class="btn text-white fw-semibold w-100 w-lg-auto btn-search">
            Rechercher un trajet
          </button>
        </div>
      </form>
    </div>
  </div>
`;





    targetElement.innerHTML = htmlContent;

    const form = targetElement.querySelector('.search-form');
    const departInput = targetElement.querySelector('#depart');
    const destinationInput = targetElement.querySelector('#destination');
    const dateInput = targetElement.querySelector('#date');

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const depart = departInput.value;
        const destination = destinationInput.value;
        const date = dateInput.value;

        console.log('Recherche de covoiturage :', { depart, destination, date });
        alert(`Recherche pour : ${depart} vers ${destination} le ${date}`);
    });
}