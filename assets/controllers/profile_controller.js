import { Controller } from "@hotwired/stimulus";
import axios from "axios";

export default class extends Controller {
    static values = { id: Number };
    static targets = ["output", "error"];

    async connect() {
        try {
            const { data } = await axios.get(`/profile/${this.idValue}`);
            this.outputTarget.textContent = JSON.stringify(data, null, 2);
        } catch (e) {
            this.errorTarget.textContent = e.response
                ? `Erreur ${e.response.status}`
                : "Erreur réseau";
        }
    }
}
