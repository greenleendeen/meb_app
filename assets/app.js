// FullCalendar
import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";

// Flatpickr (sélecteur de date)
import Flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

// Initialisation une fois le DOM chargé
document.addEventListener("DOMContentLoaded", () => {
    const calendarEl = document.getElementById("calendar");
    if (!calendarEl) return;

    const technicienSelect = document.getElementById("technicienFilter");

    // Ici on utilise bien le Calendar importé
    const calendar = new Calendar(calendarEl, {
        plugins: [ dayGridPlugin, timeGridPlugin, interactionPlugin ],
        initialView: "dayGridMonth",
        selectable: true,
        events: (fetchInfo, successCallback, failureCallback) => {
            let url = `/calendar/events?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`;
            if (technicienSelect?.value) {
                url += `&technicien=${technicienSelect.value}`;
            }
            fetch(url)
                .then(res => res.json())
                .then(events => successCallback(events))
                .catch(err => failureCallback(err));
        },
        dateClick: info => alert("Date sélectionnée : " + info.dateStr)
    });

    calendar.render();

    // Filtrer par technicien en live
    technicienSelect?.addEventListener("change", () => calendar.refetchEvents());
});
