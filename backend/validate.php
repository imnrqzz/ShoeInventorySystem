<?php
// backend/validate.php - Shared validation helper. Reads rules from validation_rules.php.

function getValidationRules() {
    static $rules = null;
    if ($rules === null) {
        $rules = require __DIR__ . '/validation_rules.php';
    }
    return $rules;
}

function validateField($value, $rules) {
    $value = is_string($value) ? trim($value) : $value;
    $errors = [];

    if (!empty($rules['required']) && ($value === '' || $value === null)) {
        $errors[] = 'This field is required.';
        return $errors;
    }

    if ($value === '' || $value === null) {
        return [];
    }

    if (isset($rules['minlength']) && is_string($value) && mb_strlen($value) < $rules['minlength']) {
        $errors[] = "Must be at least {$rules['minlength']} characters.";
    }

    if (isset($rules['maxlength']) && is_string($value) && mb_strlen($value) > $rules['maxlength']) {
        $errors[] = "Must be no more than {$rules['maxlength']} characters.";
    }

    if (!empty($rules['type']) && $rules['type'] === 'email') {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
    }

    if (isset($rules['min']) && is_numeric($value) && (float)$value < (float)$rules['min']) {
        $errors[] = "Must be at least {$rules['min']}.";
    }

    if (isset($rules['max']) && is_numeric($value) && (float)$value > (float)$rules['max']) {
        $errors[] = "Must be no more than {$rules['max']}.";
    }

    if (!empty($rules['pattern']) && is_string($value) && !preg_match('/^' . $rules['pattern'] . '$/', $value)) {
        $errors[] = 'Format is invalid.';
    }

    return $errors;
}

function validateForm($formName, $data) {
    $allRules = getValidationRules();
    $rules = $allRules[$formName] ?? [];
    $errors = [];

    foreach ($rules as $field => $fieldRules) {
        $fieldErrors = validateField($data[$field] ?? '', $fieldRules);
        if ($fieldErrors) {
            $errors[$field] = $fieldErrors;
        }
    }

    return $errors;
}