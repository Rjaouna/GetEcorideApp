// public/js/lockedAccounts.js
document.addEventListener("DOMContentLoaded", async () => {
    const counterEl = document.getElementById("lockedAccountes");
    if (!counterEl) return;

    const endpoint =
        counterEl.getAttribute("data-endpoint") ||
        "/admin/users/stats/locked-accounts";
    const old = counterEl.textContent;
    counterEl.textContent = "…";

    try {
        const res = await fetch(endpoint, {
            headers: { Accept: "application/json" },
        });
        if (!res.ok) throw new Error("HTTP " + res.status);
        const data = await res.json();

        const total = Number(data.lockedCount ?? 0) || 0;
        const lockedUsers = Array.isArray(data.lockedUsers)
            ? data.lockedUsers
            : [];

        // 1) Compteur formaté
        counterEl.textContent = new Intl.NumberFormat("fr-FR").format(total);

        // 2) Petite phrase (si un conteneur est présent)
        const summaryEl = document.getElementById("lockedAccountsSummary");
        if (summaryEl) {
            const phrase =
                total === 0
                    ? "Aucun compte bloqué."
                    : total === 1
                    ? "1 compte bloqué."
                    : `${new Intl.NumberFormat("fr-FR").format(
                          total
                      )} comptes bloqués.`;
            summaryEl.textContent = phrase;
        }

        // 3) Liste avec liens email → compte
        const listEl = document.getElementById("lockedAccountsList");
        if (listEl) {
            const urlTpl =
                listEl.getAttribute("data-user-url") || "/admin/users/{id}";

            if (!lockedUsers.length) {
                listEl.innerHTML = `<div class="text-muted">Aucun compte bloqué.</div>`;
            } else {
                const rows = lockedUsers
                    .map((u) => {
                        const id = u.id ?? "";
                        const email = u.email ?? "-";
                        const userUrl = urlTpl.replace(
                            "{id}",
                            encodeURIComponent(id)
                        );
                        return `
              <tr>
                <td>${id}</td>
                <td><a href="${userUrl}" class="text-decoration-none">${email}</a></td>
              </tr>
            `;
                    })
                    .join("");

                listEl.innerHTML = `
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                
                </tr>
              </thead>
              <tbody>${rows}</tbody>
            </table>
          </div>
        `;
            }
        }
    } catch (e) {
        console.error(e);
        counterEl.textContent = old || "—";
        counterEl.title = "Erreur de chargement des stats";
        const listEl = document.getElementById("lockedAccountsList");
        if (listEl) {
            listEl.innerHTML = `<div class="alert alert-danger">Erreur de chargement des comptes bloqués.</div>`;
        }
        const summaryEl = document.getElementById("lockedAccountsSummary");
        if (summaryEl)
            summaryEl.textContent =
                "Impossible de récupérer le nombre de comptes bloqués.";
    }
});
