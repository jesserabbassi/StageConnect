/**
 * StageConnect — Form Validation (Client-Side)
 * Handles real-time validation and error display
 */

/**
 * Show an error message under a field
 */
function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    field.classList.add('error');
    field.classList.remove('success');

    // Remove existing error message
    const existing = field.parentElement.querySelector('.form-error');
    if (existing) existing.remove();

    // Create and append new error
    const errorEl = document.createElement('p');
    errorEl.className = 'form-error';
    errorEl.innerHTML = `⚠ ${message}`;
    field.parentElement.appendChild(errorEl);
}

/**
 * Show a success state on a field
 */
function showSuccess(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    field.classList.remove('error');
    field.classList.add('success');

    const existing = field.parentElement.querySelector('.form-error');
    if (existing) existing.remove();
}

/**
 * Clear all errors on a field
 */
function clearError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    field.classList.remove('error', 'success');
    const existing = field.parentElement.querySelector('.form-error');
    if (existing) existing.remove();
}

/**
 * Validate the registration form
 * Returns true if valid, false otherwise
 */
function validateRegisterForm() {
    let isValid = true;

    // Validate name
    const name = document.getElementById('name')?.value.trim();
    if (!name) {
        showError('name', 'Le nom complet est obligatoire.');
        isValid = false;
    } else if (name.length < 3) {
        showError('name', 'Le nom doit contenir au moins 3 caractères.');
        isValid = false;
    } else {
        showSuccess('name');
    }

    // Validate email
    const email = document.getElementById('email')?.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email) {
        showError('email', 'L\'adresse email est obligatoire.');
        isValid = false;
    } else if (!emailRegex.test(email)) {
        showError('email', 'Veuillez entrer une adresse email valide.');
        isValid = false;
    } else {
        showSuccess('email');
    }

    // Validate password
    const password = document.getElementById('password')?.value;
    if (!password) {
        showError('password', 'Le mot de passe est obligatoire.');
        isValid = false;
    } else if (password.length < 8) {
        showError('password', 'Le mot de passe doit contenir au moins 8 caractères.');
        isValid = false;
    } else {
        showSuccess('password');
    }

    // Validate password confirmation
    const confirmPassword = document.getElementById('confirm_password')?.value;
    if (confirmPassword !== undefined) {
        if (!confirmPassword) {
            showError('confirm_password', 'Veuillez confirmer votre mot de passe.');
            isValid = false;
        } else if (confirmPassword !== password) {
            showError('confirm_password', 'Les mots de passe ne correspondent pas.');
            isValid = false;
        } else {
            showSuccess('confirm_password');
        }
    }

    return isValid;
}

/**
 * Validate the login form
 */
function validateLoginForm() {
    let isValid = true;

    const email = document.getElementById('email')?.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email) {
        showError('email', 'L\'adresse email est obligatoire.');
        isValid = false;
    } else if (!emailRegex.test(email)) {
        showError('email', 'Format d\'email invalide.');
        isValid = false;
    } else {
        showSuccess('email');
    }

    const password = document.getElementById('password')?.value;
    if (!password) {
        showError('password', 'Le mot de passe est obligatoire.');
        isValid = false;
    } else {
        showSuccess('password');
    }

    return isValid;
}

/**
 * Validate the application (postuler) form
 */
function validateApplyForm() {
    let isValid = true;

    const cv = document.getElementById('cv');
    if (cv && cv.files.length === 0) {
        showError('cv', 'Veuillez télécharger votre CV (PDF ou Word).');
        isValid = false;
    } else if (cv && cv.files.length > 0) {
        const file = cv.files[0];
        const allowedTypes = ['application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!allowedTypes.includes(file.type)) {
            showError('cv', 'Seuls les fichiers PDF et Word (.doc, .docx) sont acceptés.');
            isValid = false;
        } else if (file.size > 5 * 1024 * 1024) {
            showError('cv', 'Le fichier ne doit pas dépasser 5 Mo.');
            isValid = false;
        } else {
            showSuccess('cv');
        }
    }

    return isValid;
}

/**
 * Real-time password strength indicator
 */
function updatePasswordStrength(password) {
    const strengthEl = document.getElementById('password-strength');
    if (!strengthEl) return;

    let strength = 0;
    if (password.length >= 8)   strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    const labels = ['', 'Faible', 'Moyen', 'Fort', 'Très fort'];
    const colors = ['', '#EF4444', '#F59E0B', '#10B981', '#1B4FD8'];

    strengthEl.textContent = password ? `Force: ${labels[strength]}` : '';
    strengthEl.style.color = colors[strength];
}

// ── Auto-attach validators on DOM load ───────────────────────────
document.addEventListener('DOMContentLoaded', () => {

    // Real-time password strength
    const passwordField = document.getElementById('password');
    if (passwordField) {
        passwordField.addEventListener('input', () => {
            updatePasswordStrength(passwordField.value);
        });
    }

    // Clear error on input
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('input', () => {
            clearError(input.id);
        });
    });

    // Attach to register form
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            if (!validateRegisterForm()) {
                e.preventDefault();
            }
        });
    }

    // Attach to login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            if (!validateLoginForm()) {
                e.preventDefault();
            }
        });
    }

    // Attach to apply form
    const applyForm = document.getElementById('apply-form');
    if (applyForm) {
        applyForm.addEventListener('submit', (e) => {
            if (!validateApplyForm()) {
                e.preventDefault();
            }
        });
    }

    // CV file preview
    const cvInput = document.getElementById('cv');
    if (cvInput) {
        cvInput.addEventListener('change', () => {
            const preview = document.getElementById('cv-preview');
            if (preview && cvInput.files.length > 0) {
                const file = cvInput.files[0];
                const size = (file.size / 1024).toFixed(1);
                preview.innerHTML = `✅ <strong>${file.name}</strong> (${size} Ko)`;
                preview.style.display = 'block';
            }
        });
    }
});
