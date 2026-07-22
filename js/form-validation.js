/**
 * form-validation.js - Shared Client-Side Form Validation
 *
 * Provides automatic validation for any form with the "data-validate" attribute.
 *
 * Two modes of operation:
 * 1. HTML-attribute mode (default): reads required, minlength, min, max, type, pattern from the DOM.
 * 2. Shared-rules mode: if the form has data-form-name="X", looks up rules from
 *    window.ValidationRules[X] (loaded from validation-rules.js).
 *    Shared rules take priority over HTML attributes when both are present.
 *
 * To use: Add data-validate and novalidate to your <form>, and put a
 * <span class="field-error"></span> after each input that needs validation.
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        var forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(function(form) {
            initFormValidation(form);
        });
    });

    function initFormValidation(form) {
        var formName = form.getAttribute('data-form-name');
        var sharedRules = (formName && window.ValidationRules) ? window.ValidationRules[formName] : null;
        var fields = form.querySelectorAll('input, select, textarea');

        fields.forEach(function(field) {
            if (field.type === 'hidden') return;

            field.addEventListener('blur', function() {
                validateField(field, sharedRules);
            });

            field.addEventListener('input', function() {
                var errorEl = getErrorElement(field);
                if (errorEl && field.value.trim() !== '') {
                    clearError(field, errorEl);
                }
            });
        });

        form.addEventListener('submit', function(e) {
            var allValid = true;

            fields.forEach(function(field) {
                if (field.type === 'hidden') return;
                if (!validateField(field, sharedRules)) {
                    allValid = false;
                }
            });

            if (!allValid) {
                e.preventDefault();
                var firstError = form.querySelector('.input-error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }

    /**
     * Validate a single field. Uses sharedRules if available, otherwise reads HTML attributes.
     */
    function validateField(field, sharedRules) {
        var value = field.value.trim();
        var errorEl = getErrorElement(field);
        if (!errorEl) return true;

        var label = getFieldLabel(field);

        // Look up rules: shared rules first, then fall back to HTML attributes
        var rules = null;
        if (sharedRules && sharedRules[field.name]) {
            rules = sharedRules[field.name];
        }

        // Helper to get a rule value: sharedRules first, then HTML attribute
        function rule(key, attrKey) {
            if (rules && rules[key] !== undefined) return rules[key];
            if (attrKey === undefined) attrKey = key;
            return field.getAttribute(attrKey);
        }

        // Required
        var isRequired = rules ? !!rules.required : field.hasAttribute('required');
        if (isRequired && value === '') {
            showError(field, errorEl, label + ' is required.');
            return false;
        }

        if (value === '') {
            clearError(field, errorEl);
            return true;
        }

        // MinLength
        var minLen = rule('minlength');
        if (minLen && value.length < parseInt(minLen)) {
            showError(field, errorEl, label + ' must be at least ' + minLen + ' characters.');
            return false;
        }

        // MaxLength
        var maxLen = rule('maxlength');
        if (maxLen && value.length > parseInt(maxLen)) {
            showError(field, errorEl, label + ' must be no more than ' + maxLen + ' characters.');
            return false;
        }

        // Email
        var isEmail = rules ? (rules.type === 'email') : (field.type === 'email');
        if (isEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            showError(field, errorEl, 'Please enter a valid email address.');
            return false;
        }

        // Number range (min/max/step)
        if (field.type === 'number') {
            var numVal = parseFloat(value);
            if (isNaN(numVal)) {
                showError(field, errorEl, label + ' must be a valid number.');
                return false;
            }

            var minVal = rule('min');
            if (minVal !== null && numVal < parseFloat(minVal)) {
                showError(field, errorEl, label + ' must be at least ' + minVal + '.');
                return false;
            }

            var maxVal = rule('max');
            if (maxVal !== null && numVal > parseFloat(maxVal)) {
                showError(field, errorEl, label + ' must be no more than ' + maxVal + '.');
                return false;
            }

            var step = rule('step');
            if (String(step) === '1' && numVal !== Math.floor(numVal)) {
                showError(field, errorEl, label + ' must be a whole number.');
                return false;
            }
        }

        // Pattern
        var pattern = rules ? rules.pattern : field.getAttribute('pattern');
        if (pattern) {
            var regex = new RegExp('^' + pattern + '$');
            if (!regex.test(value)) {
                var patternMsg = field.getAttribute('title') || label + ' format is invalid.';
                showError(field, errorEl, patternMsg);
                return false;
            }
        }

        clearError(field, errorEl);
        return true;
    }

    function getErrorElement(field) {
        var next = field.nextElementSibling;
        if (next && next.classList.contains('field-error')) return next;
        var group = field.closest('.form-group');
        if (group) return group.querySelector('.field-error');
        return null;
    }

    function getFieldLabel(field) {
        var group = field.closest('.form-group');
        if (group) {
            var label = group.querySelector('label');
            if (label) {
                return label.textContent.replace(/\s*\*\s*$/, '').trim();
            }
        }
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