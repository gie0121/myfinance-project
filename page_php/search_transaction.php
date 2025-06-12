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
    $keyword = sanitizeInput($_GET["keyword"] ?? '');
    $category = sanitizeInput($_GET["category"] ?? '');
    $dateFrom = sanitizeInput($_GET["date_from"] ?? '');
    $dateTo = sanitizeInput($_GET["date_to"] ?? '');
    $type = sanitizeInput($_GET["type"] ?? ''); // expense, income, or empty for both
    
    // Build query
    $whereConditions = ["t.user_id = ?"];
    $params = [$userId];
    
    if (!empty($keyword)) {
        $whereConditions[] = "t.description LIKE ?";
        $params[] = "%$keyword%";
    }
    
    if (!empty($category)) {
        $whereConditions[] = "c.name LIKE ?";
        $params[] = "%$category%";
    }
    
    if (!empty($type)) {
        $whereConditions[] = "c.type = ?";
        $params[] = $type;
    }
    
    if (!empty($dateFrom)) {
        $whereConditions[] = "t.transaction_date >= ?";
        $params[] = $dateFrom;
    }
    
    if (!empty($dateTo)) {
        $whereConditions[] = "t.transaction_date <= ?";
        $params[] = $dateTo;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $sql = "
        SELECT 
            t.id,
            t.transaction_date as date,
            t.amount,
            c.name as category,
            c.type,
            t.description,
            t.created_at
        FROM transactions t 
        JOIN categories c ON t.category_id = c.id 
        WHERE $whereClause
        ORDER BY t.transaction_date DESC, t.created_at DESC
        LIMIT 100
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();
    
    // Format the data
    $formattedTransactions = [];
    foreach ($transactions as $transaction) {
        $formattedTransactions[] = [
            'id' => $transaction['id'],
            'date' => $transaction['date'],
            'amount' => floatval($transaction['amount']),
            'category' => $transaction['category'],
            'type' => $transaction['type'],
            'description' => $transaction['description'],
            'created_at' => $transaction['created_at']
        ];
    }
    
    echo json_encode([
        'status' => 'success', 
        'data' => $formattedTransactions,
        'count' => count($formattedTransactions)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
}
?>