// assets/js/calendar/calendar.js
import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import frLocale from "@fullcalendar/core/locales/fr";
import resourceTimeGridPlugin from "@fullcalendar/resource-timegrid"; //le plugin supplémentaire pour afficher planning à la verticale


export function initCalendar() {
    const calendarEl = document.getElementById("calendar");
    const technicienSelect = document.getElementById("technicienFilter");
    const techButtons = document.querySelectorAll("[data-tech-id]");
    let selectedTech = "";

    if (!calendarEl) return;

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: "timeGridWeek",
        locale: frLocale,
        height: "auto",
        editable: true, //  Permet le drag & drop
        eventResizableFromStart: true,  // permet de redimensionner depuis le début
        selectable: true,
        eventDurationEditable: true,
        eventStartEditable: true,


        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridDay,timeGridWeek,dayGridMonth' // boutons visibles
        },
        slotMinTime: "07:00:00",
        slotMaxTime: "17:00:00",
        weekends: false,
        allDaySlot: false,
        slotDuration: "00:30:00",
        height: "auto",

        // Chaque événement lié à un technicien
        events: async function (fetchInfo, successCallback, failureCallback) {
            try {
                const res = await fetch(
                    `/calendar/events?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`
                );
                const events = await res.json();
                successCallback(events);
            } catch (err) {
                failureCallback(err);
            }
        },

        eventClick: (info) => {
            window.location.href = `/intervention/${info.event.id}`;
        },

        // Liste des techniciens = colonnes
        resources: async function (fetchInfo, successCallback, failureCallback) {
            try {
                const res = await fetch("/calendar/techniciens");
                const data = await res.json();

                successCallback(
                    data.map((t) => ({
                        id: t.id,
                        title: t.nom,
                        color: t.couleur || "#3788d8",
                    }))
                );
            } catch (err) {
                failureCallback(err);
            }
        },
        // Déplacement ou redimensionnement direct
        eventDrop: info => updateEvent(info.event),
        eventResize: info => updateEvent(info.event),

        //  Affichage personnalisé (inchangé)
        eventDidMount: info => {
            const technicien = info.event.extendedProps.technicien || "";
            const start = info.event.start;
            const end = info.event.end;
            const heureDebut = start ? start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
            const heureFin = end ? end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
            const adresse = info.event.extendedProps.adresse || 'Adresse inconnue';

            info.el.innerHTML = '';
            const textDiv = document.createElement('div');
            textDiv.textContent = `${heureDebut} - ${heureFin} : ${adresse}`;
            textDiv.style.whiteSpace = 'normal';
            textDiv.style.textAlign = 'center';
            textDiv.style.padding = '2px 4px';
            info.el.appendChild(textDiv);

            if (info.event.extendedProps.color) {
                info.el.style.backgroundColor = info.event.extendedProps.color;
                info.el.style.borderColor = info.event.extendedProps.color;
                textDiv.style.color = '#fff';
            }
            info.el.setAttribute(
                "title",
                `${technicien}\n${start} - ${end}\n${adresse}`
            );

            // Ajoute un écouteur de double-clic pour ouvrir la modale d'édition
            info.el.addEventListener("dblclick", () => {
                const event = info.event;
                const modal = document.getElementById("editEventModal");

                modal.querySelector("#editEventId").value = event.id;
                modal.querySelector("#editStart").value = event.start.toISOString().slice(0, 16);
                modal.querySelector("#editEnd").value = event.end.toISOString().slice(0, 16);
                modal.querySelector("#editTechnicien").value = event.extendedProps.technicienId || "";

                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
            });
        },

    });
    // Événement : quand on clique sur "Enregistrer"
    document.getElementById("saveEventBtn").addEventListener("click", async () => {
        const id = document.getElementById("editEventId").value;
        const start = document.getElementById("editStart").value;
        const end = document.getElementById("editEnd").value;
        const technicien = document.getElementById("editTechnicien").value;

        const res = await fetch("/calendar/update", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id, start, end, technicien }),
        });

        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById("editEventModal")).hide();
            calendar.refetchEvents();
        } else {
            alert("Erreur lors de la mise à jour de l’intervention");
        }
    });
    calendar.render();

    technicienSelect?.addEventListener("change", e => {
        selectedTech = e.target.value;
        calendar.refetchEvents();
        resetButtonStates();
    });

    techButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            selectedTech = btn.dataset.techId || "";
            resetButtonStates();
            btn.classList.add("active");
            technicienSelect.value = selectedTech;
            calendar.refetchEvents();
        });
    });

    function resetButtonStates() {
        techButtons.forEach(b => b.classList.remove("active"));
    }

    //  Fonction de mise à jour (AJAX)
    function updateEvent(event) {
        const data = {
            start: event.start.toISOString(),
            end: event.end ? event.end.toISOString() : null,
        };

        fetch(`/calendar/update/${event.id}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify(data),
        })
            .then(res => {
                if (!res.ok) throw new Error("Erreur de mise à jour");
                return res.json();
            })
            .then(() => console.log(" Intervention mise à jour"))
            .catch(err => alert(" Erreur : " + err.message));
    }

    function updateEvent(event) {
        fetch("/calendar/update", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                id: event.id,
                start: event.start.toISOString(),
                end: event.end ? event.end.toISOString() : event.start.toISOString(),
            }),
        })
            .then((res) => {
                if (!res.ok) throw new Error("Erreur de mise à jour");
                return res.json();
            })
            .then((data) => {
                if (data.success) {
                    showToast(" Intervention mise à jour !");
                } else {
                    showToast((data.error || "Erreur inconnue"), "#e67e22");
                }
            })
            .catch((err) => {
                console.error(err);
                showToast(" Erreur de mise à jour", "#e74c3c");
            });
    }

    function showToast(message, color = "#2ecc71") {
        const toast = document.createElement("div");
        toast.className = "toast";
        toast.style.backgroundColor = color;
        toast.textContent = message;

        document.body.appendChild(toast);

        // Affiche le toast
        setTimeout(() => toast.classList.add("show"), 50);

        // Le supprime après 3 secondes
        setTimeout(() => {
            toast.classList.remove("show");
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

}