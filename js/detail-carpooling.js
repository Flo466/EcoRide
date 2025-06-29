// public/js/detail-carpooling.js

import { fetchApi } from './api/fetch.js';
import { API_BASE_URL } from './config.js';
import { Carpooling } from './models/Carpooling.js';
import { Review } from './models/Review.js';

(async () => {
    const container = document.getElementById('detail-carpooling-container');
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

    if (!container) {
        console.error('Container not found!');
        return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');

    if (!id) {
        container.innerHTML = '<p class="text-danger">Aucun covoiturage sélectionné.</p>';
        return;
    }

    const apiUrl = `${API_BASE_URL}/api/carpooling/${id}`;

    try {
        const result = await fetchApi(apiUrl);
        console.log('Results JSON carpooling', result);

        const carpooling = new Carpooling(result);

        container.innerHTML = '';

        container.appendChild(carpooling.toDetailCarpooling());

        if (carpooling.driver) {
            const driverCard = carpooling.toDriverCardElement();
            container.appendChild(driverCard);

            const reviewsSection = document.createElement('div');
            reviewsSection.className = 'row justify-content-center mt-4';
            reviewsSection.innerHTML = `
                <div class="col-12 col-md-8">
                    <h4 class="mb-3">Avis</h4>
                    <div id="driver-reviews-list"></div>
                </div>
            `;
            container.appendChild(reviewsSection);

            const driverReviewsList = document.getElementById('driver-reviews-list');

            try {
                const reviewsApiUrl = `${API_BASE_URL}/api/review/user/${carpooling.driver.id}`;
                const reviewsResult = await fetchApi(reviewsApiUrl);
                console.log('Reviews JSON', reviewsResult);

                if (reviewsResult && reviewsResult.length > 0) {
                    let hasApprovedReviews = false;
                    reviewsResult.forEach(reviewData => {
                        if (reviewData.status === 'APPROVED') {
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
            const noDriverInfo = document.createElement('div');
            noDriverInfo.className = 'col-12 col-md-8 mx-auto mt-4';
            noDriverInfo.innerHTML = '<div class="p-4"><p class="text-danger mb-0">Aucune information sur le conducteur disponible.</p></div>';
            container.appendChild(noDriverInfo);
        }

        const currentUrlParams = new URLSearchParams(window.location.search);
        currentUrlParams.set('page', 'detail');
        window.history.replaceState(null, '', `?${currentUrlParams.toString()}`);

    } catch (error) {
        console.error(error);
        container.innerHTML = '<p class="text-danger">Erreur lors du chargement du covoiturage.</p>';
    }
})();