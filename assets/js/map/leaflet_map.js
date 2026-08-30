/**
 * ==================================================
 * MEB-APP
 * Module : Carte Leaflet
 * --------------------------------------------------
 * Rôle :
 * Gestion des cartes Leaflet de l'application.
 *
 * Fonctionnalités :
 * - Initialisation carte accueil
 * - Gestion carte plein écran
 * - Affichage interventions
 *
 * Dépendances :
 * - Leaflet
 *
 * Utilisé par :
 * - assets/app.js
 * 
 *  * Dernière mise à jour :
 * 23/05/2026
 * ==================================================
 */
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

let map;
let modalMap;

export function initMap() {
    const mapEl = document.getElementById('map');
    if (!mapEl) return;

    const map = L.map('map').setView([48.8566, 2.3522], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    return map;
}
//  FONCTION agrandir la carte dans la modal
export function openMapModal() {

    setTimeout(() => {
        const el = document.getElementById('mapModalContainer');

        if (!el) return;

        // évite double init
        if (modalMap) {
            modalMap.remove();
        }

        modalMap = L.map('mapModalContainer').setView([48.8566, 2.3522], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(modalMap);

        modalMap.invalidateSize();

    }, 300);
}