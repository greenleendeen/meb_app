import { Controller } from "stimulus";
import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";

export default class extends Controller {
    static targets = ["calendar"];

    connect() {
        if (!this.hasCalendarTarget) return;

        const calendar = new Calendar(this.calendarTarget, {
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            initialView: "timeGridWeek", // vue semaine avec heures
            selectable: true,
            locale: "fr",
            slotDuration: "00:30:00", // intervalle des créneaux horaires
            select: info => {
                alert(
                    "Sélection : " +
                    info.start.toLocaleString() + " → " +
                    info.end.toLocaleString()
                );
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            }
        });

        calendar.render();
    }
}
