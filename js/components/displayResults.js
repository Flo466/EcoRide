import { Carpooling } from '../models/Carpooling.js';

/**
 * Display carpooling search results as cards.
 * @param {Array} results - Array of carpooling objects (JSON).
 * @param {string} targetElementId - ID of the container element to render results in.
 */
export function displaySearchResults(results, targetElementId = 'carpooling-results') {
  const container = document.getElementById(targetElementId);

  if (!container) {
    console.error(`Container with ID "${targetElementId}" not found.`);
    return;
  }

  container.innerHTML = ''; // Clear previous results

  if (results.length === 0) {
    container.innerHTML = `<p class="text-center mt-3">Aucun trajet trouvé.</p>`;
    return;
  }

  

  results.forEach(data => {
    const carpooling = new Carpooling(data);
    const card = carpooling.toCardElement();
    container.appendChild(card);
  });
}
