// assets/js/calendar/calendar.js
import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import listPlugin from "@fullcalendar/list";
import frLocale from "@fullcalendar/core/locales/fr";

/**
 * Initialise le calendrier FullCalendar
 * @param {HTMLElement} calendarEl
 */
export function initCalendar(calendarEl) {
    if (!calendarEl) return;

    console.log("InitCalendar lancé !", calendarEl);

    let selectedTech = ""; // valeur par défaut = tous les techniciens
    const technicienSelect = document.getElementById("technicienFilter");
    const techButtons = document.querySelectorAll("[data-tech-id]");

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: "timeGridWeek",
        locale: frLocale,
        selectable: true,
        editable: true,
        selectMirror: true,
        eventResizableFromStart: true,
        eventDurationEditable: true,
        eventStartEditable: true,
        height: "auto",

        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,listWeek,timeGridDay"
        },

        slotMinTime: "07:00:00",
        slotMaxTime: "17:00:00",
        slotDuration: "00:30:00",
        weekends: false,
        allDaySlot: false,

        views: {
            listWeek: { buttonText: "Semaine" }
        },

        /**callback select */
        select: function (info) {
   // console.log('Plage sélectionnée :', info.startStr, info.endStr);
    console.log('Sélection :', info.startStr, info.endStr);
    console.log('Technicien :', info.resource);

    if (typeof window.openCreateInterventionModal === 'function') {
        window.openCreateInterventionModal(info);
    } else {
        console.warn('openCreateInterventionModal non définie');
    }
},

        /** ------------------------------
         *  FETCH EVENTS FILTRÉS PAR TECH
         * ------------------------------ */
        events: async function (fetchInfo, success, fail) {
            try {
                const res = await fetch(
                    `/calendar/events?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}&tech=${selectedTech}`
                );
                success(await res.json());
            } catch (err) {
                fail(err);
            }
        },

        /** ------------------------------
         *  RESSOURCES (TECHNICIENS)
         * ------------------------------ */
        resources: async function (fetchInfo, success, fail) {
            try {
                const res = await fetch("/calendar/techniciens");
                const data = await res.json();

                success(
                    data.map(t => ({
                        id: t.id,
                        title: t.nom,
                        color: t.couleur || "#3788d8",
                    }))
                );
            } catch (err) {
                fail(err);
            }
        },

        /** ------------------------------
         *  CLIC → PAGE DÉTAIL
         * ------------------------------ */
        eventClick: info => {
            window.location.href = `/intervention/${info.event.id}`;
        },

        /** ------------------------------
         *  DRAG / RESIZE = UPDATE AJAX
         * ------------------------------ */
        eventDrop: info => updateEvent(info.event),
        eventResize: info => updateEvent(info.event),

        /** ------------------------------
         *  RENDU PERSONNALISÉ
         * ------------------------------ */
        eventDidMount: info => {
            const e = info.event;

            const heureDebut = e.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const heureFin = e.end?.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) || "";
            const adresse = e.extendedProps.adresse || "Adresse inconnue";

            info.el.innerHTML = "";
            const div = document.createElement("div");
            div.textContent = `${heureDebut} - ${heureFin} : ${adresse}`;
            div.style.textAlign = "center";
            div.style.whiteSpace = "normal";
            div.style.padding = "2px 4px";

            // Couleur = technicien
            if (e.extendedProps.color) {
                info.el.style.background = e.extendedProps.color;
                info.el.style.borderColor = e.extendedProps.color;
                div.style.color = "#fff";
            }

            info.el.appendChild(div);

            // Double-clic = modale
            info.el.addEventListener("dblclick", () => openEditModal(e));
        }
    });

    calendar.render();

    /** ------------------------------
     *  FILTRES TECHNICIENS (select + boutons)
     * ------------------------------ */
    technicienSelect?.addEventListener("change", e => {
        selectedTech = e.target.value;
        resetButtons();
        calendar.refetchEvents();
    });

    techButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            selectedTech = btn.dataset.techId;
            resetButtons();
            btn.classList.add("active");
            technicienSelect.value = selectedTech;
            calendar.refetchEvents();
        });
    });

    function resetButtons() {
        techButtons.forEach(b => b.classList.remove("active"));
    }

    /** --------------------------
     *  MODALE ÉDITION
     * -------------------------- */
    function openEditModal(event) {
        const modal = document.getElementById("editEventModal");
        modal.querySelector("#editEventId").value = event.id;
        modal.querySelector("#editStart").value = event.start.toISOString().slice(0, 16);
        modal.querySelector("#editEnd").value = event.end.toISOString().slice(0, 16);
        modal.querySelector("#editTechnicien").value = event.extendedProps.technicienId || "";

        new bootstrap.Modal(modal).show();
    }

    document.getElementById("saveEventBtn")?.addEventListener("click", async () => {
        const id = document.getElementById("editEventId").value;
        const start = document.getElementById("editStart").value;
        const end = document.getElementById("editEnd").value;
        const technicien = document.getElementById("editTechnicien").value;

        const res = await fetch("/calendar/update", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id, start, end, technicien })
        });

        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById("editEventModal")).hide();
            calendar.refetchEvents();
            showToast("Intervention mise à jour !");
        } else {
            showToast("Erreur lors de la mise à jour", "#e74c3c");
        }
    });

    /** --------------------------
     *  UPDATE AJAX DRAG & DROP
     * -------------------------- */
    function updateEvent(event) {
        fetch("/calendar/update", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                id: event.id,
                start: event.start.toISOString(),
                end: event.end?.toISOString() || event.start.toISOString()
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast("Intervention mise à jour !");
                } else {
                    showToast(data.error || "Erreur inconnue", "#e67e22");
                }
            })
            .catch(() => showToast("Erreur de mise à jour", "#e74c3c"));
    }

    /** --------------------------
     *  TOAST VISUEL
     * -------------------------- */
    function showToast(message, color = "#2ecc71") {
        const toast = document.createElement("div");
        toast.className = "toast";
        toast.style.background = color;
        toast.textContent = message;

        document.body.appendChild(toast);

        setTimeout(() => toast.classList.add("show"), 10);
        setTimeout(() => {
            toast.classList.remove("show");
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}
