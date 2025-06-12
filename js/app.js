/**
 * MyFinance - Unified JavaScript
 * Combined auth.js, script.js, random_background.js, and summary_search.js
 */

// ================================
// AUTHENTICATION MODULE
// ================================


/**
 * Validate user credentials
 */
function validateUser(username, password) {
  let users = JSON.parse(localStorage.getItem("users") || "{}");
  return users[username] && users[username].password === password;
}

/**
 * Logout current user
 */
function logoutUser() {
  localStorage.removeItem("currentUser");
  alert("You have been logged out.");
  window.location.href = "login.html";
}

/**
 * Initialize authentication forms and check login status
 */
function initAuth() {
  const regForm = document.getElementById("registerForm");
  const loginForm = document.getElementById("loginForm");

  // Login form handler
  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const username = document.getElementById("loginUsername").value;
      const password = document.getElementById("loginPassword").value;
      if (validateUser(username, password)) {
        localStorage.setItem("currentUser", username);
        alert("Welcome back, " + username + "!");
        window.location.href = "myfinance.html";
      } else {
        alert("Invalid credentials");
      }
    });
  }

  // Check if login is required for current page
  const currentUser = localStorage.getItem("currentUser");
  const needLoginPages = ["myfinance.html"];
  const currentPage = window.location.pathname.split("/").pop();
  if (needLoginPages.includes(currentPage) && !currentUser) {
    alert("Please login first.");
    window.location.href = "login.html";
  }
}

// ================================
// FINANCE TRACKER MODULE
// ================================

let total = 0;
let categoryData = {
  Food: 0,
  Transport: 0,
  Entertainment: 0
};

/**
 * Update pie chart display
 */
function updateChart() {
  const chartCanvas = document.getElementById('chart');
  if (!chartCanvas) return;
  
  const ctx = chartCanvas.getContext('2d');
  if (window.myChart) {
    window.myChart.destroy();
  }
  window.myChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: Object.keys(categoryData),
      datasets: [{
        data: Object.values(categoryData),
        backgroundColor: ['#f9a1bc', '#fbc687', '#97e5ef']
      }]
    },
    options: {
      responsive: true
    }
  });
}

/**
 * Initialize finance tracker form
 */
function initFinanceTracker() {
  const form = document.getElementById('form');
  if (!form) return;

  const tbody = document.querySelector('#transactions tbody');
  const totalSpan = document.getElementById('total');

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const amount = parseFloat(document.getElementById('amount').value);
    const category = document.getElementById('category').value;
    const date = document.getElementById('date').value;
    const desc = document.getElementById('description').value;

    // Add row to table
    const row = document.createElement('tr');
    row.innerHTML = `<td>$${amount.toFixed(2)}</td><td>${category}</td><td>${date}</td><td>${desc}</td>`;
    tbody.appendChild(row);

    // Update totals
    total += amount;
    totalSpan.textContent = total.toFixed(2);
    categoryData[category] += amount;

    // Save to localStorage for persistence
    const transactions = JSON.parse(localStorage.getItem("transactions") || "[]");
    transactions.push({ amount, category, date, description: desc });
    localStorage.setItem("transactions", JSON.stringify(transactions));

    updateChart();
    form.reset();
  });

  // Load existing transactions
  loadTransactions();
}

/**
 * Load transactions from localStorage
 */
function loadTransactions() {
  const transactions = JSON.parse(localStorage.getItem("transactions") || "[]");
  const tbody = document.querySelector('#transactions tbody');
  const totalSpan = document.getElementById('total');
  
  if (!tbody || !totalSpan) return;

  total = 0;
  categoryData = { Food: 0, Transport: 0, Entertainment: 0 };

  transactions.forEach(tx => {
    const row = document.createElement('tr');
    row.innerHTML = `<td>$${parseFloat(tx.amount).toFixed(2)}</td><td>${tx.category}</td><td>${tx.date}</td><td>${tx.description}</td>`;
    tbody.appendChild(row);

    total += parseFloat(tx.amount);
    categoryData[tx.category] += parseFloat(tx.amount);
  });

  totalSpan.textContent = total.toFixed(2);
  updateChart();
}

// ================================
// BACKGROUND THEME MODULE
// ================================

const backgroundColors = [
  "linear-gradient(to right, #ffe4e1, #fff9e6)",
  "linear-gradient(to right, #d0e6ff, #f1fcff)",
  "linear-gradient(to right, #fdf6e3, #ffeebb)",
  "linear-gradient(to right, #e8f0fe, #d7f9f1)",
  "linear-gradient(to right, #fce1ff, #fff0f5)"
];

/**
 * Set random background gradient
 */
function setRandomBackground() {
  const randomColor = backgroundColors[Math.floor(Math.random() * backgroundColors.length)];
  document.body.style.background = randomColor;
}

// ================================
// SUMMARY & SEARCH MODULE
// ================================

/**
 * Get month name from index
 */
function getMonthName(monthIndex) {
  return [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ][monthIndex];
}

/**
 * Update monthly summary table
 */
function updateMonthlySummary() {
  const transactions = JSON.parse(localStorage.getItem("transactions") || "[]");
  const monthlyTotals = new Array(12).fill(0);
  
  transactions.forEach(tx => {
    const date = new Date(tx.date);
    if (!isNaN(date)) {
      const month = date.getMonth();
      monthlyTotals[month] += parseFloat(tx.amount || 0);
    }
  });

  const tbody = document.getElementById("monthly-summary-body");
  if (!tbody) return;
  
  tbody.innerHTML = "";
  for (let i = 0; i < 12; i++) {
    tbody.innerHTML += `<tr><td>${getMonthName(i)}</td><td>$${monthlyTotals[i].toFixed(2)}</td></tr>`;
  }
}

/**
 * Search transactions by keyword
 */
function searchTransactions() {
  const transactions = JSON.parse(localStorage.getItem("transactions") || "[]");
  const keyword = document.getElementById("search").value.toLowerCase();
  
  const result = transactions.filter(tx =>
    (tx.category && tx.category.toLowerCase().includes(keyword)) ||
    (tx.description && tx.description.toLowerCase().includes(keyword))
  );
  
  const tbody = document.getElementById("search-body");
  if (!tbody) return;
  
  tbody.innerHTML = "";
  result.forEach(tx => {
    tbody.innerHTML += `<tr>
      <td>${tx.date}</td>
      <td>${tx.category}</td>
      <td>$${parseFloat(tx.amount).toFixed(2)}</td>
      <td>${tx.description}</td>
    </tr>`;
  });
}

// ================================
// INITIALIZATION
// ================================

/**
 * Initialize all modules when DOM is loaded
 */
document.addEventListener("DOMContentLoaded", function() {
  // Initialize authentication
  initAuth();
  
  // Initialize finance tracker
  initFinanceTracker();
  
  // Set random background
  setRandomBackground();
  
  // Initialize monthly summary if on that page
  if (document.getElementById("monthly-summary-body")) {
    updateMonthlySummary();
  }
  
  // Add search functionality if search input exists
  const searchInput = document.getElementById("search");
  if (searchInput) {
    searchInput.addEventListener("input", searchTransactions);
  }
});

// ================================
// GLOBAL FUNCTIONS
// ================================

// Make functions available globally for HTML onclick handlers
window.logoutUser = logoutUser;
window.searchTransactions = searchTransactions;
window.updateMonthlySummary = updateMonthlySummary;
