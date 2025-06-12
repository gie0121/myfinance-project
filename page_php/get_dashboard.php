<?php
session_start();
require_once 'config.php';
require_once 'database_utils.php';

header("Content-Type: application/json");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in first']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $dashboardData = getUserDashboardData($userId);
    
    echo json_encode([
        'status' => 'success',
        'data' => $dashboardData
    ]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to load dashboard data']);
}
?>
