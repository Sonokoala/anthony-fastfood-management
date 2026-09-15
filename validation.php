<?php
/**
 * Input validation and sanitization functions for Anthony's Fast Food staff management system
 */

/**
 * Validates and sanitizes a string input
 * @param string|null $input The input to validate
 * @param int $minLength Minimum length (default: 1)
 * @param int $maxLength Maximum length (default: 255)
 * @param string $pattern Regex pattern for validation (default: null)
 * @return array ['valid' => bool, 'value' => string|null, 'error' => string|null]
 */
function validateString($input, $minLength = 1, $maxLength = 255, $pattern = null) {
    if ($input === null || $input === '') {
        return ['valid' => false, 'value' => null, 'error' => 'Input is required'];
    }

    $sanitized = trim(strip_tags($input));
    
    if (strlen($sanitized) < $minLength) {
        return ['valid' => false, 'value' => null, 'error' => "Input must be at least $minLength characters"];
    }
    
    if (strlen($sanitized) > $maxLength) {
        return ['valid' => false, 'value' => null, 'error' => "Input cannot exceed $maxLength characters"];
    }
    
    if ($pattern && !preg_match($pattern, $sanitized)) {
        return ['valid' => false, 'value' => null, 'error' => 'Input format is invalid'];
    }
    
    return ['valid' => true, 'value' => $sanitized, 'error' => null];
}

/**
 * Validates and sanitizes an email address
 * @param string|null $email The email to validate
 * @return array ['valid' => bool, 'value' => string|null, 'error' => string|null]
 */
function validateEmail($email) {
    $result = validateString($email, 5, 255);
    if (!$result['valid']) {
        return $result;
    }
    
    $sanitized = filter_var($result['value'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'value' => null, 'error' => 'Invalid email format'];
    }
    
    return ['valid' => true, 'value' => $sanitized, 'error' => null];
}

/**
 * Validates and sanitizes a phone number
 * @param string|null $phone The phone number to validate
 * @return array ['valid' => bool, 'value' => string|null, 'error' => string|null]
 */
function validatePhone($phone) {
    $result = validateString($phone, 8, 15);
    if (!$result['valid']) {
        return $result;
    }
    
    // Remove all non-numeric characters except + (for international numbers)
    $sanitized = preg_replace('/[^0-9+]/', '', $result['value']);
    
    // Basic phone number format validation
    if (!preg_match('/^\+?[0-9]{8,14}$/', $sanitized)) {
        return ['valid' => false, 'value' => null, 'error' => 'Invalid phone number format'];
    }
    
    return ['valid' => true, 'value' => $sanitized, 'error' => null];
}

/**
 * Validates and sanitizes a numeric input
 * @param mixed $input The input to validate
 * @param float|null $min Minimum value (default: null)
 * @param float|null $max Maximum value (default: null)
 * @return array ['valid' => bool, 'value' => float|null, 'error' => string|null]
 */
function validateNumeric($input, $min = null, $max = null) {
    if ($input === null || $input === '') {
        return ['valid' => false, 'value' => null, 'error' => 'Input is required'];
    }
    
    $sanitized = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    if ($sanitized === false || !is_numeric($sanitized)) {
        return ['valid' => false, 'value' => null, 'error' => 'Input must be a number'];
    }
    
    $value = floatval($sanitized);
    
    if ($min !== null && $value < $min) {
        return ['valid' => false, 'value' => null, 'error' => "Value must be at least $min"];
    }
    
    if ($max !== null && $value > $max) {
        return ['valid' => false, 'value' => null, 'error' => "Value cannot exceed $max"];
    }
    
    return ['valid' => true, 'value' => $value, 'error' => null];
}

/**
 * Validates and sanitizes a date input
 * @param string|null $date The date to validate (YYYY-MM-DD format)
 * @param string|null $min Minimum date (default: null)
 * @param string|null $max Maximum date (default: null)
 * @return array ['valid' => bool, 'value' => string|null, 'error' => string|null]
 */
function validateDate($date, $min = null, $max = null) {
    // Remove the string length validation since date format is fixed
    if (empty($date)) {
        return ['valid' => false, 'value' => null, 'error' => 'Date is required'];
    }
    
    $sanitized = trim($date);
    $dateTime = DateTime::createFromFormat('Y-m-d', $sanitized);
    
    if (!$dateTime || $dateTime->format('Y-m-d') !== $sanitized) {
        return ['valid' => false, 'value' => null, 'error' => 'Invalid date format. Please use YYYY-MM-DD format (e.g., 1990-01-01)'];
    }
    
    if ($min !== null) {
        $minDate = new DateTime($min);
        if ($dateTime < $minDate) {
            return ['valid' => false, 'value' => null, 'error' => "Date must be after {$minDate->format('Y-m-d')}"]; 
        }
    }
    
    if ($max !== null) {
        $maxDate = new DateTime($max);
        if ($dateTime > $maxDate) {
            return ['valid' => false, 'value' => null, 'error' => "Date must be before {$maxDate->format('Y-m-d')}"]; 
        }
    }
    
    return ['valid' => true, 'value' => $sanitized, 'error' => null];
}

/**
 * Validates and sanitizes an ID input
 * @param mixed $input The input to validate
 * @return array ['valid' => bool, 'value' => int|null, 'error' => string|null]
 */
function validateId($input) {
    if ($input === null || $input === '') {
        return ['valid' => false, 'value' => null, 'error' => 'ID is required'];
    }
    
    $sanitized = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    if ($sanitized === false || !filter_var($sanitized, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        return ['valid' => false, 'value' => null, 'error' => 'Invalid ID format'];
    }
    
    return ['valid' => true, 'value' => intval($sanitized), 'error' => null];
}

/**
 * Validates and sanitizes a password
 * @param string|null $password The password to validate
 * @return array ['valid' => bool, 'value' => string|null, 'error' => string|null]
 */
function validatePassword($password) {
    if ($password === null || $password === '') {
        return ['valid' => false, 'value' => null, 'error' => 'Password is required'];
    }
    
    if (strlen($password) < 8) {
        return ['valid' => false, 'value' => null, 'error' => 'Password must be at least 8 characters'];
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'value' => null, 'error' => 'Password must contain at least one uppercase letter'];
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'value' => null, 'error' => 'Password must contain at least one lowercase letter'];
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'value' => null, 'error' => 'Password must contain at least one number'];
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        return ['valid' => false, 'value' => null, 'error' => 'Password must contain at least one special character'];
    }
    
    return ['valid' => true, 'value' => $password, 'error' => null];
}

/**
 * Escapes a value for safe use in SQL queries
 * @param mixed $value The value to escape
 * @param mysqli $connection The database connection
 * @return string The escaped value
 */
function escapeValue($value, $connection) {
    if ($value === null) {
        return 'NULL';
    }
    return "'" . $connection->real_escape_string($value) . "'";
} 