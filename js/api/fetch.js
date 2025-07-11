// Your file: ../api/fetch.js
export async function fetchApi(url, method = 'GET', data = null, headers = {}) {
    const config = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json', // Indicates to the server that we prefer JSON responses
            ...headers
        }
    };

    // For non-GET requests, stringify the data to be sent in the request body
    if (data && method !== 'GET') {
        config.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, config);

        // --- SPECIFIC HANDLING FOR 204 NO CONTENT ---
        // If the status is 204 (No Content), there is no response body to parse.
        // Return an empty object to signify success without data.
        if (response.status === 204) {
            return {};
        }

        // Check if the response's content type is JSON before attempting to parse it.
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            const responseData = await response.json(); // Attempt to parse the response as JSON

            // If the response is not OK (e.g., 4xx, 5xx status codes) but contains a JSON body,
            // create and throw a custom error with details from the JSON.
            if (!response.ok) {
                const error = new Error(responseData.message || response.statusText || 'Unknown API error');
                error.statusCode = response.status;
                error.data = responseData; // Attach all error data for debugging/specific handling
                throw error;
            }
            return responseData; // Return the JSON data on successful response
        } else {
            // If the response is not JSON (e.g., plain text, HTML, or empty body for other codes) and not 204.
            if (!response.ok) {
                // For non-JSON error responses, try to read the body as plain text.
                const text = await response.text().catch(() => response.statusText);
                const error = new Error(text || response.statusText || 'Unexpected non-JSON error');
                error.statusCode = response.status;
                throw error;
            }
            // If the response is not JSON but the status is OK (e.g., 200 with an empty non-JSON body),
            // return an empty object.
            return {};
        }

    } catch (error) {
        // Log the error message for debugging purposes.
        console.error('Error in fetchApi:', error.message);
        // Re-throw the error to be caught by the calling function (e.g., deleteVehicle)
        throw error;
    }
}