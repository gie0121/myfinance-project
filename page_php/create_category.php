<?php
session_start();
require_once 'config.php';

header("Content-Type: application/json");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in first']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $userId = $_SESSION['user_id'];
        $name = sanitizeInput($_POST["name"] ?? '');
        $type = sanitizeInput($_POST["type"] ?? 'expense');
        
        // Validate input
        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Category name is required']);
            exit;
        }
        
        if (!in_array($type, ['income', 'expense'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid category type']);
            exit;
        }
        
        // Check if category already exists for this user
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ? AND type = ?");
        $stmt->execute([$userId, $name, $type]);
        
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Category already exists']);
            exit;
        }
        
        // Create new category
        $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type) VALUES (?, ?, ?)");
        
        if ($stmt->execute([$userId, $name, $type])) {
            $categoryId = $pdo->lastInsertId();
            echo json_encode([
                'status' => 'success', 
                'message' => 'Category created successfully',
                'data' => ['id' => $categoryId, 'name' => $name, 'type' => $type]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create category']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
