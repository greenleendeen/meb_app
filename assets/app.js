import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import frLocale from "@fullcalendar/core/locales/fr";

import { Application } from "stimulus";
import FlatpickrController from "./controllers/flatpickr_controller";

// Démarrage de Stimulus
const application = Application.start();
// Enregistrement du contrôleur
application.register("flatpickr", FlatpickrController);

// importer CSS ici
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
    const techButtons = document.querySelectorAll("[data-tech-id]");
    let selectedTech = ""; // identifiant du technicien actif

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
    slotMaxTime: "17:00:00",     // fin de journée à 17h
    weekends: false,             // cache samedi/dimanche
    allDaySlot: false,
    slotDuration: "00:30:00",    // créneaux de 30 min
        height: "auto",
        selectable: true,

        // Chargement dynamique des événements
        events: (fetchInfo, successCallback, failureCallback) => {
            let url = `/calendar/events?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`;
            if (selectedTech || technicienSelect?.value) {
                const techId = selectedTech || technicienSelect.value;
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
         // Couleur dynamique selon technicien (optionnel si ton backend ne renvoie pas déjà une couleur)
        eventDidMount: (info) => {
            if (info.event.extendedProps.color) {
                info.el.style.backgroundColor = info.event.extendedProps.color;
                info.el.style.borderColor = info.event.extendedProps.color;
            }
        },
    });

    calendar.render();

// Changement via le select
    technicienSelect?.addEventListener("change", (e) => {
        selectedTech = e.target.value;
        calendar.refetchEvents();
        resetButtonStates();
    });

    // Changement via les boutons colorés
    techButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            selectedTech = btn.dataset.techId || "";
            resetButtonStates();
            btn.classList.add("active");
            technicienSelect.value = selectedTech; // synchronise avec le select
            calendar.refetchEvents();
        });
    });

    function resetButtonStates() {
        techButtons.forEach((b) => b.classList.remove("active"));
    }
});


    // Rafraîchissement automatique des événements lors du changement de technicien
   // technicienSelect?.addEventListener("change", () => {
   //     calendar.refetchEvents();

        
   // });
//});