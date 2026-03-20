
// =============================================================================
// I. Constants and Messages
// =============================================================================

// User-facing messages
const MESSAGES = {
    NO_COMMENT_PROVIDED: 'Aucun commentaire fourni.'
};

// =============================================================================
// II. Review Display Functions
// =============================================================================

/**
 * Creates the HTML content for a single review.
 * @param {Object} data - The review data object.
 * @param {Object} data.user - The user object associated with the review.
 * @param {string} data.user.userName - The name of the user who wrote the review.
 * @param {string} data.comment - The review comment.
 * @param {number} data.rating - The review rating (out of 5).
 * @param {string} [data.createdAt] - The ISO date string when the review was created.
 * @returns {string} The HTML string representing the review card content.
 */
export function createReviewContent(data) {
    const userName = data.user && data.user.userName;
    const comment = data.comment || MESSAGES.NO_COMMENT_PROVIDED;
    const rating = data.rating || 0;

    return `
        <div class="ms-3 mb-3 review-list w-md-40">
            <div class="">
                <div class="d-flex align-items-center mb-2">
                    <h5 class="mb-0 me-2">${userName}</h5>
                    <span class="text-muted ms-1">(${rating}/5)</span>
                </div>
                <p class="card-text mb-3">${comment}</p>
                ${data.createdAt ? `<p class="card-subtitle mb-1 text-muted text-end"><small>Le:
                    ${new Date(data.createdAt).toLocaleDateString('fr-FR')}</small></p>` : ''}
            </div>
        </div>
    `;
}