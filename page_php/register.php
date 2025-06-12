<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get and sanitize input
        $username = sanitizeInput($_POST["username"] ?? '');
        $email = sanitizeInput($_POST["email"] ?? '');
        $password = $_POST["password"] ?? '';
        
        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
            exit;
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
            exit;
        }
        
        // Check password strength
        if (strlen($password) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters long']);
            exit;
        }
        
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'User with this email or username already exists']);
            exit;
        }
        
        // Hash password and create user
        $hashedPassword = hashPassword($password);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        
        if ($stmt->execute([$username, $email, $hashedPassword])) {
            $userId = $pdo->lastInsertId();
            
            // Create default categories for new user
            $defaultCategories = [
                ['Food', 'expense'],
                ['Transport', 'expense'],
                ['Shopping', 'expense'],
                ['Entertainment', 'expense'],
                ['Utilities', 'expense'],
                ['Salary', 'income'],
                ['Freelance', 'income'],
                ['Investment', 'income']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type) VALUES (?, ?, ?)");
            foreach ($defaultCategories as $category) {
                $stmt->execute([$userId, $category[0], $category[1]]);
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Registration successful']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Registration failed']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>