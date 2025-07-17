// src/js/profile/my-items-loader.js

import { loadUserItems } from './my-items-list.js';

/**
 * Determines the current page type based on the URL path and loads the corresponding user items.
 * This script acts as an entry point for loading either user's vehicles or journeys.
 */
const initializePage = () => {
    const path = window.location.pathname;

    if (path.includes('/my-vehicles')) {
        loadUserItems('vehicles');
    } else if (path.includes('/my-journeys')) {
        loadUserItems('journeys');
    } else {
        console.error("Unknown page type for my-items-loader.js");
    }
};

// Execute the initialization function when the script runs.
initializePage();