// js/utils/sanitizer.js

/**
 * Nettoie une chaîne de caractères pour réduire les risques d'injection XSS basique.
 * Remplace certains caractères spéciaux par leurs entités HTML.
 * @param {string} input La chaîne de caractères à nettoyer.
 * @returns {string} La chaîne de caractères nettoyée.
 */
export function sanitizeInput(input) {
    if (typeof input !== 'string') {
        return '';
    }

    let cleanedInput = input.trim();

    cleanedInput = cleanedInput.replace(/&/g, '&amp;');
    cleanedInput = cleanedInput.replace(/</g, '&lt;');
    cleanedInput = cleanedInput.replace(/>/g, '&gt;');
    cleanedInput = cleanedInput.replace(/"/g, '&quot;');
    cleanedInput = cleanedInput.replace(/'/g, '&#039;');

    return cleanedInput;
}