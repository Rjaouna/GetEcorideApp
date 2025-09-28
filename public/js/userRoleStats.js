// public/js/userRoleStats.js
document.addEventListener("DOMContentLoaded", async () => {
    const el = document.getElementById("role-stats");
    if (!el) return;

    const endpoint = el.getAttribute("data-endpoint");

    // Optionnel : spinner simple pendant le chargement
    el.innerHTML = `
    <div class="d-flex align-items-center text-muted" style="gap:.5rem;">
      <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
      <span>Chargement des statistiques…</span>
    </div>
  `;

    try {
        const res = await fetch(endpoint, {
            headers: { Accept: "application/json" },
        });
        if (!res.ok) throw new Error("HTTP " + res.status);
        const data = await res.json();

        const total = Number(data.total ?? 0) || 0;
        const byRole = data.byRole || {};

        // Petites couleurs par rôle (facultatif)
        const colorByRole = {
            ROLE_ADMIN: "bg-danger",
            ROLE_DRIVER: "bg-warning",
            ROLE_PASSAGER: "bg-info",
            ROLE_EMPLOYE: "bg-success",
        };

        // Util classes compat Bootstrap 4/5
        const gapEnd = "me-2";

        const rows = Object.entries(byRole)
            .map(([roleLabel, countRaw]) => {
                const count = Number(countRaw) || 0;
                const pct = total
                    ? Math.min(100, Math.round((count * 100) / total))
                    : 0;

                const roleCodeGuess = roleLabel
                    .toUpperCase()
                    .replace(/\s+/g, "_"); // ex: "Role admin" -> "ROLE_ADMIN"
                const barColor = colorByRole[roleCodeGuess] || "bg-primary";

                return `
        <tr>
          <td>
            <div class="d-flex justify-content-between align-items-center flex-wrap">
              <strong>${roleLabel}</strong>
              <small class="text-muted">${pct}%</small>
            </div>
            <div class="progress progress-sm ${gapEnd}" style="height:6px;">
              <div class="progress-bar ${barColor}" role="progressbar"
                   style="width:${pct}%"
                   aria-valuenow="${count}" aria-valuemin="0" aria-valuemax="${total}">
              </div>
            </div>
          </td>
          <td class="text-end">${count}</td>
        </tr>
      `;
            })
            .join("");

        el.innerHTML = `
        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Utilisateurs par rôle</div>
        <div class="text-muted mb-2">
          Nombre total des utilisateurs :
          <span class="text-primary fw-bold">${total}</span>
        </div>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <tbody>
              ${
                  rows ||
                  `<tr><td colspan="2" class="text-muted">Aucune donnée.</td></tr>`
              }
            </tbody>
          </table>
        </div>
    `;
    } catch (e) {
        console.error(e);
        el.innerHTML = `<div class="alert alert-danger">Erreur de chargement des stats.</div>`;
    }
});
