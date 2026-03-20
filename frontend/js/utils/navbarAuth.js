
// =============================================================================
// I. Constants and Messages
// =============================================================================

// User-facing messages (kept in French as requested)
const MESSAGES = {
    CONFIRM_LOGOUT: 'Êtes-vous sûr(e) de vouloir vous déconnecter ?',
    LOGOUT_CANCELLED: 'Déconnexion annulée.'
};

// =============================================================================
// II. Authentication Handling Functions
// =============================================================================

/**
 * Handles the user logout process.
 * Prompts for confirmation and clears local storage items related to user session.
 */
export const handleLogout = () => {
    if (confirm(MESSAGES.CONFIRM_LOGOUT)) {
        // Clear all relevant user data from local storage
        localStorage.removeItem('userToken');
        localStorage.removeItem('userId');
        localStorage.removeItem('userEmail');
        localStorage.removeItem('userRoles');
        localStorage.removeItem('currentUserId'); // Assuming this is also a user-related token/ID
        localStorage.removeItem('apiToken'); // Assuming this is also a user-related token/ID

        // Add a success flag for the home page (if needed, as per previous discussion)
        localStorage.setItem('logoutSuccess', 'true');

        // Redirect to the login page
        window.location.href = '/login';
    } else {
        console.log(MESSAGES.LOGOUT_CANCELLED);
    }
};

/**
 * Updates the visibility of navigation links based on user authentication status.
 * Shows/hides login, my account, and logout links accordingly.
 */
export const updateNavbarAuthLinks = () => {
    console.log('Updating navbar authentication links...');
    const userToken = localStorage.getItem('userToken');

    // Get references to the navigation links
    const navLoginLink = document.getElementById('navLoginLink');
    const navMyAccountLink = document.getElementById('navMyAccountLink');
    const navLogoutLink = document.getElementById('navLogoutLink');
    const logoutBtn = document.getElementById('logoutLink'); // This seems to be the actual clickable logout button

    if (userToken) {
        // If a user token exists, the user is considered logged in
        if (navLoginLink) navLoginLink.style.display = 'none'; // Hide login link
        if (navMyAccountLink) navMyAccountLink.style.display = 'block'; // Show "My Account" link
        if (navLogoutLink) navLogoutLink.style.display = 'block'; // Show logout link

        // Attach event listener to the logout button only once
        if (logoutBtn && !logoutBtn.dataset.listenerAttached) {
            logoutBtn.addEventListener('click', (event) => {
                event.preventDefault(); // Prevent default link behavior
                handleLogout(); // Call the logout handler
            });
            logoutBtn.dataset.listenerAttached = 'true'; // Mark listener as attached
        }

    } else {
        // If no user token, the user is not logged in
        if (navLoginLink) navLoginLink.style.display = 'block'; // Show login link
        if (navMyAccountLink) navMyAccountLink.style.display = 'none'; // Hide "My Account" link
        if (navLogoutLink) navLogoutLink.style.display = 'none'; // Hide logout link
    }
};