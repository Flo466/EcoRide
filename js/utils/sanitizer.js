// public/js/utils/sanitizer.js

/**
 * Cleans a string to reduce the risk of basic XSS injection.
 * Replaces certain special characters with their HTML entities.
 * @param {string} input The string to sanitize.
 * @returns {string} The sanitized string.
 */
export function sanitizeInput(input) {
    // Ensure the input is a string. If not, return an empty string.
    if (typeof input !== 'string') {
        return '';
    }

    // Trim whitespace from the beginning and end of the string.
    let cleanedInput = input.trim();

    // Replace special HTML characters with their corresponding HTML entities
    // to prevent them from being interpreted as code.
    cleanedInput = cleanedInput.replace(/&/g, '&amp;');   // Replace '&' with '&amp;'
    cleanedInput = cleanedInput.replace(/</g, '&lt;');    // Replace '<' with '&lt;'
    cleanedInput = cleanedInput.replace(/>/g, '&gt;');    // Replace '>' with '&gt;'
    cleanedInput = cleanedInput.replace(/"/g, '&quot;');  // Replace '"' with '&quot;'
    cleanedInput = cleanedInput.replace(/'/g, '&#039;');  // Replace ''' with '&#039;'

    return cleanedInput;
}