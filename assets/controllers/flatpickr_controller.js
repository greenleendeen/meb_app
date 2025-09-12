import { Controller } from "stimulus";
import Flatpickr from "flatpickr";

export default class extends Controller {
    static targets = ["input"]

    connect() {
        if (!this.hasInputTarget) return;

        Flatpickr(this.inputTarget, {
            enableTime: true,
            dateFormat: "Y-m-d H:i"
        });
    }
}