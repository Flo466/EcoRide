(function () {
    'use strict';
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);

    let currentPath = window.location.pathname;
    if (currentPath.endsWith('/') && currentPath.length > 1) {
        currentPath = currentPath.slice(0, -1);
    }

    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    navLinks.forEach(link => {
        let linkPath = link.getAttribute('href');
        if (linkPath === '/') {
            if (currentPath === '/' || currentPath === '') {
                link.classList.add('active');
                link.setAttribute('aria-current', 'page');
            }
        } else if (currentPath.includes(linkPath) && linkPath !== '/') {
            link.classList.add('active');
            link.setAttribute('aria-current', 'page');
        }
    });
})();