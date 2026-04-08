/**
 * StageConnect — Main JavaScript
 * UI Interactions: navbar, filter, modals, animations
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── Mobile Navigation ──────────────────────────────────────
    const hamburger = document.querySelector('.hamburger');
    const navMenu   = document.querySelector('.navbar-nav');
    const navActions = document.querySelector('.navbar-actions');

    if (hamburger) {
        hamburger.addEventListener('click', () => {
            navMenu?.classList.toggle('open');
            navActions?.classList.toggle('open');
            hamburger.setAttribute('aria-expanded',
                navMenu?.classList.contains('open') ? 'true' : 'false'
            );
        });
    }

    // Close mobile menu when a link is clicked
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            navMenu?.classList.remove('open');
            navActions?.classList.remove('open');
        });
    });

    // ── Active Nav Link ────────────────────────────────────────
    const currentPath = window.location.pathname.split('/').pop();
    document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
        const href = link.getAttribute('href')?.split('/').pop();
        if (href && currentPath.includes(href.replace(/\.php|\.html/, ''))) {
            link.classList.add('active');
        }
    });

    // ── Offers Filter ──────────────────────────────────────────
    const searchInput  = document.getElementById('search-offers');
    const domainFilter = document.getElementById('filter-domain');
    const offerCards   = document.querySelectorAll('.offer-card-wrapper');
    const emptyState   = document.getElementById('empty-state');

    function filterOffers() {
        const searchTerm = searchInput?.value.toLowerCase().trim() ?? '';
        const domain     = domainFilter?.value.toLowerCase().trim() ?? '';
        let visibleCount = 0;

        offerCards.forEach(card => {
            const title   = card.dataset.title?.toLowerCase()   ?? '';
            const company = card.dataset.company?.toLowerCase() ?? '';
            const dom     = card.dataset.domain?.toLowerCase()  ?? '';

            const matchesSearch = !searchTerm ||
                title.includes(searchTerm) || company.includes(searchTerm);
            const matchesDomain = !domain || dom.includes(domain);

            if (matchesSearch && matchesDomain) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (searchInput)  searchInput.addEventListener('input', filterOffers);
    if (domainFilter) domainFilter.addEventListener('change', filterOffers);

    // ── Modal System ───────────────────────────────────────────
    window.openModal = function(modalId) {
        const overlay = document.getElementById(modalId);
        if (overlay) {
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function(modalId) {
        const overlay = document.getElementById(modalId);
        if (overlay) {
            overlay.classList.remove('open');
            document.body.style.overflow = '';
        }
    };

    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            }
        });
    });

    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.open').forEach(overlay => {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            });
        }
    });

    // ── Scroll Animations ──────────────────────────────────────
    const animateOnScroll = () => {
        const elements = document.querySelectorAll('.animate-on-scroll');
        elements.forEach(el => {
            const rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight - 80) {
                el.classList.add('animate-fade-up');
                el.classList.remove('animate-on-scroll');
            }
        });
    };

    window.addEventListener('scroll', animateOnScroll, { passive: true });
    animateOnScroll(); // Run once on load

    // ── Confirm Delete ─────────────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const message = btn.dataset.confirm || 'Êtes-vous sûr de vouloir supprimer cet élément ?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // ── Counter Animations (stats) ─────────────────────────────
    const counters = document.querySelectorAll('.stat-number[data-target]');
    if (counters.length > 0) {
        const animateCounters = () => {
            counters.forEach(counter => {
                const rect = counter.getBoundingClientRect();
                if (rect.top < window.innerHeight && !counter.classList.contains('counted')) {
                    counter.classList.add('counted');
                    const target = parseInt(counter.dataset.target);
                    let current = 0;
                    const step  = Math.ceil(target / 40);
                    const timer = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        counter.textContent = current.toLocaleString('fr');
                    }, 30);
                }
            });
        };
        window.addEventListener('scroll', animateCounters, { passive: true });
        animateCounters();
    }

    // ── Auto-dismiss alerts ────────────────────────────────────
    const alerts = document.querySelectorAll('.alert[data-auto-dismiss]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity .5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

    // ── Admin: Pre-fill edit modal ─────────────────────────────
    document.querySelectorAll('[data-edit-offer]').forEach(btn => {
        btn.addEventListener('click', () => {
            const data = btn.dataset;
            ['edit_id', 'edit_title', 'edit_description',
             'edit_company', 'edit_location', 'edit_domain', 'edit_duration']
            .forEach(key => {
                const field = document.getElementById(key);
                if (field) field.value = data[key] || '';
            });
            openModal('edit-modal');
        });
    });

    // ── Show password toggle ───────────────────────────────────
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.togglePassword;
            const field    = document.getElementById(targetId);
            if (!field) return;
            const isText   = field.type === 'text';
            field.type     = isText ? 'password' : 'text';
            btn.textContent = isText ? '👁' : '🙈';
        });
    });

});
