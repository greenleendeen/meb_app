
import { Application } from "stimulus";
import CalendarController from "./controllers/calendar_controller";
import FlatpickrController from "./controllers/flatpickr_controller";

const application = Application.start();

// Enregistrement manuel des contrôleurs
application.register("calendar", CalendarController);
application.register("flatpickr", FlatpickrController);