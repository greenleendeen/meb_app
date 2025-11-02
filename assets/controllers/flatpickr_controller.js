import { Controller } from "stimulus";
import Flatpickr from "flatpickr";

export default class extends Controller {
    static targets = ["input"];

    connect() {
        if (!this.hasInputTarget) return;

        Flatpickr(this.inputTarget, {
            enableTime: this.inputTarget.dataset.flatpickrEnableTime === 'true',
            noCalendar: this.inputTarget.dataset.flatpickrNoCalendar === 'true',
            minuteIncrement: parseInt(this.inputTarget.dataset.flatpickrMinuteIncrement) || 1,
            minTime: this.inputTarget.dataset.flatpickrMinTime || null,
            maxTime: this.inputTarget.dataset.flatpickrMaxTime || null,
            dateFormat: "Y-m-d H:i",
        });

        console.log("Flatpickr connecté !", this.inputTarget);
    }
}
