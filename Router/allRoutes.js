import Route from "./Route.js";

export const allRoutes = [
    new Route("/", "Home", "pages/home.html", "js/home.js"),
    new Route("/carpooling", "Carpoolings", "pages/carpooling.html", "js/carpooling.js"),
    new Route("/detail-carpooling", "Carpooling detail", "pages/detail-carpooling.html", "js/detail-carpooling.js"),
    new Route("/login", "Login", "pages/account/login.html", "js/account/login.js"),
    new Route("/signup", "SignUp", "pages/account/signup.html", "js/account/signup.js"),
    new Route("/my-account", "My acocunt", "pages/account/my-account.html", "js/account/my-account.js"),
    new Route("/car-form", "Car", "pages/car-form.html", "js/car-form.js"),

];

export const websiteName = "EcoRide";