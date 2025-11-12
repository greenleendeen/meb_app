
import { Application } from "stimulus";
import FlatpickrController from "./controllers/flatpickr_controller";
import { initCalendar } from "../assets/js/calendar/calendar";
import "bootstrap"; // initialise les modales, tooltips, etc.


// Démarrage de Stimulus
const application = Application.start();
// Enregistrement du contrôleur
application.register("flatpickr", FlatpickrController);

import "../assets/styles/app.scss";

import "../assets/styles/fullcalendar/fullcalendar.scss";

document.addEventListener("DOMContentLoaded", () => {
  initCalendar();
});