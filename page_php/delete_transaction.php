<?php
session_start();
require_once 'config.php';

header("Content-Type: application/json");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in first']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "DELETE" || ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['_method']) && $_POST['_method'] == 'DELETE')) {
    try {
        $userId = $_SESSION['user_id'];
        $transactionId = $_POST['id'] ?? $_GET['id'] ?? null;
        
        if (!$transactionId) {
            echo json_encode(['status' => 'error', 'message' => 'Transaction ID is required']);
            exit;
        }
        
        // Verify that this transaction belongs to the current user
        $stmt = $pdo->prepare("SELECT id FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->execute([$transactionId, $userId]);
        
        if (!$stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Transaction not found or access denied']);
            exit;
        }
        
        // Delete the transaction
        $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
        
        if ($stmt->execute([$transactionId, $userId])) {
            echo json_encode(['status' => 'success', 'message' => 'Transaction deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete transaction']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
