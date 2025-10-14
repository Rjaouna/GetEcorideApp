import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);

import axios from "axios";

document.addEventListener("DOMContentLoaded", async () => {
    const box = document.getElementById("profile-box");
    if (!box) return;

    const url = box.dataset.url; // URL générée par Twig
    const name = document.getElementById("p-name");
    const email = document.getElementById("p-email");
    const error = document.getElementById("p-error");

    try {
        const { data } = await axios.get(url, {
            headers: { Accept: "application/json" },
        });
        // Adapte ces champs à ceux exposés par ton groupe "profile:read"
        name.textContent =
            [data.firstName, data.lastName].filter(Boolean).join(" ") ||
            "(Sans nom)";
        email.textContent = data.email || "(Email non défini)";
    } catch (e) {
        error.textContent = e.response
            ? `Erreur ${e.response.status}`
            : "Erreur réseau";
    }
});
