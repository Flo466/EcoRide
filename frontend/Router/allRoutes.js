import Route from "./Route.js";

export const allRoutes = [
    new Route("/", "Home", "pages/home.html", "js/home.js"),
    new Route("/carpooling", "Carpoolings", "pages/carpooling.html", "js/carpooling.js"),
    new Route("/detail-carpooling", "Carpooling detail", "pages/detail-carpooling.html", "js/detail-carpooling.js"),
    new Route("/login", "Login", "pages/profile/login.html", "js/profile/login.js"),
    new Route("/signup", "SignUp", "pages/profile/signup.html", "js/profile/signup.js"),
    new Route("/profile", "My profile", "pages/profile/profile.html", "js/profile/profile.js"),
    new Route("/car-form", "Car", "pages/profile/car-form.html", "js/profile/car-form.js"),
    new Route("/my-vehicles", "My vehicles", "pages/profile/my-vehicles.html", "js/profile/my-items-loader.js"),
    new Route("/journey-form", "Journey", "pages/profile/journey-form.html", "js/profile/journey-form.js"),
    new Route("/my-journeys", "My journeys", "pages/profile/my-journeys.html", "js/profile/my-items-loader.js"),
    new Route("/my-history", "My history", "pages/profile/my-history.html", "js/profile/my-items-loader.js"),
];

export const websiteName = "EcoRide";