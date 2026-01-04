// assets/app.js

// Styles
import "../assets/styles/app.scss";
import "../assets/styles/fullcalendar/fullcalendar.scss";

// Bootstrap (modales, tooltips, etc.)
import { Modal } from "bootstrap";

//import { Modal, Tooltip, Toast } from "bootstrap";

//import 'bootstrap/dist/css/bootstrap.min.css';
// Stimulus
import { Application } from "stimulus";

const app = Application.start();

// Charge tous les controllers depuis ./controllers
const controllers = import.meta.glob('./controllers/**/*.js', { eager: true });

Object.entries(controllers).forEach(([path, controllerModule]) => {
    const name = path.split('/').pop().replace('_controller.js', '');
    app.register(name, controllerModule.default);
});

// Exemple : si tu as un controller Flatpickr à enregistrer manuellement
import FlatpickrController from './controllers/flatpickr_controller.js';
app.register("flatpickr", FlatpickrController);
// Calendar init
import { initCalendar } from "../assets/js/calendar/calendar";

// Si tu veux que le calendrier s'initialise automatiquement pour un target existant
document.addEventListener("DOMContentLoaded", () => {
    const calendarEl = document.getElementById("calendar");
    if (calendarEl) {
        console.log("InitCalendar lancé !", calendarEl);
        initCalendar(calendarEl);
    }
});

/**
 * MODAL UNIVERSELLE
 */
window.openGlobalModal = function ({ title, body, footer = null }) {
    const titleEl = document.getElementById('globalModalTitle');
    const bodyEl = document.getElementById('globalModalBody');
    const footerEl = document.getElementById('globalModalFooter');
    const modalEl = document.getElementById('globalModal');

    if (!modalEl) {
        console.error('Global modal not found in DOM');
        return;
    }

    titleEl.innerHTML = title || '';
    bodyEl.innerHTML = body || '';

    if (footer) {
        footerEl.innerHTML = footer;
        footerEl.classList.remove('d-none');
    } else {
        footerEl.innerHTML = '';
        footerEl.classList.add('d-none');
    }

    const modal = new Modal(modalEl);
    modal.show();
};

document.addEventListener('click', function (e) {
    const link = e.target.closest('.open-doc-modal');
    if (!link) return;

    e.preventDefault();

    const title = link.dataset.title;
    const url = link.dataset.url;

    if (!url) {
    console.warn('Document URL missing');
    return;
}

    openGlobalModal({
        title: title,
        body: `
            <div class="text-center py-5">
                <div class="spinner-border text-primary"></div>
            </div>
        `
    });

    setTimeout(() => {
        document.getElementById('globalModalBody').innerHTML = `
            <iframe src="${url}"
                    style="width:100%;height:75vh;border:none"></iframe>
        `;
    }, 150);



});

/**
 * Ouvre un PDF dans la modal universelle
 */
window.openPdfModal = function (url, title = 'Document PDF') {

    if (!url) {
        console.warn('openPdfModal: URL manquante');
        return;
    }

    openGlobalModal({
        title: title,
        body: `
            <div class="text-center py-5">
                <div class="spinner-border text-primary"></div>
            </div>
        `,
        footer: `
            <button type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                Fermer
            </button>

            <a href="${url}"
               target="_blank"
               class="btn btn-outline-primary">
                Ouvrir dans un nouvel onglet
            </a>
        `
    });

    // Remplace le spinner par le PDF
    setTimeout(() => {
        const bodyEl = document.getElementById('globalModalBody');
        if (!bodyEl) return;

        bodyEl.innerHTML = `
            <iframe src="${url}"
                    style="width:100%;height:75vh;border:none">
            </iframe>
        `;
    }, 150);
};
document.addEventListener('click', (e) => {
    const link = e.target.closest('.open-pdf');
    if (!link) return;

    e.preventDefault();

    openPdfModal(
        link.dataset.url,
        link.dataset.title
    );
});

/**
 * Modal création intervention (étape A)
 */
window.openCreateInterventionModal = function (info) {

    console.log('openCreateInterventionModal appelée', info);

   const start = toDatetimeLocal(info.startStr);
    const end = toDatetimeLocal(info.endStr);
    const technicienId = info.resource ? info.resource.id : null;

   // const technicienNom = info.resource ? info.resource.title : '';

    openGlobalModal({
        title: 'Créer une intervention',
        body: `
            <form class="p-4">

                <div class="mb-3">
                    <label class="form-label">Début</label>
                    <input type="datetime-local"
                           class="form-control"
                           value="${start}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Fin</label>
                    <input type="datetime-local"
                           class="form-control"
                           value="${end}">
                </div>

    ${buildTechnicienSelect(technicienId)}

                <div class="mb-3">
                    <label class="form-label">Référence chantier</label>
                    <input type="text"
                           class="form-control"
                           placeholder="Ex : CH-2025-001">
                </div>

            </form>
        `,
        footer: `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Annuler
            </button>
            <button type="button" class="btn btn-primary" disabled>
                Créer (étape suivante)
            </button>
        `
    });
};

//select technicien

function buildTechnicienSelect(selectedId = null) {
    let options = `<option value="">— Sélectionner —</option>`;

    window.TECHNICIENS.forEach(t => {
        const selected = selectedId && selectedId == t.id ? 'selected' : '';
        options += `<option value="${t.id}" ${selected}>${t.nom}</option>`;
    });

    return `
        <div class="mb-3">
            <label class="form-label">Technicien</label>
            <select class="form-select" id="createTechnicien">
                ${options}
            </select>
        </div>
    `;
}

function toDatetimeLocal(dateStr) {
    return dateStr.slice(0, 16);
}