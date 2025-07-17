import { User } from './User.js';
import { createReviewContent } from '../templates/reviewContent.js';

/**
 * Defines the possible statuses for a review.
 */
const ReviewStatus = {
    PENDING: 'PENDING',
    APPROVED: 'APPROVED',
    REJECTED: 'REJECTED',
};

/**
 * Represents a Review entity with properties initialized from provided data.
 */
export class Review {
    /**
     * @param {object} data - The data object to construct a Review.
     */
    constructor(data) {
        this.id = data.id || null;
        this.comment = data.comment || '';
        this.rating = data.ratting || 0; // Note: 'ratting' might be a typo for 'rating'
        this.status = data.status || ReviewStatus.PENDING;

        // Ensure user is an instance of User, or create one if raw data is provided.
        this.user = (data.user instanceof User) ? data.user : (data.user ? new User(data.user) : null);

        this.createdAt = data.createdAt ? new Date(data.createdAt) : null;
        this.updatedAt = data.updatedAt ? new Date(data.updatedAt) : null;
    }

    /**
     * Generates an HTML element representing a review card.
     * @returns {HTMLElement} - The div element containing the review content.
     */
    toReviewCardElement() {
        const reviewWrapper = document.createElement('div');
        reviewWrapper.className = 'col-12';

        // Populate the wrapper's inner HTML using a template function.
        reviewWrapper.innerHTML = createReviewContent({
            user: this.user,
            comment: this.comment,
            rating: this.rating,
            createdAt: this.createdAt
        });

        return reviewWrapper;
    }
}