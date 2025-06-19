import Route from "./Route.js";

export const allRoutes = [
    new Route("/", "Accueil", "pages/home.html", "js/home.js"),
    new Route("/covoiturages", "Covoiturages", "pages/search.html", "js/search.js")
];

export const websiteName = "EcoRide";