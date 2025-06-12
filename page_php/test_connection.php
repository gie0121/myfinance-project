<?php
echo "Testing database connection...\n";

// Test basic connection first
try {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    
    // Try to connect to MySQL server
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to MySQL server\n";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS myfinance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database 'myfinance' created/exists\n";
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=myfinance;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to myfinance database\n";
    
    echo "Database connection test successful!\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
?>
