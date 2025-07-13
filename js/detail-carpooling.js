// public/js/detail-carpooling.js

import { fetchApi } from './api/fetch.js';
import { API_BASE_URL } from './config.js';
import { Carpooling } from './models/Carpooling.js';
import { Review } from './models/Review.js';

(async () => {
    const carpoolingContainer = document.getElementById('detail-carpooling-container');
    const userContainer = document.getElementById('detail-user-container');
    const reviewContainer = document.getElementById('detail-review-container');

    const backButton = document.getElementById('back-button');

    if (backButton) {
        backButton.addEventListener('click', (e) => {
            e.preventDefault();
            if (document.referrer && document.referrer.includes(window.location.host)) {
                window.history.back();
            } else {
                window.location.href = '/covoiturages';
            }
        });
    }

    if (!carpoolingContainer || !userContainer || !reviewContainer) {
        console.error('Un ou plusieurs conteneurs (carpooling, user, review) non trouvés!');
        return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');

    if (!id) {
        carpoolingContainer.innerHTML = '<p class="text-danger">Aucun covoiturage sélectionné.</p>';
        return;
    }

    const apiUrl = `${API_BASE_URL}/api/carpoolings/${id}`;

    try {
        const result = await fetchApi(apiUrl);
        console.log('Results JSON carpooling', result);

        const carpooling = new Carpooling(result);

        carpoolingContainer.innerHTML = '';
        userContainer.innerHTML = '';
        reviewContainer.innerHTML = '';

        carpoolingContainer.appendChild(carpooling.toDetailCarpooling());

        if (carpooling.driver) {
            userContainer.appendChild(carpooling.toDriverCardElement());
            reviewContainer.innerHTML = `<h4 class="ms-3 mb-3">Avis</h4><div id="driver-reviews-list"></div>`;

            const driverReviewsList = document.getElementById('driver-reviews-list');
            try {
                const reviewsApiUrl = `${API_BASE_URL}/api/review/user/${carpooling.driver.id}/target`;
                const reviewsResult = await fetchApi(reviewsApiUrl);
                console.log('Reviews JSON', reviewsResult);

                if (reviewsResult && reviewsResult.length > 0) {
                    let hasApprovedReviews = false;
                    reviewsResult.forEach(reviewData => {
                        if (reviewData.status.toLowerCase() === 'approved') {
                            const review = new Review(reviewData);
                            driverReviewsList.appendChild(review.toReviewCardElement());
                            hasApprovedReviews = true;
                        }
                    });

                    if (!hasApprovedReviews) {
                        const noApprovedReviewsInfo = document.createElement('div');
                        noApprovedReviewsInfo.className = 'p-4';
                        noApprovedReviewsInfo.innerHTML = '<p class="text-info mb-0">Aucun avis approuvé disponible pour ce conducteur pour le moment.</p>';
                        driverReviewsList.appendChild(noApprovedReviewsInfo);
                    }
                } else {
                    const noReviewsInfo = document.createElement('div');
                    noReviewsInfo.className = 'p-4';
                    noReviewsInfo.innerHTML = '<p class="text-info mb-0">Aucun avis disponible pour ce conducteur pour le moment.</p>';
                    driverReviewsList.appendChild(noReviewsInfo);
                }
            } catch (reviewsError) {
                console.error('Erreur lors du chargement des reviews du conducteur:', reviewsError);
                const reviewsErrorInfo = document.createElement('div');
                reviewsErrorInfo.className = 'p-4';
                reviewsErrorInfo.innerHTML = '<p class="text-danger mb-0">Erreur lors du chargement des avis.</p>';
                driverReviewsList.appendChild(reviewsErrorInfo);
            }

        } else {
            userContainer.innerHTML = '<div class="p-4"><p class="text-danger mb-0">Aucune information sur le conducteur disponible.</p></div>';
            reviewContainer.innerHTML = '<div class="p-4"><p class="text-info mb-0">Aucun avis à afficher car pas de conducteur.</p></div>';
        }

        const currentUrlParams = new URLSearchParams(window.location.search);
        currentUrlParams.set('page', 'detail');
        window.history.replaceState(null, '', `?${currentUrlParams.toString()}`);

    } catch (error) {
        console.error(error);
        carpoolingContainer.innerHTML = '<p class="text-danger">Erreur lors du chargement du covoiturage.</p>';
    }
})();