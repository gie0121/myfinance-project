<?php
/**
 * Security Configuration and Helper Functions
 * Centralized security utilities for MyFinance Application
 */

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.use_strict_mode', 1);
    session_start();
}
/**
 * Security Helper Class
 */
class SecurityHelper {
    
    /**
     * Hash password securely
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password against hash
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Require login (redirect if not logged in)
     */
    public static function requireLogin($redirectTo = '../pages/login.html') {
        if (!self::isLoggedIn()) {
            header("Location: $redirectTo");
            exit();
        }
    }
    
    /**
     * Get current user ID
     */
    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Validate email format
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate password strength
     */
    public static function isValidPassword($password, $minLength = 6) {
        return strlen($password) >= $minLength;
    }
    
    /**
     * Generate secure random string
     */
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
}

// Legacy function aliases for backward compatibility
function hashPassword($password) {
    return SecurityHelper::hashPassword($password);
}

function verifyPassword($password, $hash) {
    return SecurityHelper::verifyPassword($password, $hash);
}

function sanitizeInput($data) {
    return SecurityHelper::sanitizeInput($data);
}

function generateCSRFToken() {
    return SecurityHelper::generateCSRFToken();
}

function verifyCSRFToken($token) {
    return SecurityHelper::verifyCSRFToken($token);
}
?>
