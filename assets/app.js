/// JS principal (Stimulus, Bootstrap JS, etc.)
import "./bootstrap.js";

// CSS Bootstrap (via npm)
import "bootstrap/dist/css/bootstrap.min.css";

// Mon SCSS qui importe toutes les variables, layouts, components…)
import "./styles/app.scss";


/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import timeGridPlugin from "@fullcalendar/timegrid";

import Flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";



document.addEventListener("DOMContentLoaded", () => {
    const calendarEl = document.getElementById("calendar");
    if (calendarEl) {
        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin],
            initialView: "dayGridMonth",
            selectable: true,
            dateClick: info => alert("Date sélectionnée : " + info.dateStr)
        });
        calendar.render();
    }

    const dateInput = document.querySelector("#intervention_date");
    if (dateInput) {
        Flatpickr(dateInput, { enableTime: true, dateFormat: "Y-m-d H:i" });
    }
});