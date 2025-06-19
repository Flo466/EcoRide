import { Carpooling } from '../models/Carpooling.js';

export function displaySearchResults(data) {
  const resultsContainer = document.getElementById('carpooling-results');
  
  if (!resultsContainer) {
    console.error('Results container not found');
    return;
  }

  resultsContainer.innerHTML = ''; // clear previous

  if (data && data.length > 0) {
    data.forEach(item => {
      const carpooling = new Carpooling(item);    
      const cardElement = carpooling.toCardElement();
      resultsContainer.appendChild(cardElement);
    });
  } else {
    resultsContainer.innerHTML = '<p class="text-center">Aucun trajet trouvé.</p>';
  }
}
