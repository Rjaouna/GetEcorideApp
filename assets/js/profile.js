(() => {
    // --- Routes (venant de Twig via window.PROFILE_ROUTES) ---
    const {
        profileUrl,
        switchRoleUrl,
        vehicleCreateUrl,
        vehicleDeleteCsrf, // on le garde si tu l’as déjà côté Twig; sinon meta fallback plus bas
    } = window.PROFILE_ROUTES || {};

    // --- Références DOM ---
    const userEl = document.getElementById("user-content");
    const vehEl = document.getElementById("vehicles-content");
    const carpEl = document.getElementById("carpoolings-content");
    const errEl = document.getElementById("p-error");
    const flashEl = document.getElementById("switch-flash");
    const isDriverAlert = document.getElementById("isDriver");
    const vehiclesDiv = document.getElementById("vehicles-div");
    const carpoolDiv = document.getElementById("carpoolings-div");
    const isDriverPref = document.getElementById("isDriverPref");

    // --- Modal Bootstrap (fiable avec getOrCreateInstance) ---
    const vehicleModalEl = document.getElementById("vehicleModal");
    const vehicleModal = vehicleModalEl
        ? bootstrap.Modal.getOrCreateInstance(vehicleModalEl)
        : null;

    // --- Helpers ---
    const fmtDate = (iso) =>
        iso
            ? new Intl.DateTimeFormat("fr-FR", {
                  dateStyle: "medium",
                  timeStyle: "short",
                  timeZone: "Europe/Paris",
              }).format(new Date(iso))
            : "—";

    const money = (n) =>
        n == null
            ? "—"
            : new Intl.NumberFormat("fr-FR", {
                  style: "currency",
                  currency: "EUR",
              }).format(n);

    const txt = (v) => v ?? "—";

    function showFlash(message, type = "warning") {
        if (!flashEl) return;
        flashEl.classList.remove(
            "d-none",
            "alert-warning",
            "alert-danger",
            "alert-success",
            "alert-info"
        );
        flashEl.classList.add("alert-" + type);
        flashEl.innerHTML = message;
    }

    function hideFlash() {
        if (!flashEl) return;
        flashEl.classList.add("d-none");
        flashEl.textContent = "";
    }

    function openVehicleModal() {
        const el = document.getElementById("vehicleModal");
        const form = document.getElementById("vehicle-form");
        const err = document.getElementById("vehicle-error");
        form?.reset();
        err?.classList.add("d-none");
        if (err) err.textContent = "";

        const bs = window.bootstrap;
        if (!bs?.Modal) return; // sécurité
        bs.Modal.getOrCreateInstance(el).show();
    }


    // --- Sauvegarde véhicule + éventuelle bascule vers driver (conditionnelle) ---
    async function saveVehicleAndMaybeSwitch() {
        const plate = document.getElementById("vehicle-plate").value.trim();
        const firstReg = document.getElementById("vehicle-firstReg").value;
        const brand = document.getElementById("vehicle-brand").value.trim();
        const model = document.getElementById("vehicle-model").value.trim();
        const seats = parseInt(
            document.getElementById("vehicle-seats").value,
            10
        );
        const isElec = document.getElementById("vehicle-electric").checked;
        const isAct = document.getElementById("vehicle-active").checked;
        const errBox = document.getElementById("vehicle-error");
        const btn = document.getElementById("vehicle-save");

        const errs = [];
        if (!plate) errs.push("Immatriculation obligatoire.");
        if (!firstReg)
            errs.push("Date de première mise en circulation obligatoire.");
        if (!brand) errs.push("Marque obligatoire.");
        if (!model) errs.push("Modèle obligatoire.");
        if (!Number.isInteger(seats) || seats < 1 || seats > 9)
            errs.push("Nombre de places invalide (1–9).");

        if (errs.length) {
            errBox.classList.remove("d-none");
            errBox.innerHTML = errs.map((x) => `<div>${x}</div>`).join("");
            return;
        }

        btn?.classList.add("disabled");
        try {
            // 1) Création du véhicule
            await axios.post(
                vehicleCreateUrl,
                {
                    plateNumber: plate,
                    firstRegistrationAt: firstReg,
                    brand,
                    model,
                    seats,
                    isElectric: isElec,
                    isActive: isAct,
                },
                {
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                }
            );

            vehicleModal?.hide();
            showFlash("<strong>Succès :</strong> véhicule ajouté.", "success");

            // 2) Vérifier le mode actuel APRÈS l'ajout
            const { data } = await axios.get(profileUrl, {
                headers: { Accept: "application/json" },
            });
            const isDriverNow = !!data.meta?.isDriver;

            // 3) Si on est passager -> activer driver. Si déjà driver -> ne rien faire.
            if (!isDriverNow) {
                await axios.post(
                    switchRoleUrl,
                    {},
                    { headers: { Accept: "application/json" } }
                );
                showFlash(
                    "<strong>Succès :</strong> mode driver activé.",
                    "success"
                );
            }

            // 4) Recharger l’UI
            await loadProfile();
        } catch (e) {
            const status = e?.response?.status;
            const msg =
                e?.response?.data?.message ||
                "Erreur lors de la création du véhicule.";
            errBox.classList.remove("d-none");
            errBox.textContent = (status ? `[${status}] ` : "") + msg;
        } finally {
            btn?.classList.remove("disabled");
        }
    }

    // --- UI: Bandeau mode driver/passager ---
    function renderBanner({ isDriver, cars, active }) {
        if (!isDriverAlert) return;

        isDriverAlert.classList.toggle("alert-success", isDriver);
        isDriverAlert.classList.toggle("alert-warning", !isDriver);
        isDriverAlert.classList.remove("alert-secondary");

        isDriverAlert.innerHTML = `
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      <strong>${isDriver ? "Mode driver activé !" : "Mode passager"}</strong>
      <span class="ms-2">
        ${
            isDriver
                ? `Voitures : <b>${cars}</b> — Covoiturages actifs : <b>${active}</b>`
                : "Vous ne voyez pas les véhicules/covoiturages en mode passager."
        }
      </span>
      <a href="#" id="switch-role" class="alert-link ms-2">Basculer de mode</a>.
    `;

        // Bind du switch (créé dynamiquement)
        const btn = document.getElementById("switch-role");
        btn?.addEventListener("click", async (e) => {
            e.preventDefault();
            hideFlash();
            btn.classList.add("disabled");
            try {
                await axios.post(
                    switchRoleUrl,
                    {},
                    {
                        headers: {
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    }
                );
                await loadProfile();
                showFlash(
                    "<strong>Succès :</strong> votre mode a été basculé.",
                    "success"
                );
            } catch (err) {
                const status = err?.response?.status;
                const apiMsg = err?.response?.data?.message;
                const action = err?.response?.data?.action;

                if (status === 422) {
                    console.log(status);
                    if (action?.type === "modal") openVehicleModal();
                    const cta = `<button type="button" class="btn btn-sm btn-primary ms-2" id="open-add-vehicle">Ajouter un véhicule</button>`;
                    showFlash(
                        `<strong>Attention :</strong> ${
                            apiMsg ?? "Aucun véhicule."
                        }${cta}`,
                        "warning"
                    );
                    setTimeout(() => {
                        document
                            .getElementById("open-add-vehicle")
                            ?.addEventListener("click", (ev) => {
                                ev.preventDefault();
                                openVehicleModal();
                            });
                    }, 0);
                } else {
                    showFlash(
                        `<strong>Erreur :</strong> ${
                            status ? "HTTP " + status : "Problème réseau"
                        }`,
                        "danger"
                    );
                }
            } finally {
                btn.classList.remove("disabled");
            }
        });
    }

    // --- UI: Rendu des véhicules (grille) ---
    function renderVehicles(vehicles) {
        const list = Array.isArray(vehicles) ? vehicles : [];
        if (!vehEl) return;

        // Option : mettre à jour un compteur si présent dans le header
        const countEl = document.getElementById("vehicles-count");
        if (countEl) countEl.textContent = String(list.length);

        const emptyEl = document.getElementById("vehicles-empty");
        if (emptyEl) emptyEl.classList.toggle("d-none", list.length > 0);

        vehEl.classList.add("row", "g-3"); // pour grille responsive
        vehEl.innerHTML = list.length
            ? list
                  .map(
                      (v) => `
    <div class="col-12 col-md-6 col-lg-4">
      <div class="border rounded-3 p-3 bg-body-tertiary h-100 position-relative">
        <div class="position-absolute top-0 end-0 m-2 d-flex gap-2">
          <!-- Modifier (existant, récup id pareil via data-id) -->
          <button type="button"
                  class="btn btn-sm btn-outline-secondary"
                  data-action="edit-vehicle"
                  data-id="${v.id}"
                  title="Modifier">
            <i class="bi bi-pencil"></i>
          </button>

          <!-- Supprimer (simple) -->
          <button type="button"
                  class="btn btn-sm btn-outline-danger"
                  data-action="delete-vehicle"
                  data-id="${v.id}"
                  title="Supprimer">
            <i class="bi bi-trash"></i>
          </button>
        </div>

        <div class="mb-2">
          <strong>${txt(v.brand)} ${txt(v.model)}</strong>
          <span class="text-muted ms-2">• ${txt(v.plateNumber)}</span>
        </div>

        <div class="small"><strong>Mise en circ. :</strong> ${fmtDate(
            v.firstRegistrationAt
        )}</div>
        <div class="small">
          <strong>Places :</strong> ${txt(v.seats)}
          ${
              v.isElectric
                  ? '<span class="badge bg-success ms-2 text-primary">Électrique</span>'
                  : ""
          }
        </div>
       
      </div>
    </div>
        `
                  )
                  .join("")
            : `</div>`;
    }

    // --- UI: Rendu des covoiturages ---
    function renderCarpools(carp) {
        const list = Array.isArray(carp) ? carp : [];
        if (!carpEl) return;

        carpEl.innerHTML = list.length
            ? list
                  .map((c) => {
                      const seats = `${
                          c.seatsAvaible ?? c.seatsAvailable ?? "—"
                      } / ${c.seatsTotal ?? "—"}`;
                      const eco = c.ecoTag
                          ? '<span class="badge bg-success ms-2">Éco</span>'
                          : "";
                      const badge = ((s) => {
                          const k = (s || "").toLowerCase();
                          if (k === "published") return "bg-success";
                          if (k === "cancelled") return "bg-danger";
                          return "bg-secondary";
                      })(c.status);

                      return `
            <div class="border rounded-2 p-2 mb-2 bg-white">
              <div class="d-flex align-items-center mb-1">
                <strong class="me-2">${txt(c.deparatureCity)} → ${txt(
                          c.arrivalCity
                      )}</strong>
                ${eco}
                <div class="ms-auto d-flex align-items-center gap-2">
                  <span class="badge ${badge}">${txt(c.status)}</span>
                  <button type="button" class="btn btn-sm btn-outline-secondary edit-carp" data-id="${
                      c.id
                  }" title="Modifier ce covoiturage">
                    <i class="bi bi-pencil"></i>
                  </button>
                </div>
              </div>
              <div class="row gy-1">
                <div class="col-12 col-md-4"><strong>Départ :</strong> ${fmtDate(
                    c.deparatureAt
                )}</div>
                <div class="col-12 col-md-4"><strong>Arrivée :</strong> ${fmtDate(
                    c.arrivalAt
                )}</div>
                <div class="col-12 col-md-4"><strong>Places :</strong> ${seats}</div>
                <div class="col-12 col-md-4"><strong>Prix :</strong> ${money(
                    c.price
                )}</div>
                <div class="col-12 col-md-8"><strong>Véhicule :</strong> ${txt(
                    c?.vehicle?.plateNumber
                )}</div>
              </div>
            </div>
          `;
                  })
                  .join("")
            : "Aucun covoiturage";
    }

    // --- Charger le profil + remplir l'UI ---
    async function loadProfile() {
        hideFlash();
        try {
            const { data } = await axios.get(profileUrl, {
                headers: { Accept: "application/json" },
            });
            const user = data.user;
            const meta = data.meta || {};

            renderBanner({
                isDriver: !!meta.isDriver,
                cars:
                    meta.vehiclesCount ??
                    (Array.isArray(user.vehicles) ? user.vehicles.length : 0),
                active: meta.activeCarpoolings ?? 0,
            });

            // afficher zone véhicules uniquement en driver
            vehiclesDiv?.classList.toggle("d-none", !meta.isDriver);
            isDriverPref?.classList.toggle("d-none", !meta.isDriver);

            // USER
            const fullName =
                [user.firstName, user.lastName].filter(Boolean).join(" ") ||
                "—";
            if (userEl) {
                userEl.innerHTML = `
          <div class="position-relative border rounded-3 p-3 bg-white mb-2">
            <button type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasPrefs"
                    aria-controls="offcanvasPrefs"
                    class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-2 edit-user"
                    data-id="${user.id}" title="Modifier le profil">
              <i class="bi bi-gear"></i>
            </button>

            <div class="row gy-2">
              <div class="col-12 col-md-6"><strong>Nom :</strong> ${fullName}</div>
              <div class="col-12 col-md-6"><strong>Pseudo :</strong> ${txt(
                  user.pseudo
              )}</div>
              <div class="col-12 col-md-6"><strong>Email :</strong> ${txt(
                  user.email
              )}</div>
              <div class="col-12 col-md-6"><strong>Téléphone :</strong> ${txt(
                  user.phone
              )}</div>
              <div class="col-12"><strong>Adresse :</strong> ${txt(
                  user.address
              )}</div>
              <div class="col-12 col-md-6"><strong>Date de naissance :</strong> ${fmtDate(
                  user.dateOfBirth
              )}</div>
            </div>
          </div>
        `;
            }

            // VEHICLES
            renderVehicles(user.vehicles);

            // CARPOOLINGS
            renderCarpools(user.carpoolings);
        } catch (e) {
            let msg = "Erreur réseau";
            if (e.response) {
                msg = `Erreur ${e.response.status}`;
                if (e.response.status === 401) msg += " — non authentifié";
                if (e.response.status === 403) msg += " — accès interdit";
                if (e.response.status === 404) msg += " — profil introuvable";
            }
            if (errEl) {
                errEl.textContent = msg;
                errEl.classList.remove("d-none");
            }
            if (userEl)
                userEl.textContent =
                    "Impossible de charger les infos utilisateur.";
            if (vehEl)
                vehEl.textContent = "Impossible de charger les véhicules.";
            if (carpEl)
                carpEl.textContent = "Impossible de charger les covoiturages.";
        }
    }

    // --- Écouteurs (attachés UNE SEULE FOIS) ---
    // Bouton "Ajouter un véhicule" (dans le header Twig, via délégation sur #vehicles-div)
    vehiclesDiv?.addEventListener("click", (e) => {
        const addBtn = e.target.closest('[data-action="add-vehicle"]');
        if (addBtn) {
            e.preventDefault();
            openVehicleModal();
            return;
        }
    });

    // SUPPRIMER (simple : on prend data-id et on call /api/vehicles/{id})
    vehiclesDiv?.addEventListener("click", async (e) => {
        const delBtn = e.target.closest('[data-action="delete-vehicle"]');
        if (!delBtn) return;

        e.preventDefault();
        const id = delBtn.dataset.id;
        console.debug(
            "DELETE id=",
            id,
            "URL=",
            `/api/vehicles/${encodeURIComponent(id)}`
        );
        if (!id) {
            showFlash(
                "<strong>Erreur :</strong> ID véhicule introuvable.",
                "danger"
            );
            return;
        }
        if (!confirm("Supprimer ce véhicule ?")) return;

        delBtn.classList.add("disabled");
        const csrf =
            window.PROFILE_ROUTES?.vehicleDeleteCsrf ||
            document.querySelector('meta[name="csrf-token"]')?.content ||
            "";

        try {
            await axios.delete(`/api/vehicles/${encodeURIComponent(id)}`, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrf,
                },
            });
            showFlash(
                "<strong>Succès :</strong> véhicule supprimé.",
                "success"
            );
            await loadProfile();
        } catch (err) {
            const status = err?.response?.status;
            const msg =
                err?.response?.data?.message || "Suppression impossible.";
            showFlash(
                `<strong>Erreur :</strong> ${
                    status ? "[" + status + "] " : ""
                }${msg}`,
                "danger"
            );
        } finally {
            delBtn.classList.remove("disabled");
        }
    });

    // Bouton "Enregistrer" du modal véhicule
    document.getElementById("vehicle-save")?.addEventListener("click", (e) => {
        e.preventDefault();
        saveVehicleAndMaybeSwitch();
    });

    // --- Boot ---
    loadProfile();
})();
