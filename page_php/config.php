<?php
/**
 * Legacy Configuration File
 * This file now loads the new centralized configuration
 * Kept for backward compatibility
 */

// Load the new centralized configuration
require_once __DIR__ . '/../config/app.php';

// Legacy variables for backward compatibility
// (These are now handled by the DatabaseConnection class)

// Note: The following variables are still available:
// $pdo - PDO connection instance
// $mysqli - MySQLi connection instance

// All security functions are now available through SecurityHelper class
// or legacy function aliases
?>
