import { Controller } from "stimulus";

import { initCalendar } from "../js/calendar/calendar.js"; 


export default class extends Controller {
    static targets = ["calendar"];

    connect() {
        console.log("Stimulus OK !");
        console.log("Target =", this.calendarTarget);

        if (!this.hasCalendarTarget) {
            console.error("Aucun target calendar trouvé !");
            return;
        }

        initCalendar(this.calendarTarget);
    }
    
}

