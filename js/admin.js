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
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
}

// ============================================================
// Specialty field toggle (show/hide for doctor users)
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    const userTypeSelect = document.getElementById('user_type');
    const specialtyGroup = document.getElementById('specialtyGroup');
    if (userTypeSelect && specialtyGroup) {
        function toggleSpecialty() {
            const val = userTypeSelect.value;
            specialtyGroup.style.display = (val === 'doctor' || val === 'medic') ? '' : 'none';
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
    const requiredFields = form.querySelectorAll('[required]');
    let valid = true;
    requiredFields.forEach(function (field) {
        field.style.borderColor = '';
        if (!field.value.trim()) {
            field.style.borderColor = '#ff6363';
            valid = false;
        }
    });

    // Email validation
    const emailField = form.querySelector('input[type="email"]');
    if (emailField && emailField.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value)) {
        emailField.style.borderColor = '#ff6363';
        valid = false;
    }

    // Password match
    const pass = form.querySelector('input[name="password"]');
    const passConfirm = form.querySelector('input[name="password_confirm"]');
    if (pass && passConfirm && pass.value && passConfirm.value && pass.value !== passConfirm.value) {
        passConfirm.style.borderColor = '#ff6363';
        valid = false;
    }

    return valid;
}
