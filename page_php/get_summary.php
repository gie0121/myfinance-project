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
    $type = $_GET['type'] ?? 'expense'; // expense, income, or both
    $year = $_GET['year'] ?? date('Y');
    
    // Get monthly summary
    $monthlyData = [];
    
    if ($type === 'both') {
        // Get both income and expense
        $stmt = $pdo->prepare("
            SELECT 
                MONTH(t.transaction_date) as month,
                MONTHNAME(t.transaction_date) as month_name,
                c.type,
                SUM(t.amount) as total
            FROM transactions t 
            JOIN categories c ON t.category_id = c.id 
            WHERE t.user_id = ? AND YEAR(t.transaction_date) = ?
            GROUP BY MONTH(t.transaction_date), c.type
            ORDER BY MONTH(t.transaction_date)
        ");
        $stmt->execute([$userId, $year]);
        $results = $stmt->fetchAll();
        
        // Organize data by month
        $summary = [];
        foreach ($results as $row) {
            $month = $row['month_name'];
            if (!isset($summary[$month])) {
                $summary[$month] = ['income' => 0, 'expense' => 0];
            }
            $summary[$month][$row['type']] = $row['total'];
        }
        
        echo json_encode(['status' => 'success', 'data' => $summary]);
    } else {
        // Get specific type (income or expense)
        $stmt = $pdo->prepare("
            SELECT 
                MONTHNAME(t.transaction_date) as month,
                SUM(t.amount) as total
            FROM transactions t 
            JOIN categories c ON t.category_id = c.id 
            WHERE t.user_id = ? AND c.type = ? AND YEAR(t.transaction_date) = ?
            GROUP BY MONTH(t.transaction_date)
            ORDER BY MONTH(t.transaction_date)
        ");
        $stmt->execute([$userId, $type, $year]);
        $results = $stmt->fetchAll();
        
        $summary = [];
        foreach ($results as $row) {
            $summary[$row['month']] = $row['total'];
        }
        
        echo json_encode(['status' => 'success', 'data' => $summary]);
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
}
?>