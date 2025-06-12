<?php
session_start();
require_once 'config.php';

header("Content-Type: application/json");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in first']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $type = $_GET['type'] ?? ''; // expense, income, or empty for both
    
    $sql = "SELECT id, name, type FROM categories WHERE user_id = ?";
    $params = [$userId];
    
    if (!empty($type)) {
        $sql .= " AND type = ?";
        $params[] = $type;
    }
    
    $sql .= " ORDER BY type, name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $categories = $stmt->fetchAll();
    
    echo json_encode(['status' => 'success', 'data' => $categories]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
}
?>
