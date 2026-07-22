/**
 * validation-rules.js - Single source of truth for client-side validation rules.
 * Mirrors backend/validation_rules.php. Loaded before form-validation.js.
 */
window.ValidationRules = {
    login: {
        username: { required: true, minlength: 3 },
        password: { required: true, minlength: 6 }
    },
    register: {
        name:     { required: true, minlength: 2 },
        username: { required: true, minlength: 3, maxlength: 50, pattern: '^[a-zA-Z0-9_]+$' },
        email:    { required: true, type: 'email' },
        password: { required: true, minlength: 6 }
    },
    item: {
        item_name:    { required: true, minlength: 2 },
        price:        { required: true, min: 0 },
        min_quantity: { required: true, min: 0, step: 1 }
    },
    supplier: {
        supplier_name: { required: true, minlength: 2 }
    },
    stock: {
        current_qty:   { required: true, min: 0, step: 1 },
        min_threshold: { required: true, min: 0, step: 1 }
    },
    transaction: {
        item_id:  { required: true },
        type:     { required: true },
        quantity: { required: true, min: 1, step: 1 }
    },
    user_edit: {
        username: { required: true, minlength: 3 },
        name:     { required: true, minlength: 2 },
        email:    { required: true, type: 'email' }
    }
};