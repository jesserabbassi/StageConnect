/* =====================================================
   StageConnect — Page Accueil
   Fichier : index.js
   ===================================================== */

document.addEventListener('DOMContentLoaded', () => {
  initNavMobile();
  initScrollReveal();
  initCounters();
});

/* ── Hamburger (mobile) ── */
function initNavMobile() {
  const btn   = document.getElementById('burger');
  const nav   = document.getElementById('nav-menu');
  if (!btn || !nav) return;
  btn.addEventListener('click', () => nav.classList.toggle('open'));
  document.addEventListener('click', e => {
    if (!e.target.closest('.site-header')) nav.classList.remove('open');
  });
}

/* ── Scroll reveal ── */
function initScrollReveal() {
  const els = document.querySelectorAll('.scroll-reveal');
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); } });
  }, { threshold: 0.12 });
  els.forEach(el => obs.observe(el));
}

