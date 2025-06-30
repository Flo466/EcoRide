export function createReviewContent(data) {
    const userName = data.user && data.user.userName
    const comment = data.comment || 'Aucun commentaire fourni.';
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