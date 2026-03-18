/**
 * MediTrust - Admin Panel JavaScript
 */

'use strict';

// ============================================================
// CSRF Token helper
// ============================================================
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

// ============================================================
// Modal Management
// ============================================================
function openModal(id) {
    const overlay = document.getElementById(id);
    if (overlay) {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const overlay = document.getElementById(id);
    if (overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close modal on overlay click
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function (overlay) {
            overlay.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
});

// ============================================================
// Delete Confirmation
// ============================================================
let pendingDeleteForm = null;

function confirmDelete(formId, name) {
    pendingDeleteForm = document.getElementById(formId);
    const nameEl = document.getElementById('deleteTargetName');
    if (nameEl) {
        nameEl.textContent = name || 'acest element';
    }
    openModal('confirmDeleteModal');
}

document.addEventListener('DOMContentLoaded', function () {
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (pendingDeleteForm) {
                pendingDeleteForm.submit();
                pendingDeleteForm = null;
            }
            closeModal('confirmDeleteModal');
        });
    }
});

// ============================================================
// Alert auto-dismiss
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert[data-auto-dismiss]');
    alerts.forEach(function (alert) {
        const delay = parseInt(alert.getAttribute('data-auto-dismiss')) || 4000;
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function () {
                alert.remove();
            }, 500);
        }, delay);
    });
});

// ============================================================
// Table search (client-side quick filter)
// ============================================================
function initTableSearch(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;

    input.addEventListener('input', function () {
        const filter = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(function (row) {
            const text = row.textContent.toLowerCase();
            const isVisible = text.includes(filter);
            row.style.display = isVisible ? '' : 'none';
        });
    });
}

// ============================================================
// Specialty field toggle (show/hide for doctor users)
// Works only if page contains #specialtyGroup
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    const userTypeSelect = document.getElementById('user_type');
    const specialtyGroup = document.getElementById('specialtyGroup');
    const specialtySelect = document.getElementById('specialty_id');

    if (userTypeSelect && specialtyGroup) {
        function toggleSpecialty() {
            const val = userTypeSelect.value;
            const isDoctor = (val === 'doctor');

            specialtyGroup.style.display = isDoctor ? '' : 'none';

            if (specialtySelect) {
                specialtySelect.required = isDoctor;
            }
        }

        userTypeSelect.addEventListener('change', toggleSpecialty);
        toggleSpecialty();
    }
});

// ============================================================
// Form validation helpers
// ============================================================
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    form.querySelectorAll('[required]').forEach(function (field) {
        field.style.borderColor = '';
    });

    let valid = true;
    const errors = [];

    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(function (field) {
        if (!field.value || (field.type === 'text' && !field.value.trim())) {
            field.style.borderColor = '#ff6363';
            valid = false;
            errors.push('⚠️ ' + (field.placeholder || field.name) + ' este obligatoriu!');
        }
    });

    const emailField = form.querySelector('input[type="email"]');
    if (emailField && emailField.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailField.value)) {
            emailField.style.borderColor = '#ff6363';
            valid = false;
            errors.push('❌ Email invalid!');
        }
    }

    const pass = form.querySelector('input[name="password"]');
    const passConfirm = form.querySelector('input[name="password_confirm"]');

    if (pass && pass.value) {
        if (pass.value.length < 6) {
            pass.style.borderColor = '#ff6363';
            valid = false;
            errors.push('❌ Parola trebuie să aibă minim 6 caractere!');
        }

        if (passConfirm && passConfirm.value) {
            if (pass.value !== passConfirm.value) {
                passConfirm.style.borderColor = '#ff6363';
                valid = false;
                errors.push('❌ Parolele nu se potrivesc!');
            }
        }
    }

    if (!valid && errors.length > 0) {
        alert(errors.join('\n'));
    }

    return valid;
}

// ============================================================
// Password strength indicator (optional)
// ============================================================
function checkPasswordStrength(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    input.addEventListener('input', function () {
        const pass = this.value;
        let strength = 0;

        if (pass.length >= 6) strength++;
        if (pass.length >= 10) strength++;
        if (/[a-z]/.test(pass) && /[A-Z]/.test(pass)) strength++;
        if (/[0-9]/.test(pass)) strength++;
        if (/[^a-zA-Z0-9]/.test(pass)) strength++;

        const indicator = document.getElementById(inputId + '_strength');
        if (indicator) {
            indicator.className = 'password-strength strength-' + strength;
            const labels = ['Foarte slabă', 'Slabă', 'Acceptabilă', 'Bună', 'Foarte bună'];
            indicator.textContent = labels[strength - 1] || '';
        }
    });
}

// ============================================================
// Disable button on valid form submit (prevent double-click)
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form');
    forms.forEach(function (form) {
        form.addEventListener('submit', function () {
            if (!form.checkValidity()) return;

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.6';
                submitBtn.style.cursor = 'not-allowed';
            }
        });
    });
});