/**
 * Formats an ISO 8601 string to a French time string (HH:MM).
 * @param {string} isoString - The ISO 8601 date string (e.g., "2025-07-17T10:30:00Z").
 * @returns {string} The formatted time string (e.g., "10:30") or an empty string if input is invalid.
 */
export function formatTime(isoString) {
    // Return empty string if the input is null or undefined.
    if (!isoString) {
        return '';
    }

    // Create a Date object from the ISO string.
    const date = new Date(isoString);

    // Check if the Date object is valid (i.e., not "Invalid Date").
    if (isNaN(date.getTime())) {
        return '';
    }

    // Format the time using 'fr-FR' locale for 2-digit hour and minute, in 24-hour format.
    return date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false // Ensure 24-hour format
    });
}

/**
 * Formats a date input (string or Date object) to a French string (e.g., "Jeudi 17 juillet").
 * Capitalizes the first letter of the weekday.
 * @param {string|Date} dateInput - The date string or Date object to format.
 * @returns {string} The formatted date string or an empty string if input is invalid.
 */
export function formatDateToFrench(dateInput) {
    // Return empty string if the input is null or undefined.
    if (!dateInput) {
        return '';
    }

    // Create a Date object from the input.
    const date = new Date(dateInput);

    // Check if the Date object is valid.
    if (isNaN(date.getTime())) {
        return '';
    }

    // Format the date to a long French date string, including weekday, day, and month.
    const formattedDate = date.toLocaleDateString('fr-FR', {
        weekday: 'long', // e.g., "jeudi"
        day: 'numeric',  // e.g., "17"
        month: 'long'    // e.g., "juillet"
    });

    // Split the formatted string to capitalize the weekday.
    const [weekday, day, month] = formattedDate.split(' ');
    const capitalizedWeekday = weekday.charAt(0).toUpperCase() + weekday.slice(1);

    // Recombine the parts with the capitalized weekday.
    return [capitalizedWeekday, day, month].join(' ');
}