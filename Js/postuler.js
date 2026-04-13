
document.addEventListener('DOMContentLoaded', () => {
  initDragDrop();
  initFormCandidature();
});

/* ── Drag & Drop + clic sur la zone ── */
function initDragDrop() {
  const zone  = document.getElementById('zone-depot');
  const input = document.getElementById('cv');
  if (!zone || !input) return;

  // Clic ouvre le sélecteur de fichier
  zone.addEventListener('click', () => input.click());

  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('survol'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('survol'));
  zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('survol');
    if (e.dataTransfer.files.length > 0) {
      const dt = new DataTransfer();
      dt.items.add(e.dataTransfer.files[0]);
      input.files = dt.files;
      afficherApercu(input);
    }
  });

  input.addEventListener('change', () => afficherApercu(input));
}

function afficherApercu(input) {
  const apercu = document.getElementById('apercu-cv');
  if (!apercu || !input.files.length) return;
  const f = input.files[0];
  apercu.innerHTML = `✅ <strong>${f.name}</strong> (${(f.size/1024).toFixed(1)} Ko)`;
  apercu.style.display = 'flex';
}

/* ── Validation et soumission ── */
function initFormCandidature() {
  const form = document.getElementById('form-candidature');
  if (!form) return;

  form.addEventListener('submit', e => {
    e.preventDefault();
    const cv = document.getElementById('cv');
    const apercu = document.getElementById('apercu-cv');
    let valid = true;

    if (!cv || cv.files.length === 0) {
      erreur('cv', 'Veuillez joindre votre CV (PDF ou Word).');
      valid = false;
    } else {
      const f = cv.files[0];
      const types = ['application/pdf','application/msword',
                     'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
      if (!types.includes(f.type)) { erreur('cv', 'Format non accepté. Utilisez PDF ou Word.'); valid = false; }
      else if (f.size > 5*1024*1024) { erreur('cv', 'Fichier trop lourd (max 5 Mo).'); valid = false; }
      else { succes('cv'); }
    }

    if (!valid) return;

    /* Afficher un message de succès */
    const msg = document.getElementById('msg-succes');
    if (msg) { msg.style.display = 'flex'; form.style.display = 'none'; }
  });
}

function erreur(id, msg) {
  const el = document.getElementById(id); if (!el) return;
  el.classList.add('erreur');
  el.closest('.form-groupe')?.querySelector('.msg-erreur')?.remove();
  const p = document.createElement('p'); p.className = 'msg-erreur'; p.textContent = '⚠ ' + msg;
  el.closest('.form-groupe')?.appendChild(p);
}
function succes(id) {
  const el = document.getElementById(id); if (!el) return;
  el.classList.remove('erreur'); el.classList.add('valide');
  el.closest('.form-groupe')?.querySelector('.msg-erreur')?.remove();
}
