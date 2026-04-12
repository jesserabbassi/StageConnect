
document.addEventListener('DOMContentLoaded', () => {
  initEyeToggle();
  initStrengthMeter();
  initRegisterForm();
});

function initEyeToggle() {
  document.querySelectorAll('[data-eye]').forEach(btn => {
    btn.addEventListener('click', () => {
      const t = document.getElementById(btn.dataset.eye);
      if (!t) return;
      t.type = t.type === 'password' ? 'text' : 'password';
      btn.textContent = t.type === 'password' ? '👁' : '🙈';
    });
  });
}

function initStrengthMeter() {
  const pwd = document.getElementById('password');
  if (!pwd) return;
  pwd.addEventListener('input', () => updateStrength(pwd.value));
}

function updateStrength(val) {
  const bars = document.querySelectorAll('.strength-bar');
  const txt  = document.querySelector('.strength-text');
  let score = 0;
  if (val.length >= 8)           score++;
  if (/[A-Z]/.test(val))        score++;
  if (/[0-9]/.test(val))        score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const levels  = ['','Faible','Moyen','Fort','Très fort'];
  const colours = ['','#dc2626','#f59e0b','#16a34a','#2563eb'];
  bars.forEach((b, i) => b.style.background = i < score ? colours[score] : '#dbe3ee');
  if (txt) { txt.textContent = val ? 'Force : ' + levels[score] : ''; txt.style.color = colours[score]; }
}

function setError(id, msg) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('is-error'); el.classList.remove('is-valid');
  let e = el.closest('.form-group').querySelector('.field-error');
  if (!e) { e = document.createElement('p'); e.className='field-error'; el.closest('.form-group').appendChild(e); }
  e.textContent = '⚠ ' + msg;
}
function setOk(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('is-error'); el.classList.add('is-valid');
  el.closest('.form-group').querySelector('.field-error')?.remove();
}
function clearField(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('is-error','is-valid');
  el.closest('.form-group').querySelector('.field-error')?.remove();
}

function initRegisterForm() {
  const form = document.getElementById('register-form');
  if (!form) return;

  ['name','email','password','password2'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', () => clearField(id));
  });

  form.addEventListener('submit', e => {
    e.preventDefault();
    let valid = true;

    const name  = document.getElementById('name')?.value.trim();
    const email = document.getElementById('email')?.value.trim();
    const pwd   = document.getElementById('password')?.value;
    const pwd2  = document.getElementById('password2')?.value;

    if (!name || name.length < 3)                { setError('name', 'Nom obligatoire (min 3 caractères).'); valid = false; } else setOk('name');
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setError('email','Email invalide.'); valid = false; } else setOk('email');
    if (!pwd || pwd.length < 8)                  { setError('password','Minimum 8 caractères.'); valid = false; } else setOk('password');
    if (pwd2 !== pwd)                            { setError('password2','Les mots de passe ne correspondent pas.'); valid = false; } else if(pwd2) setOk('password2');

    if (!valid) return;

    sessionStorage.setItem('sc_user', JSON.stringify({ name, role: 'student' }));
    window.location.href = '../offres/offres.html';
  });
}
