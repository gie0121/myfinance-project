<?php
header('Content-Type: application/json');

try {
    // Database configuration
    $host = 'localhost';
    $dbname = 'myfinance';
    $username = 'root';
    $password = '';
    
    $steps = [];
    
    // Step 1: Connect to MySQL server
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $steps[] = "Connected to MySQL server";
    
    // Step 2: Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $steps[] = "Database '$dbname' created/verified";
    
    // Step 3: Use the database
    $pdo->exec("USE $dbname");
    $steps[] = "Using database '$dbname'";
    
    // Step 4: Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $steps[] = "Users table created";
    
    // Step 5: Create categories table
    $pdo->exec("
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
    $steps[] = "Categories table created";
    
    // Step 6: Create transactions table
    $pdo->exec("
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
    $steps[] = "Transactions table created";
    
    // Step 7: Check if demo user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['demo@myfinance.com']);
    
    if (!$stmt->fetch()) {
        // Create demo user
        $demoPassword = password_hash('demo123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['Demo User', 'demo@myfinance.com', $demoPassword]);
        $demoUserId = $pdo->lastInsertId();
        $steps[] = "Demo user created (email: demo@myfinance.com, password: demo123)";
        
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
        
        $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type) VALUES (?, ?, ?)");
        foreach ($defaultCategories as $category) {
            $stmt->execute([$demoUserId, $category[0], $category[1]]);
        }
        $steps[] = "Default categories created (" . count($defaultCategories) . " categories)";
        
        // Create sample transactions for demo user
        $sampleTransactions = [
            // Recent transactions (June 2025)
            [1, 15.50, '2025-06-08', 'Lunch at cafe'],
            [2, 45.00, '2025-06-07', 'Bus card top-up'],
            [3, 120.00, '2025-06-06', 'Grocery shopping'],
            [4, 25.00, '2025-06-05', 'Movie tickets'],
            [9, 3000.00, '2025-06-01', 'Monthly salary'],
            
            // May transactions
            [1, 12.00, '2025-05-30', 'Coffee and pastry'],
            [5, 85.00, '2025-05-28', 'Electricity bill'],
            [3, 95.00, '2025-05-25', 'Weekly groceries'],
            [6, 150.00, '2025-05-20', 'Doctor visit'],
            [9, 3000.00, '2025-05-01', 'Monthly salary'],
            
            // April transactions
            [8, 800.00, '2025-04-15', 'Weekend trip'],
            [10, 500.00, '2025-04-10', 'Freelance project'],
            [1, 35.00, '2025-04-08', 'Dinner with friends'],
            [9, 3000.00, '2025-04-01', 'Monthly salary'],
        ];
        
        // Get category IDs
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE user_id = ? ORDER BY id");
        $stmt->execute([$demoUserId]);
        $categoryIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, category_id, amount, transaction_date, description) VALUES (?, ?, ?, ?, ?)");
        foreach ($sampleTransactions as $transaction) {
            // Use the correct category ID
            $categoryIndex = $transaction[0] - 1;
            $categoryId = $categoryIds[$categoryIndex] ?? $categoryIds[0];
            $stmt->execute([$demoUserId, $categoryId, $transaction[1], $transaction[2], $transaction[3]]);
        }
        $steps[] = "Sample transactions created (" . count($sampleTransactions) . " transactions)";
    } else {
        $steps[] = "Demo user already exists";
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Database setup completed successfully!',
        'steps' => $steps
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database setup failed',
        'details' => $e->getMessage()
    ]);
}
?>
