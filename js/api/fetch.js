export async function fetchApi(url, method = 'GET', data = null, headers = {}) {
    const config = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            ...headers
        }
    };

    if (data && method !== 'GET') {
        config.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, config);

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Erreur API');
        }

        return await response.json();
    } catch (error) {
        console.error('Erreur dans fetchApi:', error.message);
        throw error;
    }
}
