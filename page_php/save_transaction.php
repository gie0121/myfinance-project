<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Please log in first']);
        exit;
    }
    
    try {
        // Get and sanitize input
        $amount = floatval($_POST["amount"] ?? 0);
        $category = sanitizeInput($_POST["category"] ?? '');
        $description = sanitizeInput($_POST["description"] ?? '');
        $date = sanitizeInput($_POST["date"] ?? '');
        $type = sanitizeInput($_POST["type"] ?? 'expense'); // expense or income
        
        // Validate input
        if ($amount <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Amount must be greater than 0']);
            exit;
        }
        
        if (empty($category)) {
            echo json_encode(['status' => 'error', 'message' => 'Category is required']);
            exit;
        }
        
        if (empty($date)) {
            echo json_encode(['status' => 'error', 'message' => 'Date is required']);
            exit;
        }
        
        // Validate date format
        if (!DateTime::createFromFormat('Y-m-d', $date)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid date format']);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Check if category exists for this user, if not create it
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ? AND type = ?");
        $stmt->execute([$userId, $category, $type]);
        $categoryData = $stmt->fetch();
        
        if (!$categoryData) {
            // Create new category
            $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $category, $type]);
            $categoryId = $pdo->lastInsertId();
        } else {
            $categoryId = $categoryData['id'];
        }
        
        // Save transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, category_id, amount, transaction_date, description) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$userId, $categoryId, $amount, $date, $description])) {
            echo json_encode(['status' => 'success', 'message' => 'Transaction saved successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save transaction']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>