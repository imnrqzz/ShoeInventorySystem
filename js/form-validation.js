/**
 * form-validation.js — Shared Client-Side Form Validation
 *
 * Best Practice: This script provides automatic validation for any form
 * that has the "data-validate" attribute. It reads standard HTML5 validation
 * attributes (required, minlength, min, max, type="email", pattern) from
 * each input and shows styled inline error messages.
 *
 * How it works:
 * 1. On page load, finds all <form data-validate> elements
 * 2. For each form, finds all <input>, <select>, <textarea> with validation attributes
 * 3. Attaches "blur" listeners for real-time feedback as user fills in fields
 * 4. On "input", clears errors so user sees immediate feedback as they type
 * 5. On form "submit", validates all fields and prevents submission if invalid
 *
 * To use: Add data-validate to your <form>, add novalidate to disable browser
 * default popups, and put a <span class="field-error"></span> after each input
 * that needs validation.
 *
 * Example:
 *   <form data-validate novalidate>
 *     <div class="form-group">
 *       <label>Name *</label>
 *       <input type="text" name="name" required minlength="2">
 *       <span class="field-error"></span>
 *     </div>
 *   </form>
 */

(function() {
    'use strict';

    // ── Wait for DOM to be ready ────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        // Find all forms marked for validation
        var forms = document.querySelectorAll('form[data-validate]');

        forms.forEach(function(form) {
            initFormValidation(form);
        });
    });

    /**
     * Initialize validation for a single form.
     * Finds all validatable inputs and attaches event listeners.
     */
    function initFormValidation(form) {
        // Get all inputs/selects/textareas that have validation-related attributes
        var fields = form.querySelectorAll('input, select, textarea');

        fields.forEach(function(field) {
            // Skip hidden inputs — they don't need user-facing validation
            if (field.type === 'hidden') return;

            // Best Practice: Validate on "blur" (when user leaves the field).
            // This gives feedback at the right moment — not while typing, but
            // after they've finished entering a value and moved on.
            field.addEventListener('blur', function() {
                validateField(field);
            });

            // Clear error styling as user types (immediate positive feedback)
            field.addEventListener('input', function() {
                var errorEl = getErrorElement(field);
                if (errorEl && field.value.trim() !== '') {
                    clearError(field, errorEl);
                }
            });
        });

        // Validate entire form on submit
        form.addEventListener('submit', function(e) {
            var allValid = true;

            fields.forEach(function(field) {
                if (field.type === 'hidden') return;
                if (!validateField(field)) {
                    allValid = false;
                }
            });

            // Best Practice: preventDefault() stops the form from submitting
            // when validation fails. The data never leaves the browser.
            if (!allValid) {
                e.preventDefault();

                // Scroll to and focus the first invalid field so user sees it
                var firstError = form.querySelector('.input-error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }

    /**
     * Validate a single field based on its HTML attributes.
     * Returns true if valid, false if invalid.
     *
     * Best Practice: We read standard HTML5 attributes (required, minlength, min, etc.)
     * so the validation rules are defined in HTML, not duplicated in JavaScript.
     * This keeps the HTML as the single source of truth for what's required.
     */
    function validateField(field) {
        var value = field.value.trim();
        var errorEl = getErrorElement(field);

        // If there's no error span, we can't show messages — skip validation
        if (!errorEl) return true;

        // Get the label text for user-friendly error messages
        var label = getFieldLabel(field);

        // ── Required Check ──────────────────────────────────
        if (field.hasAttribute('required') && value === '') {
            showError(field, errorEl, label + ' is required.');
            return false;
        }

        // If field is empty and not required, skip further checks
        if (value === '') {
            clearError(field, errorEl);
            return true;
        }

        // ── MinLength Check ─────────────────────────────────
        var minLen = field.getAttribute('minlength');
        if (minLen && value.length < parseInt(minLen)) {
            showError(field, errorEl, label + ' must be at least ' + minLen + ' characters.');
            return false;
        }

        // ── MaxLength Check ─────────────────────────────────
        var maxLen = field.getAttribute('maxlength');
        if (maxLen && value.length > parseInt(maxLen)) {
            showError(field, errorEl, label + ' must be no more than ' + maxLen + ' characters.');
            return false;
        }

        // ── Email Format Check ──────────────────────────────
        // Best Practice: Client-side email regex is kept simple on purpose.
        // The real email validation happens server-side with PHP's filter_var().
        if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            showError(field, errorEl, 'Please enter a valid email address.');
            return false;
        }

        // ── Number Range Checks (min/max) ───────────────────
        if (field.type === 'number') {
            var numVal = parseFloat(value);

            if (isNaN(numVal)) {
                showError(field, errorEl, label + ' must be a valid number.');
                return false;
            }

            var minVal = field.getAttribute('min');
            if (minVal !== null && numVal < parseFloat(minVal)) {
                showError(field, errorEl, label + ' must be at least ' + minVal + '.');
                return false;
            }

            var maxVal = field.getAttribute('max');
            if (maxVal !== null && numVal > parseFloat(maxVal)) {
                showError(field, errorEl, label + ' must be no more than ' + maxVal + '.');
                return false;
            }

            // Check step="1" means integers only
            var step = field.getAttribute('step');
            if (step === '1' && numVal !== Math.floor(numVal)) {
                showError(field, errorEl, label + ' must be a whole number.');
                return false;
            }
        }

        // ── Pattern Check ───────────────────────────────────
        var pattern = field.getAttribute('pattern');
        if (pattern) {
            var regex = new RegExp('^' + pattern + '$');
            if (!regex.test(value)) {
                // Use the title attribute as the error message if provided
                var patternMsg = field.getAttribute('title') || label + ' format is invalid.';
                showError(field, errorEl, patternMsg);
                return false;
            }
        }

        // All checks passed
        clearError(field, errorEl);
        return true;
    }

    // ── Helper Functions ────────────────────────────────────

    /**
     * Find the .field-error span associated with a field.
     * Looks for the next sibling with the class, or within the parent .form-group.
     */
    function getErrorElement(field) {
        // First try: next sibling element
        var next = field.nextElementSibling;
        if (next && next.classList.contains('field-error')) return next;

        // Second try: within the parent .form-group
        var group = field.closest('.form-group');
        if (group) return group.querySelector('.field-error');

        return null;
    }

    /**
     * Get a human-readable label for the field.
     * Reads from the <label> element in the same .form-group.
     */
    function getFieldLabel(field) {
        var group = field.closest('.form-group');
        if (group) {
            var label = group.querySelector('label');
            if (label) {
                // Remove trailing asterisks and whitespace from label text
                return label.textContent.replace(/\s*\*\s*$/, '').trim();
            }
        }
        // Fallback: use the input's name attribute, formatted nicely
        return field.name ? field.name.replace(/[_-]/g, ' ') : 'This field';
    }

    function showError(field, errorEl, message) {
        field.classList.add('input-error');
        errorEl.textContent = message;
    }

    function clearError(field, errorEl) {
        field.classList.remove('input-error');
        errorEl.textContent = '';
    }
})();
