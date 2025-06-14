<?php
session_start();
require_once __DIR__ . '/../config/database_config.php';

header('Content-Type: application/json');

// 檢查是否登入
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => '請先登入']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 查詢交易資料（這裡使用 category 表 JOIN）
    $stmt = $pdo->prepare("
        SELECT 
            t.amount,
            c.name AS category,
            t.transaction_date AS date,
            t.description AS note
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?
        ORDER BY t.transaction_date DESC
    ");
    $stmt->execute([$userId]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $transactions]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => '資料庫錯誤']);
}
?>
