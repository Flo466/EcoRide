import { User } from './User.js';
import { createReviewContent } from '../templates/reviewContent.js';

const ReviewStatus = {
    PENDING: 'PENDING',
    APPROVED: 'APPROVED',
    REJECTED: 'REJECTED',
};

export class Review {
    constructor(data) {
        this.id = data.id || null;
        this.comment = data.comment || '';
        this.rating = data.ratting || 0;
        this.status = data.status || ReviewStatus.PENDING;

        this.user = (data.user instanceof User) ? data.user : (data.user ? new User(data.user) : null);

        this.createdAt = data.createdAt ? new Date(data.createdAt) : null;
        this.updatedAt = data.updatedAt ? new Date(data.updatedAt) : null;
    }

    toReviewCardElement() {
        const reviewWrapper = document.createElement('div');

        reviewWrapper.className = 'col-12';

        reviewWrapper.innerHTML = createReviewContent({
            user: this.user,
            comment: this.comment,
            rating: this.rating,
            createdAt: this.createdAt
        });

        return reviewWrapper;
    }
}