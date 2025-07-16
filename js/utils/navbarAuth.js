// assets/js/navbarAuth.js

// Fonction pour gérer la déconnexion
export const handleLogout = () => {
    localStorage.removeItem('userToken');
    localStorage.removeItem('userId');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userRoles');
    localStorage.removeItem('currentUserId');
    localStorage.removeItem('apiToken');
    window.location.href = '/login';
};

// Fonction pour mettre à jour l'affichage des liens de la navbar
export const updateNavbarAuthLinks = () => {
    console.log('Mise à jour des liens de la navbar...');
    const userToken = localStorage.getItem('userToken');

    const navLoginLink = document.getElementById('navLoginLink');
    const navMyAccountLink = document.getElementById('navMyAccountLink');
    const navLogoutLink = document.getElementById('navLogoutLink');
    const logoutBtn = document.getElementById('logoutLink'); // Assure-toi que c'est bien l'ID de ton bouton

    if (userToken) {
        if (navLoginLink) navLoginLink.style.display = 'none';
        if (navMyAccountLink) navMyAccountLink.style.display = 'block';
        if (navLogoutLink) navLogoutLink.style.display = 'block';

        if (logoutBtn && !logoutBtn.dataset.listenerAttached) {
            logoutBtn.addEventListener('click', (event) => {
                event.preventDefault();
                handleLogout();
            });
            logoutBtn.dataset.listenerAttached = 'true';
        }

    } else {
        if (navLoginLink) navLoginLink.style.display = 'block';
        if (navMyAccountLink) navMyAccountLink.style.display = 'none';
        if (navLogoutLink) navLogoutLink.style.display = 'none';
    }
};