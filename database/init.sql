
CREATE DATABASE IF NOT EXISTS myfinance;
USE myfinance;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  name VARCHAR(50) NOT NULL,
  type ENUM('income','expense') NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  transaction_date DATE NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- 測試帳號與密碼 (密碼為 123456)
INSERT INTO users ('username', 'email', 'password') VALUES 
('Demo User', 'test@example.com', '$2y$10$abcdefgHijkLmnopqrstuvOPQRSTUvwxyzabcdEFGHijklmNOpqr'); 

-- 測試分類（需搭配正確 user_id）
INSERT INTO categories (user_id, name, type) VALUES 
(1, 'Food', 'expense'),
(1, 'Salary', 'income');
