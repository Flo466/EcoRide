const FALLBACK_USER_NAME = 'Utilisateur anonyme';

export function createReviewContent(data) {
    const userName = data.user && data.user.firstName && data.user.lastName ? `${data.user.firstName} ${data.user.lastName}` : (data.user && data.user.userName ? data.user.userName : FALLBACK_USER_NAME);
    const comment = data.comment || 'Aucun commentaire fourni.';
    const rating = data.rating || 0;

    const stars = '⭐'.repeat(Math.round(rating));

    return `
        <div class="card shadow-sm mb-3"> <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <h5 class="card-title mb-0 me-2">${userName}</h5>
                    <span class="text-warning">${stars}</span>
                    <span class="text-muted ms-1">(${rating}/5)</span>
                </div>
                <p class="card-text">${comment}</p>
                ${data.createdAt ? `<p class="card-subtitle text-muted text-end"><small>Le: ${new Date(data.createdAt).toLocaleDateString('fr-FR')}</small></p>` : ''}
            </div>
        </div>
    `;
}