import Route from "./Route.js";

export const allRoutes = [
    new Route("/", "Accueil", "pages/home.html", "js/home.js"),
    new Route("/covoiturages", "Covoiturages", "pages/carpooling.html", "js/carpooling.js"),
    new Route("/detail-carpooling", "Détail covoiturage", "pages/detail-carpooling.html", "js/detail-carpooling.js")
];

export const websiteName = "EcoRide";