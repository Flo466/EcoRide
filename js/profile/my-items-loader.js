import { loadUserItems } from './my-items-list.js';

/**
 * Determines the current page type based on the URL path and loads the corresponding user items.
 * This script acts as an entry point for loading either user's vehicles, journeys, or history.
 */
const initializePage = () => {
    const path = window.location.pathname;

    if (path.includes('/my-vehicles')) {
        loadUserItems('vehicles');
    } else if (path.includes('/my-journeys')) {
        loadUserItems('journeys');
    } else if (path.includes('/my-history')) {
        loadUserItems('history');
    } else {
        console.error("Unknown page type for my-items-loader.js");
    }
};

// Execute the initialization function when the script runs.
initializePage();