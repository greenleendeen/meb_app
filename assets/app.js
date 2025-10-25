import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import frLocale from "@fullcalendar/core/locales/fr";

// Tu peux importer ton CSS ici
//import "@fullcalendar/core/index.css";
//import "@fullcalendar/daygrid/index.css";
//import "@fullcalendar/timegrid/index.css";
// CSS global / Bootstrap
//import "../styles/app.scss";
import "../assets/styles/app.scss";

import "../assets/styles/fullcalendar/fullcalendar.scss";

document.addEventListener("DOMContentLoaded", () => {
    const calendarEl = document.getElementById("calendar");
    const technicienSelect = document.getElementById("technicienFilter");

    if (!calendarEl) return;

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: "timeGridWeek", // par défaut semaine
        locale: frLocale,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridDay,timeGridWeek,dayGridMonth' // jour, semaine, mois
        },
        slotMinTime: "07:00:00",
        slotMaxTime: "20:00:00",
        allDaySlot: false,
        height: "auto",
        selectable: true,

        events: (fetchInfo, successCallback, failureCallback) => {
            let url = `/calendar/events?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`;
            if (technicienSelect?.value) {
                url += `&technicien=${technicienSelect.value}`;
            }
            fetch(url)
                .then((res) => res.json())
                .then((events) => successCallback(events))
                .catch((err) => failureCallback(err));
        },

        eventClick: (info) => {
            window.location.href = `/intervention/${info.event.id}`;
        },
    });

    calendar.render();

    // Rafraîchissement automatique des événements lors du changement de technicien
    technicienSelect?.addEventListener("change", () => {
        calendar.refetchEvents();
    });
});