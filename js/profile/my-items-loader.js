// src/js/profile/my-items-loader.js

import { loadUserItems } from './my-items-list.js';

// Détecte la page actuelle via l'URL
const path = window.location.pathname;

if (path.includes('/my-vehicles')) {
    loadUserItems('vehicles');
} else if (path.includes('/my-journeys')) {
    loadUserItems('journeys');
} else {
    console.error("Type de page inconnu pour my-items-loader.js");
}