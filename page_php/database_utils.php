<?php
// Database utilities and helper functions
require_once 'config.php';

class DatabaseManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get user statistics
     */
    public function getUserStats($userId) {
        try {
            $stats = [];
            
            // Total transactions
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
            $stmt->execute([$userId]);
            $stats['total_transactions'] = $stmt->fetchColumn();
            
            // Total income this month
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(t.amount), 0) 
                FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = ? AND c.type = 'income' 
                AND MONTH(t.transaction_date) = MONTH(CURRENT_DATE())
                AND YEAR(t.transaction_date) = YEAR(CURRENT_DATE())
            ");
            $stmt->execute([$userId]);
            $stats['monthly_income'] = $stmt->fetchColumn();
            
            // Total expenses this month
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(t.amount), 0) 
                FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = ? AND c.type = 'expense' 
                AND MONTH(t.transaction_date) = MONTH(CURRENT_DATE())
                AND YEAR(t.transaction_date) = YEAR(CURRENT_DATE())
            ");
            $stmt->execute([$userId]);
            $stats['monthly_expenses'] = $stmt->fetchColumn();
            
            // Balance this month
            $stats['monthly_balance'] = $stats['monthly_income'] - $stats['monthly_expenses'];
            
            // Most used category
            $stmt = $this->pdo->prepare("
                SELECT c.name, COUNT(t.id) as usage_count
                FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = ? 
                GROUP BY c.id, c.name 
                ORDER BY usage_count DESC 
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $mostUsedCategory = $stmt->fetch();
            $stats['most_used_category'] = $mostUsedCategory ? $mostUsedCategory['name'] : 'None';
            
            return $stats;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get recent transactions
     */
    public function getRecentTransactions($userId, $limit = 5) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    t.id,
                    t.transaction_date as date,
                    t.amount,
                    c.name as category,
                    c.type,
                    t.description
                FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = ? 
                ORDER BY t.transaction_date DESC, t.created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get spending by category for current month
     */
    public function getMonthlySpendingByCategory($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    c.name as category,
                    SUM(t.amount) as total,
                    COUNT(t.id) as transaction_count
                FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = ? AND c.type = 'expense'
                AND MONTH(t.transaction_date) = MONTH(CURRENT_DATE())
                AND YEAR(t.transaction_date) = YEAR(CURRENT_DATE())
                GROUP BY c.id, c.name 
                ORDER BY total DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Backup user data
     */
    public function backupUserData($userId) {
        try {
            $backup = [];
            
            // Get user info
            $stmt = $this->pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $backup['user'] = $stmt->fetch();
            
            // Get categories
            $stmt = $this->pdo->prepare("SELECT name, type FROM categories WHERE user_id = ?");
            $stmt->execute([$userId]);
            $backup['categories'] = $stmt->fetchAll();
            
            // Get transactions
            $stmt = $this->pdo->prepare("
                SELECT 
                    t.amount,
                    t.transaction_date,
                    t.description,
                    c.name as category,
                    c.type as category_type
                FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = ?
                ORDER BY t.transaction_date DESC
            ");
            $stmt->execute([$userId]);
            $backup['transactions'] = $stmt->fetchAll();
            
            return $backup;
            
        } catch (Exception $e) {
            return false;
        }
    }
}

// Helper functions for common database operations
function getUserDashboardData($userId) {
    global $pdo;
    $dbManager = new DatabaseManager($pdo);
    
    return [
        'stats' => $dbManager->getUserStats($userId),
        'recent_transactions' => $dbManager->getRecentTransactions($userId),
        'spending_by_category' => $dbManager->getMonthlySpendingByCategory($userId)
    ];
}

function validateTransaction($data) {
    $errors = [];
    
    if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
        $errors[] = 'Valid amount is required';
    }
    
    if (!isset($data['category']) || empty(trim($data['category']))) {
        $errors[] = 'Category is required';
    }
    
    if (!isset($data['date']) || !DateTime::createFromFormat('Y-m-d', $data['date'])) {
        $errors[] = 'Valid date is required';
    }
    
    if (!isset($data['type']) || !in_array($data['type'], ['income', 'expense'])) {
        $errors[] = 'Valid transaction type is required';
    }
    
    return $errors;
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}
?>
