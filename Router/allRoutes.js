import Route from "./Route.js";

export const allRoutes = [
    new Route("/", "Accueil", "pages/home.html", "js/home.js"),
    new Route("/carpooling", "Covoiturages", "pages/carpooling.html", "js/carpooling.js"),
    new Route("/detail-carpooling", "Détail covoiturage", "pages/detail-carpooling.html", "js/detail-carpooling.js"),
    new Route("/login", "Compte", "pages/account/login.html", "js/account/login.js"),
    new Route("/signup", "Créer compte", "pages/account/signup.html", "js/account/signup.js"),
    new Route("/my-account", "Mon compte", "pages/account/my-account.html", "js/account/my-account.js")
];

export const websiteName = "EcoRide";