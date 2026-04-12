
document.addEventListener('DOMContentLoaded', () => {
  initToggleMdp();
  initFormConnexion();
});

function initToggleMdp() {
  document.querySelectorAll('.btn-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const id    = btn.dataset.toggle;
      const champ = document.getElementById(id);
      if (!champ) return;
      champ.type      = champ.type === 'password' ? 'text' : 'password';
      btn.textContent = champ.type === 'password' ? '👁' : '🙈';
    });
  });
}

function showErr(id, msg) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('erreur'); el.classList.remove('valide');
  const g = el.closest('.form-groupe');
  g.querySelector('.msg-erreur')?.remove();
  const p = document.createElement('p'); p.className = 'msg-erreur'; p.textContent = '⚠ ' + msg;
  g.appendChild(p);
}
function showOk(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('erreur'); el.classList.add('valide');
  el.closest('.form-groupe')?.querySelector('.msg-erreur')?.remove();
}
function clearState(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('erreur','valide');
  el.closest('.form-groupe')?.querySelector('.msg-erreur')?.remove();
}

function initFormConnexion() {
  const form = document.getElementById('form-connexion');
  if (!form) return;

  ['email','mdp'].forEach(id => document.getElementById(id)?.addEventListener('input', () => clearState(id)));

  form.addEventListener('submit', e => {
    e.preventDefault();
    let ok = true;

    const email = document.getElementById('email')?.value.trim();
    const mdp   = document.getElementById('mdp')?.value;

    if (!email) { showErr('email', "L'email est obligatoire."); ok = false; }
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showErr('email', 'Format invalide.'); ok = false; }
    else showOk('email');

    if (!mdp) { showErr('mdp', 'Le mot de passe est obligatoire.'); ok = false; }
    else showOk('mdp');

    if (!ok) return;

    const comptes = {
      'admin@stageconnect.tn': { role:'admin',    nom:'Administrateur', mdp:'admin123' },
      'mouheb@email.tn':       { role:'etudiant', nom:'Hajjej Mouheb',  mdp:'Student123' },
      'jesser@email.tn':       { role:'etudiant', nom:'Abbessi Jesser', mdp:'Student123' },
    };

    const compte = comptes[email];
    if (compte && mdp === compte.mdp) {
      sessionStorage.setItem('user', JSON.stringify({ nom: compte.nom, role: compte.role }));
      const dest = compte.role === 'admin' ? '../dashboard/dashboard.html' : '../offres/offres.html';
      window.location.href = dest;
    } else {
      const alerteEl = document.getElementById('alerte-erreur');
      if (alerteEl) alerteEl.style.display = 'flex';
      showErr('email', ''); showErr('mdp', '');
    }
  });
}
