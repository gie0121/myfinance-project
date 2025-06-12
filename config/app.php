<?php
/**
 * Application Configuration
 * Main configuration loader for MyFinance Application
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration files
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';

// Application constants
define('BASE_URL', '/MyFinance/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('LOG_DIR', __DIR__ . '/../logs/');

// Create necessary directories
// if (!file_exists(UPLOAD_DIR)) {
//     mkdir(UPLOAD_DIR, 0755, true);
// }
// if (!file_exists(LOG_DIR)) {
//     mkdir(LOG_DIR, 0755, true);
// }

/**
 * Application Helper Class
 */
class App {
    
    /**
     * Get database connection
     */
    public static function db() {
        return DatabaseConnection::getPDO();
    }
    
    /**
     * Log application events
     */
    public static function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        // file_put_contents(LOG_DIR . 'app.log', $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Redirect with message
     */
    public static function redirect($url, $message = null) {
        if ($message) {
            $_SESSION['flash_message'] = $message;
        }
        header("Location: $url");
        exit();
    }
    
    /**
     * Get and clear flash message
     */
    public static function getFlashMessage() {
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    /**
     * JSON response helper
     */
    public static function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}

// Initialize application
App::log("Application initialized for user: " . (SecurityHelper::getCurrentUserId() ?? 'guest'));
?>
