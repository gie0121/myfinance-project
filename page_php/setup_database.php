<?php
// Database setup and initialization script
require_once 'config.php';

echo "Setting up MyFinance Database...\n";

try {
    // Create database if it doesn't exist
    $pdo_temp = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database created/verified\n";
    
    // Use the database
    $pdo_temp->exec("USE " . DB_NAME);
    
    // Create users table
    $pdo_temp->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Users table created\n";
    
    // Create categories table
    $pdo_temp->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(50) NOT NULL,
            type ENUM('income','expense') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_category (user_id, name, type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Categories table created\n";
    
    // Create transactions table
    $pdo_temp->exec("
        CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            category_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            transaction_date DATE NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
            INDEX idx_user_date (user_id, transaction_date),
            INDEX idx_user_category (user_id, category_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Transactions table created\n";
    
    // Check if demo user exists
    $stmt = $pdo_temp->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['demo@myfinance.com']);
    
    if (!$stmt->fetch()) {
        // Create demo user
        $demoPassword = hashPassword('demo123');
        $stmt = $pdo_temp->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['Demo User', 'demo@myfinance.com', $demoPassword]);
        $demoUserId = $pdo_temp->lastInsertId();
        echo "✓ Demo user created (email: demo@myfinance.com, password: demo123)\n";
        
        // Create default categories for demo user
        $defaultCategories = [
            ['Food & Dining', 'expense'],
            ['Transportation', 'expense'],
            ['Shopping', 'expense'],
            ['Entertainment', 'expense'],
            ['Bills & Utilities', 'expense'],
            ['Healthcare', 'expense'],
            ['Education', 'expense'],
            ['Travel', 'expense'],
            ['Salary', 'income'],
            ['Freelance', 'income'],
            ['Investment', 'income'],
            ['Gift', 'income']
        ];
        
        $stmt = $pdo_temp->prepare("INSERT INTO categories (user_id, name, type) VALUES (?, ?, ?)");
        foreach ($defaultCategories as $category) {
            $stmt->execute([$demoUserId, $category[0], $category[1]]);
        }
        echo "✓ Default categories created\n";
        
        // Create sample transactions for demo user
        $sampleTransactions = [
            // Recent transactions
            [$demoUserId, 1, 15.50, '2025-06-08', 'Lunch at cafe'],
            [$demoUserId, 2, 45.00, '2025-06-07', 'Bus card top-up'],
            [$demoUserId, 3, 120.00, '2025-06-06', 'Grocery shopping'],
            [$demoUserId, 4, 25.00, '2025-06-05', 'Movie tickets'],
            [$demoUserId, 9, 3000.00, '2025-06-01', 'Monthly salary'],
            
            // May transactions
            [$demoUserId, 1, 12.00, '2025-05-30', 'Coffee and pastry'],
            [$demoUserId, 5, 85.00, '2025-05-28', 'Electricity bill'],
            [$demoUserId, 3, 95.00, '2025-05-25', 'Weekly groceries'],
            [$demoUserId, 6, 150.00, '2025-05-20', 'Doctor visit'],
            [$demoUserId, 9, 3000.00, '2025-05-01', 'Monthly salary'],
            
            // April transactions
            [$demoUserId, 8, 800.00, '2025-04-15', 'Weekend trip'],
            [$demoUserId, 10, 500.00, '2025-04-10', 'Freelance project'],
            [$demoUserId, 1, 35.00, '2025-04-08', 'Dinner with friends'],
            [$demoUserId, 9, 3000.00, '2025-04-01', 'Monthly salary'],
        ];
        
        // Get category IDs
        $stmt = $pdo_temp->prepare("SELECT id FROM categories WHERE user_id = ? ORDER BY id");
        $stmt->execute([$demoUserId]);
        $categoryIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $stmt = $pdo_temp->prepare("INSERT INTO transactions (user_id, category_id, amount, transaction_date, description) VALUES (?, ?, ?, ?, ?)");
        foreach ($sampleTransactions as $transaction) {
            // Use the correct category ID
            $categoryIndex = $transaction[1] - 1;
            $categoryId = $categoryIds[$categoryIndex] ?? $categoryIds[0];
            $stmt->execute([$transaction[0], $categoryId, $transaction[2], $transaction[3], $transaction[4]]);
        }
        echo "✓ Sample transactions created\n";
    } else {
        echo "✓ Demo user already exists\n";
    }
    
    echo "\n🎉 Database setup completed successfully!\n";
    echo "You can now log in with:\n";
    echo "Email: demo@myfinance.com\n";
    echo "Password: demo123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
