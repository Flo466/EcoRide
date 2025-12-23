// Your file: ../api/fetch.js
/**
 * Generic asynchronous function to fetch data from an API.
 * @param {string} url - The URL to fetch.
 * @param {string} method - HTTP method (GET, POST, PUT, DELETE, etc.).
 * @param {object|null} data - Data to send in the request body (for non-GET requests).
 * @param {object} headers - Additional request headers.
 * @returns {Promise<object>} - A promise that resolves to the JSON response data or an empty object.
 * @throws {Error} - Throws an error if the network request fails or if the response status is not OK.
 */
export async function fetchApi(url, method = 'GET', data = null, headers = {}) {
    const config = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...headers
        }
    };

    if (data && method !== 'GET') {
        config.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, config);

        if (response.status === 204) {
            return {}; // No Content
        }

        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            const responseData = await response.json();

            if (!response.ok) {
                const error = new Error(responseData.message || response.statusText || 'Unknown API error');
                error.statusCode = response.status;
                error.data = responseData;
                throw error;
            }
            return responseData;
        } else {
            if (!response.ok) {
                const text = await response.text().catch(() => response.statusText);
                const error = new Error(text || response.statusText || 'Unexpected non-JSON error');
                error.statusCode = response.status;
                throw error;
            }
            return {}; // OK but non-JSON or empty body
        }

    } catch (error) {
        console.error('Error in fetchApi:', error.message);
        throw error;
    }
}