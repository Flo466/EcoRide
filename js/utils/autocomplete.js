// public/js/utils/autocomplete.js

/**
 * Sets up autocomplete functionality for a given input element.
 * Displays suggestions from a provided data array as the user types.
 * @param {HTMLInputElement} input - The input field to attach autocomplete to.
 * @param {Array<Object>} data - The array of data (e.g., cities) to suggest from.
 * Each item in the array should have a 'nom' (name) property.
 */
export function setupAutocomplete(input, data) {
    // Add an event listener for 'input' event to detect user typing.
    input.addEventListener("input", function () {
        const value = this.value.toLowerCase();
        closeSuggestions(); // Close any existing suggestions when input changes

        // If the input value is empty, do not show suggestions.
        if (!value) return;

        // Filter the data to find suggestions that start with the current input value.
        const suggestions = data.filter(item =>
            item.nom.toLowerCase().startsWith(value)
        );

        // Create a container for the autocomplete suggestions.
        const container = document.createElement("div");
        container.setAttribute("class", "autocomplete-items position-absolute w-100 bg-white border");
        container.style.zIndex = 999; // Ensure suggestions appear above other elements
        container.style.maxHeight = '200px'; // Limit height for scrollability
        container.style.overflowY = 'auto'; // Add scrollbar if content exceeds max height

        // For each suggestion, create a div element and append it to the container.
        suggestions.forEach(itemData => {
            const item = document.createElement("div");
            item.className = "p-2 cursor-pointer"; // Basic styling for suggestion items
            // Display the item's name and postal code
            item.innerHTML = `${itemData.nom} (${itemData.code_postal})`;
            // Add click listener to fill the input field when a suggestion is clicked.
            item.addEventListener("click", () => {
                input.value = `${itemData.nom}`; // Set input value to the selected item's name
                closeSuggestions(); // Close suggestions after selection
            });
            container.appendChild(item);
        });

        // Append the suggestions container to the parent of the input field.
        input.parentNode.appendChild(container);
    });

    // Close suggestions when a click occurs anywhere outside the input field.
    document.addEventListener("click", closeSuggestions);

    /**
     * Closes all active autocomplete suggestion lists.
     * This function is nested to have access to the global scope of `setupAutocomplete`.
     */
    function closeSuggestions() {
        const items = document.querySelectorAll(".autocomplete-items");
        items.forEach(item => item.remove()); // Remove each suggestion container from the DOM
    }
}