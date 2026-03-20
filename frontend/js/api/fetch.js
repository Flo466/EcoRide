/**
 * Importation de la configuration centrale.
 * Assure-toi que le chemin '../config.js?v=1' est correct par rapport à l'emplacement de ce fichier.
 */
import { API_BASE_URL } from '../config.js';

/**
 * Fonction asynchrone générique pour effectuer des appels API (Fetch).
 * * @param {string} endpoint - Le point de terminaison (ex: '/api/check-userName').
 * @param {string} method - Méthode HTTP (GET, POST, PUT, DELETE).
 * @param {object|null} data - Données à envoyer dans le corps de la requête.
 * @param {object} headers - En-têtes supplémentaires.
 * @returns {Promise<object>} - Promesse résolue avec les données JSON de la réponse.
 */
export async function fetchApi(endpoint, method = 'GET', data = null, headers = {}) {

    // Construction de l'URL complète en utilisant la base définie dans config.js
    const fullUrl = endpoint.startsWith('http') ? endpoint : `${API_BASE_URL}${endpoint}`;

    const config = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...headers
        }
    };

    // Ajout du corps de la requête si des données sont fournies (sauf pour GET)
    if (data && method !== 'GET') {
        config.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(fullUrl, config);

        // Gestion du cas "204 No Content" (succès sans contenu)
        if (response.status === 204) {
            return {};
        }

        const contentType = response.headers.get('content-type');

        // Vérification si la réponse est bien du JSON
        if (contentType && contentType.includes('application/json')) {
            const responseData = await response.json();

            // Gestion des erreurs serveurs (4xx, 5xx) avec détails
            if (!response.ok) {
                const error = new Error(responseData.message || response.statusText || 'Erreur API inconnue');
                error.statusCode = response.status;
                error.data = responseData;
                throw error;
            }
            return responseData;
        } else {
            // Gestion des réponses non-JSON (erreurs critiques ou pages HTML d'erreur)
            if (!response.ok) {
                const text = await response.text().catch(() => response.statusText);
                const error = new Error(text || response.statusText || 'Erreur non-JSON inattendue');
                error.statusCode = response.status;
                throw error;
            }
            return {}; // Succès mais pas de JSON à renvoyer
        }

    } catch (error) {
        // Log de l'erreur dans la console pour le débogage navigateur
        console.error('Erreur dans fetchApi:', error.message);
        throw error;
    }
}