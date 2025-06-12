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
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    
    // Get monthly transactions
    $stmt = $pdo->prepare("
        SELECT 
            t.id,
            t.transaction_date as date,
            t.amount,
            c.name as category,
            c.type,
            t.description
        FROM transactions t 
        JOIN categories c ON t.category_id = c.id 
        WHERE t.user_id = ? AND MONTH(t.transaction_date) = ? AND YEAR(t.transaction_date) = ?
        ORDER BY t.transaction_date DESC, t.created_at DESC
    ");
    $stmt->execute([$userId, $month, $year]);
    $transactions = $stmt->fetchAll();
    
    // Get monthly totals
    $stmt = $pdo->prepare("
        SELECT 
            c.type,
            SUM(t.amount) as total
        FROM transactions t 
        JOIN categories c ON t.category_id = c.id 
        WHERE t.user_id = ? AND MONTH(t.transaction_date) = ? AND YEAR(t.transaction_date) = ?
        GROUP BY c.type
    ");
    $stmt->execute([$userId, $month, $year]);
    $totals = $stmt->fetchAll();
    
    // Organize totals
    $summary = ['income' => 0, 'expense' => 0];
    foreach ($totals as $total) {
        $summary[$total['type']] = $total['total'];
    }
    $summary['balance'] = $summary['income'] - $summary['expense'];
    
    // Get category breakdown
    $stmt = $pdo->prepare("
        SELECT 
            c.name as category,
            c.type,
            SUM(t.amount) as total,
            COUNT(t.id) as count
        FROM transactions t 
        JOIN categories c ON t.category_id = c.id 
        WHERE t.user_id = ? AND MONTH(t.transaction_date) = ? AND YEAR(t.transaction_date) = ?
        GROUP BY c.id, c.name, c.type
        ORDER BY total DESC
    ");
    $stmt->execute([$userId, $month, $year]);
    $categoryBreakdown = $stmt->fetchAll();
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'transactions' => $transactions,
            'summary' => $summary,
            'categoryBreakdown' => $categoryBreakdown,
            'month' => $month,
            'year' => $year,
            'monthName' => date('F', mktime(0, 0, 0, $month, 1))
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
}
?>
