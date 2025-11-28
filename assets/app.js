// assets/app.js

// Styles
import "../assets/styles/app.scss";
import "../assets/styles/fullcalendar/fullcalendar.scss";

// Bootstrap (modales, tooltips, etc.)
import "bootstrap";
//import 'bootstrap/dist/css/bootstrap.min.css';
// Stimulus
import { Application } from "stimulus";

const app = Application.start();

// Charge tous les controllers depuis ./controllers
const controllers = import.meta.glob('./controllers/**/*.js', { eager: true });

Object.entries(controllers).forEach(([path, controllerModule]) => {
    const name = path.split('/').pop().replace('_controller.js', '');
    app.register(name, controllerModule.default);
});

// Exemple : si tu as un controller Flatpickr à enregistrer manuellement
import FlatpickrController from './controllers/flatpickr_controller.js';
app.register("flatpickr", FlatpickrController);
// Calendar init
import { initCalendar } from "../assets/js/calendar/calendar";

// Si tu veux que le calendrier s'initialise automatiquement pour un target existant
document.addEventListener("DOMContentLoaded", () => {
    const calendarEl = document.getElementById("calendar");
    if (calendarEl) {
        console.log("InitCalendar lancé !", calendarEl);
        initCalendar(calendarEl);
    }
});
