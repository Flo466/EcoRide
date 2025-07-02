import Route from "./Route.js";

export const allRoutes = [
    new Route("/", "Accueil", "pages/home.html", "js/home.js"),
    new Route("/carpooling", "Covoiturages", "pages/carpooling.html", "js/carpooling.js"),
    new Route("/detail-carpooling", "Détail covoiturage", "pages/detail-carpooling.html", "js/detail-carpooling.js"),
    new Route("/connection", "Compte", "pages/account/connection.html", "js/account/connection.js"),
    new Route("/create-account", "Créer compte", "pages/account/create-account.html", "js/account/create-account.js")
];

export const websiteName = "EcoRide";