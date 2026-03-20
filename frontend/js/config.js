// Configuration de l'URL de base de l'API
export const API_BASE_URL = window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1"
    ? "http://localhost:8000"
    : "https://www.ecoride-pro.uk";