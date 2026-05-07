const apiBase = "../php";

const state = {
  session: {
    authenticated: false,
    role: "guest",
    full_name: "",
    user_id: null
  },
  offers: []
};

function escapeHtml(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

async function fetchJSON(url) {
  const response = await fetch(url, {
    credentials: "same-origin",
    headers: {
      Accept: "application/json"
    }
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(data.message || "Une erreur est survenue.");
    error.payload = data;
    throw error;
  }

  return data;
}

function buildFlash(type, message) {
  return `<div class="flash flash-${escapeHtml(type)}">${escapeHtml(message)}</div>`;
}

function displayQueryFlash() {
  const flashContainer = document.getElementById("flash-container");

  if (!flashContainer) {
    return;
  }

  const params = new URLSearchParams(window.location.search);
  const status = params.get("status");
  const message = params.get("message");

  if (status && message) {
    flashContainer.innerHTML = buildFlash(status, message);
    params.delete("status");
    params.delete("message");
    const queryString = params.toString();
    const cleanUrl = `${window.location.pathname}${queryString ? `?${queryString}` : ""}${window.location.hash}`;
    window.history.replaceState({}, document.title, cleanUrl);
  }
}

function formatDate(value) {
  if (!value) {
    return "Non disponible";
  }

  const date = new Date(value.replace(" ", "T"));
  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return date.toLocaleDateString("fr-FR", {
    year: "numeric",
    month: "long",
    day: "numeric"
  });
}

function truncateText(text, maxLength = 170) {
  const clean = String(text ?? "").trim();
  if (clean.length <= maxLength) {
    return clean;
  }

  return `${clean.slice(0, maxLength).trim()}...`;
}

function statusBadge(status) {
  const safeStatus = String(status || "pending").toLowerCase();
  const labels = {
    pending: "En attente",
    accepted: "Acceptée",
    rejected: "Refusée"
  };

  return `<span class="badge badge-${escapeHtml(safeStatus)}">${escapeHtml(labels[safeStatus] || safeStatus)}</span>`;
}

function updateNavigation() {
  const dashboardLink = document.getElementById("nav-dashboard-link");
  const authLink = document.getElementById("nav-auth-link");
  const registerLink = document.getElementById("nav-register-link");

  if (dashboardLink && state.session.role !== "admin") {
    dashboardLink.style.display = "none";
  }

  if (state.session.authenticated && authLink) {
    authLink.textContent = state.session.full_name || "Mon espace";
    authLink.href = state.session.role === "admin" ? "dashboard.html" : "portfolio.html";
  }

  if (state.session.authenticated && registerLink) {
    registerLink.textContent = "Déconnexion";
    registerLink.href = `${apiBase}/logout.php`;
  }
}

function renderEmpty(target, title, description) {
  target.innerHTML = `
    <div class="empty-state">
      <p class="mb-1">${escapeHtml(title)}</p>
      <p class="small-text">${escapeHtml(description)}</p>
    </div>
  `;
}

function buildOfferAction(offer) {
  if (!state.session.authenticated) {
    return `<a href="login.html" class="btn btn-primary">Se connecter pour postuler</a>`;
  }

  if (state.session.role === "admin") {
    return `<a href="dashboard.html" class="btn btn-outline">Gérer cette offre</a>`;
  }

  if (offer.has_applied) {
    return `<button type="button" class="btn btn-success" disabled>Candidature envoyée</button>`;
  }

  return `
    <form action="${apiBase}/apply_offer.php" method="post">
      <input type="hidden" name="offer_id" value="${escapeHtml(offer.id)}">
      <button type="submit" class="btn btn-primary">Postuler à cette offre</button>
    </form>
  `;
}

function buildOfferCard(offer, compact = false) {
  const description = truncateText(offer.description, compact ? 110 : 190);

  return `
    <div class="card">
      <h3 class="card-title">${escapeHtml(offer.title)}</h3>
      <div class="meta-list">
        <span class="meta-item">${escapeHtml(offer.company)}</span>
        <span class="meta-item">${escapeHtml(offer.location)}</span>
        <span class="meta-item">${escapeHtml(offer.duration)}</span>
      </div>
      <p class="card-text offer-description">${escapeHtml(description)}</p>
      <p class="small-text mb-0">Publiée le ${escapeHtml(formatDate(offer.created_at))}</p>
      <div class="offer-actions mt-2">
        ${buildOfferAction(offer)}
      </div>
    </div>
  `;
}

async function loadSession() {
  try {
    const data = await fetchJSON(`${apiBase}/session.php`);
    state.session = data;
  } catch (error) {
    state.session = {
      authenticated: false,
      role: "guest",
      full_name: "",
      user_id: null
    };
  }
}

async function loadOffers(limit = "") {
  const suffix = limit ? `?limit=${encodeURIComponent(limit)}` : "";
  const data = await fetchJSON(`${apiBase}/fetch_offers.php${suffix}`);
  state.offers = data.offers || [];
  return state.offers;
}

function renderHomeOffers() {
  const target = document.getElementById("home-offers-grid");

  if (!target) {
    return;
  }

  if (!state.offers.length) {
    renderEmpty(target, "Aucune offre disponible", "Les administrateurs peuvent publier de nouvelles offres depuis le dashboard.");
    return;
  }

  target.innerHTML = state.offers.slice(0, 3).map((offer) => buildOfferCard(offer, true)).join("");
}

function applyOfferFilters() {
  const target = document.getElementById("offers-grid");

  if (!target) {
    return;
  }

  const keyword = document.getElementById("filter-keyword")?.value.trim().toLowerCase() || "";
  const company = document.getElementById("filter-company")?.value.trim().toLowerCase() || "";
  const location = document.getElementById("filter-location")?.value.trim().toLowerCase() || "";
  const duration = document.getElementById("filter-duration")?.value.trim().toLowerCase() || "";
  const sort = document.getElementById("filter-sort")?.value || "recent";

  const filtered = [...state.offers].filter((offer) => {
    const haystack = `${offer.title} ${offer.company} ${offer.location} ${offer.description}`.toLowerCase();
    const matchesKeyword = !keyword || haystack.includes(keyword);
    const matchesCompany = !company || offer.company.toLowerCase().includes(company);
    const matchesLocation = !location || offer.location.toLowerCase().includes(location);
    const matchesDuration = !duration || offer.duration.toLowerCase().includes(duration);

    return matchesKeyword && matchesCompany && matchesLocation && matchesDuration;
  });

  filtered.sort((first, second) => {
    if (sort === "company") {
      return first.company.localeCompare(second.company, "fr");
    }

    if (sort === "location") {
      return first.location.localeCompare(second.location, "fr");
    }

    return new Date(second.created_at) - new Date(first.created_at);
  });

  if (!filtered.length) {
    renderEmpty(target, "Aucune offre ne correspond aux filtres", "Modifiez les critères de recherche pour afficher d'autres résultats.");
    return;
  }

  target.innerHTML = filtered.map((offer) => buildOfferCard(offer)).join("");
}

function resetOfferFilters() {
  ["filter-keyword", "filter-company", "filter-location"].forEach((id) => {
    const field = document.getElementById(id);
    if (field) {
      field.value = "";
    }
  });

  const duration = document.getElementById("filter-duration");
  const sort = document.getElementById("filter-sort");

  if (duration) {
    duration.value = "";
  }

  if (sort) {
    sort.value = "recent";
  }

  applyOfferFilters();
}

function attachOfferFilters() {
  ["filter-keyword", "filter-company", "filter-location", "filter-duration", "filter-sort"].forEach((id) => {
    const field = document.getElementById(id);
    if (field) {
      field.addEventListener("input", applyOfferFilters);
      field.addEventListener("change", applyOfferFilters);
    }
  });

  document.getElementById("reset-filters")?.addEventListener("click", resetOfferFilters);
}

function fillPortfolioForm(data) {
  const form = document.getElementById("portfolio-form");

  if (!form) {
    return;
  }

  const fields = ["full_name", "email", "phone", "bio", "skills", "education", "experience", "languages"];
  fields.forEach((field) => {
    const element = form.querySelector(`[name="${field}"]`);
    if (element) {
      element.value = data[field] || "";
    }
  });

  const cvBox = document.getElementById("portfolio-current-cv");
  if (cvBox) {
    cvBox.innerHTML = data.cv_url
      ? `<a href="${escapeHtml(data.cv_url)}" class="inline-link" target="_blank" rel="noopener">Voir le CV actuel</a>`
      : `<p class="small-text mb-0">Aucun CV enregistré pour le moment.</p>`;
  }
}

function renderPortfolioPreview(data, publicMode = false) {
  const previewName = document.getElementById("preview-name");
  const previewBio = document.getElementById("preview-bio");
  const previewMeta = document.getElementById("preview-meta");
  const previewSkills = document.getElementById("preview-skills");
  const previewEducation = document.getElementById("preview-education");
  const previewExperience = document.getElementById("preview-experience");
  const previewLanguages = document.getElementById("preview-languages");
  const publicLinkText = document.getElementById("public-link-text");
  const publicLinkAnchor = document.getElementById("public-link-anchor");

  if (!previewName) {
    return;
  }

  previewName.textContent = data.full_name || "Profil étudiant";
  previewBio.textContent = data.bio || "Aucune bio renseignée pour le moment.";

  if (previewMeta) {
    const metaItems = [
      data.email ? `<span class="meta-item">${escapeHtml(data.email)}</span>` : "",
      data.phone ? `<span class="meta-item">${escapeHtml(data.phone)}</span>` : "",
      data.cv_url ? `<span class="meta-item">CV PDF disponible</span>` : ""
    ].filter(Boolean);

    previewMeta.innerHTML = metaItems.length ? metaItems.join("") : `<span class="meta-item">Informations minimales non disponibles</span>`;
  }

  if (previewSkills) {
    previewSkills.textContent = data.skills || "Aucune compétence affichée pour le moment.";
  }

  if (previewEducation) {
    previewEducation.textContent = data.education || "Aucune formation renseignée pour le moment.";
  }

  if (previewExperience) {
    previewExperience.textContent = data.experience || "Aucune expérience renseignée pour le moment.";
  }

  if (previewLanguages) {
    previewLanguages.textContent = data.languages || "Aucune langue renseignée pour le moment.";
  }

  if (publicLinkText) {
    publicLinkText.textContent = publicMode
      ? "Vous consultez actuellement la version publique du portfolio."
      : "Partagez ce lien pour permettre à d'autres personnes de consulter votre profil.";
  }

  if (publicLinkAnchor) {
    if (data.public_url) {
      publicLinkAnchor.innerHTML = `<a href="${escapeHtml(data.public_url)}" class="inline-link">${escapeHtml(data.public_url)}</a>`;
    } else if (data.cv_url) {
      publicLinkAnchor.innerHTML = `<a href="${escapeHtml(data.cv_url)}" class="inline-link" target="_blank" rel="noopener">Télécharger le CV PDF</a>`;
    } else {
      publicLinkAnchor.innerHTML = `<p class="small-text mb-0">Aucun lien public n'est encore disponible.</p>`;
    }
  }
}

function renderApplications(applications) {
  const body = document.getElementById("applications-body");

  if (!body) {
    return;
  }

  if (!applications.length) {
    body.innerHTML = `<tr><td colspan="4">Aucune candidature enregistrée pour le moment.</td></tr>`;
    return;
  }

  body.innerHTML = applications.map((application) => `
    <tr>
      <td>${escapeHtml(application.offer_title)}</td>
      <td>${escapeHtml(application.company)}</td>
      <td>${statusBadge(application.status)}</td>
      <td>${escapeHtml(formatDate(application.created_at))}</td>
    </tr>
  `).join("");
}

async function loadPortfolioPage() {
  const params = new URLSearchParams(window.location.search);
  const requestedUser = params.get("user");
  const accessNote = document.getElementById("portfolio-access-note");
  const formBox = document.getElementById("portfolio-form-box");
  const intro = document.getElementById("portfolio-page-intro");

  if (requestedUser) {
    try {
      const data = await fetchJSON(`${apiBase}/get_portfolio.php?user=${encodeURIComponent(requestedUser)}`);
      renderPortfolioPreview(data.portfolio, true);
      renderApplications([]);

      if (formBox) {
        formBox.classList.add("hidden");
      }

      if (intro) {
        intro.textContent = "Version publique du portfolio étudiant.";
      }
    } catch (error) {
      if (accessNote) {
        accessNote.className = "flash flash-error mb-4";
        accessNote.textContent = error.message;
      }
      if (formBox) {
        formBox.classList.add("hidden");
      }
    }
    return;
  }

  if (!state.session.authenticated) {
    if (accessNote) {
      accessNote.className = "flash flash-info mb-4";
      accessNote.textContent = "Connectez-vous pour enregistrer votre portfolio et suivre vos candidatures.";
    }

    if (formBox) {
      formBox.classList.add("hidden");
    }

    renderApplications([]);
    return;
  }

  try {
    const data = await fetchJSON(`${apiBase}/get_portfolio.php?current=1`);
    renderPortfolioPreview(data.portfolio);
    fillPortfolioForm(data.portfolio);
    renderApplications(data.applications || []);
  } catch (error) {
    if (accessNote) {
      accessNote.className = "flash flash-error mb-4";
      accessNote.textContent = error.message;
    }
  }
}

function renderDashboardStats(stats) {
  const target = document.getElementById("dashboard-stats");

  if (!target) {
    return;
  }

  target.innerHTML = `
    <div class="card">
      <div class="stat-value">${escapeHtml(stats.offers_count)}</div>
      <h3 class="card-title">Offres actives</h3>
      <p class="card-text">Nombre total d'offres actuellement publiées.</p>
    </div>
    <div class="card">
      <div class="stat-value">${escapeHtml(stats.applications_count)}</div>
      <h3 class="card-title">Candidatures</h3>
      <p class="card-text">Ensemble des demandes reçues via la plateforme.</p>
    </div>
    <div class="card">
      <div class="stat-value">${escapeHtml(stats.students_count)}</div>
      <h3 class="card-title">Étudiants inscrits</h3>
      <p class="card-text">Comptes étudiants créés dans l'application.</p>
    </div>
  `;
}

function renderDashboardOffers(offers) {
  const body = document.getElementById("dashboard-offers-body");

  if (!body) {
    return;
  }

  if (!offers.length) {
    body.innerHTML = `<tr><td colspan="6">Aucune offre publiée pour le moment.</td></tr>`;
    return;
  }

  body.innerHTML = offers.map((offer) => `
    <tr>
      <td>${escapeHtml(offer.title)}</td>
      <td>${escapeHtml(offer.company)}</td>
      <td>${escapeHtml(offer.location)}</td>
      <td>${escapeHtml(offer.duration)}</td>
      <td>${escapeHtml(formatDate(offer.created_at))}</td>
      <td>
        <div class="table-actions">
          <form action="${apiBase}/delete_offer.php" method="post" class="table-form">
            <input type="hidden" name="offer_id" value="${escapeHtml(offer.id)}">
            <button type="submit" class="btn btn-danger">Supprimer</button>
          </form>
          <button type="button" class="btn btn-outline" data-edit-offer-id="${escapeHtml(offer.id)}">Modifier</button>
        </div>
      </td>
    </tr>
    <tr class="hidden" id="offer-edit-row-${escapeHtml(offer.id)}">
      <td colspan="6">
        <form action="${apiBase}/update_offer.php" method="post" class="inline-edit-form">
          <input type="hidden" name="offer_id" value="${escapeHtml(offer.id)}">
          <div class="grid grid-2">
            <div class="form-group">
              <label class="form-label" for="edit-title-${escapeHtml(offer.id)}">Titre</label>
              <input class="form-control" id="edit-title-${escapeHtml(offer.id)}" name="title" value="${escapeHtml(offer.title)}" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="edit-company-${escapeHtml(offer.id)}">Entreprise</label>
              <input class="form-control" id="edit-company-${escapeHtml(offer.id)}" name="company" value="${escapeHtml(offer.company)}" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="edit-location-${escapeHtml(offer.id)}">Localisation</label>
              <input class="form-control" id="edit-location-${escapeHtml(offer.id)}" name="location" value="${escapeHtml(offer.location)}" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="edit-duration-${escapeHtml(offer.id)}">Duree</label>
              <input class="form-control" id="edit-duration-${escapeHtml(offer.id)}" name="duration" value="${escapeHtml(offer.duration)}" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="edit-description-${escapeHtml(offer.id)}">Description</label>
            <textarea class="form-control" id="edit-description-${escapeHtml(offer.id)}" name="description" required>${escapeHtml(offer.description)}</textarea>
          </div>
          <div class="table-actions">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <button type="button" class="btn btn-outline" data-cancel-edit-id="${escapeHtml(offer.id)}">Annuler</button>
          </div>
        </form>
      </td>
    </tr>
  `).join("");

  body.querySelectorAll("[data-edit-offer-id]").forEach((button) => {
    button.addEventListener("click", () => {
      const row = document.getElementById(`offer-edit-row-${button.dataset.editOfferId}`);
      if (row) {
        row.classList.toggle("hidden");
      }
    });
  });

  body.querySelectorAll("[data-cancel-edit-id]").forEach((button) => {
    button.addEventListener("click", () => {
      const row = document.getElementById(`offer-edit-row-${button.dataset.cancelEditId}`);
      if (row) {
        row.classList.add("hidden");
      }
    });
  });
}

function buildApplicationStatusForm(applicationId, status, label, buttonClass) {
  return `
    <form action="${apiBase}/update_application_status.php" method="post" class="table-form">
      <input type="hidden" name="application_id" value="${escapeHtml(applicationId)}">
      <input type="hidden" name="status" value="${escapeHtml(status)}">
      <button type="submit" class="btn ${escapeHtml(buttonClass)}">${escapeHtml(label)}</button>
    </form>
  `;
}

function renderDashboardApplications(applications) {
  const body = document.getElementById("dashboard-applications-body");

  if (!body) {
    return;
  }

  if (!applications.length) {
    body.innerHTML = `<tr><td colspan="5">Aucune candidature enregistrée pour le moment.</td></tr>`;
    return;
  }

  body.innerHTML = applications.map((application) => `
    <tr>
      <td>
        <strong>${escapeHtml(application.student_name)}</strong><br>
        <span class="small-text">${escapeHtml(application.student_email)}</span>
      </td>
      <td>
        <strong>${escapeHtml(application.offer_title)}</strong><br>
        <span class="small-text">${escapeHtml(application.company)}</span>
      </td>
      <td>${statusBadge(application.status)}</td>
      <td>${escapeHtml(formatDate(application.created_at))}</td>
      <td>
        <div class="table-actions">
          ${application.cv_url
            ? `<a href="${escapeHtml(application.cv_url)}" class="btn btn-outline" target="_blank" rel="noopener">Voir le CV</a>`
            : `<span class="small-text">CV non disponible</span>`}
          ${buildApplicationStatusForm(application.id, "accepted", "Accepter", "btn-success")}
          ${buildApplicationStatusForm(application.id, "rejected", "Refuser", "btn-danger")}
          ${application.status !== "pending" ? buildApplicationStatusForm(application.id, "pending", "Remettre en attente", "btn-outline") : ""}
        </div>
      </td>
    </tr>
  `).join("");
}

async function loadDashboardPage() {
  const guard = document.getElementById("dashboard-guard");
  const content = document.getElementById("dashboard-content");

  if (!state.session.authenticated || state.session.role !== "admin") {
    if (guard) {
      guard.classList.remove("hidden");
    }
    if (content) {
      content.classList.add("hidden");
    }
    return;
  }

  if (guard) {
    guard.classList.add("hidden");
  }
  if (content) {
    content.classList.remove("hidden");
  }

  try {
    const data = await fetchJSON(`${apiBase}/dashboard_data.php`);
    renderDashboardStats(data.stats || {});
    renderDashboardOffers(data.offers || []);
    renderDashboardApplications(data.applications || []);
  } catch (error) {
    const flashContainer = document.getElementById("flash-container");
    if (flashContainer) {
      flashContainer.innerHTML = buildFlash("error", error.message);
    }
  }
}

async function initPage() {
  displayQueryFlash();
  await loadSession();
  updateNavigation();

  const page = document.body.dataset.page;

  if (page === "accueil") {
    await loadOffers(3);
    renderHomeOffers();
  }

  if (page === "offres") {
    await loadOffers();
    attachOfferFilters();
    applyOfferFilters();
  }

  if (page === "portfolio") {
    await loadPortfolioPage();
  }

  if (page === "dashboard") {
    await loadDashboardPage();
  }
}

document.addEventListener("DOMContentLoaded", initPage);
