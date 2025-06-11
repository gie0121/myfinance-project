<?php
// Load centralized configuration
require_once 'config/app.php';

// Check if user is logged in
$isLoggedIn = SecurityHelper::isLoggedIn();
$username = $isLoggedIn ? $_SESSION['username'] : null;

// Get any flash messages
$flashMessage = App::getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo APP_NAME; ?> 🌈</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <?php if ($flashMessage): ?>
    <div class="alert alert-info" style="padding: 15px; margin-bottom: 20px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724;">
      <?php echo htmlspecialchars($flashMessage); ?>
    </div>
    <?php endif; ?>
    
    <div class="welcome-section">
      <h1>🌸 Welcome to <?php echo APP_NAME; ?></h1>
      
      <?php if ($isLoggedIn): ?>
        <div class="user-greeting">
          Hello, <?php echo htmlspecialchars($username); ?>! 👋
        </div>
        <p>Ready to manage your finances in the cutest way possible? 🍡</p>
      <?php else: ?>
        <p>Track your money in the cutest way possible 🍡</p>
        <p>Please log in or register to get started!</p>
      <?php endif; ?>
      
      <div class="nav-buttons">
        <?php if ($isLoggedIn): ?>
          <a href="pages/myfinance.html" class="btn">💰 Finance Tracker</a>
          <a href="pages/monthly_summary.html" class="btn">📊 Monthly Summary</a>
          <a href="pages/search_transactions.html" class="btn secondary">🔍 Search Transactions</a>
          <a href="pages/view_summary.html" class="btn secondary">📈 View Summary</a>
          <a href="page php/logout.php" class="btn danger">🚪 Logout</a>
        <?php else: ?>
          <a href="page_php/login.php" class="btn">🔐 Login</a>
          <a href="page_php/register.php" class="btn secondary">📝 Register</a>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!$isLoggedIn): ?>
    <div class="features">
      <div class="feature-card">
        <div class="feature-icon">💰</div>
        <h3>Track Expenses</h3>
        <p>Keep track of your daily expenses with our intuitive interface</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">📊</div>
        <h3>Monthly Reports</h3>
        <p>Get detailed monthly summaries of your spending patterns</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">🔍</div>
        <h3>Smart Search</h3>
        <p>Find specific transactions quickly with our powerful search</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">📈</div>
        <h3>Visual Analytics</h3>
        <p>Beautiful charts and graphs to visualize your financial data</p>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($isLoggedIn): ?>
    <div class="features">
      <div class="feature-card">
        <div class="feature-icon">🎯</div>
        <h3>Quick Actions</h3>
        <p>Jump straight to the most common tasks:</p>
        <div style="margin-top: 15px;">
          <a href="pages/myfinance.html" class="btn" style="display: block; margin-bottom: 10px;">Add New Transaction</a>
          <a href="pages/monthly_summary.html" class="btn secondary" style="display: block;">View This Month</a>
        </div>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">📱</div>
        <h3>Mobile Friendly</h3>
        <p>Access your finances on any device, anywhere, anytime</p>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <script>
    // Add some dynamic behavior
    document.addEventListener('DOMContentLoaded', function() {
      // Add smooth transitions
      const cards = document.querySelectorAll('.feature-card');
      cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.style.animation = 'fadeInUp 0.6s ease forwards';
      });
    });
  </script>
</body>
</html>
