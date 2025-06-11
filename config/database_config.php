<?php
/**
 * Database Credentials Configuration
 * Contains sensitive database connection information
 * Keep this file secure and out of version control
 */

// Database connection credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'myfinance');
define('DB_USER', 'root');
define('DB_PASS', 'password'); // XAMPP default password is empty
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// Alternative database configurations for different environments
// Uncomment and modify as needed

// For XAMPP with custom settings:
// define('DB_HOST', 'localhost');
// define('DB_USER', 'your_username');
// define('DB_PASS', 'your_password');

// For remote database:
// define('DB_HOST', 'your-server.com');
// define('DB_USER', 'remote_user');
// define('DB_PASS', 'remote_password');

// For different port:
// define('DB_PORT', 3307);

// SSL Configuration (if needed)
// define('DB_SSL_ENABLED', false);
// define('DB_SSL_CERT', '/path/to/cert.pem');
// define('DB_SSL_KEY', '/path/to/key.pem');
// define('DB_SSL_CA', '/path/to/ca.pem');
?>
