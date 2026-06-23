-- ==========================================
-- MAL TECH STORE - DATABASE SCHEMA
-- ==========================================

CREATE DATABASE IF NOT EXISTS maltechstore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE maltechstore_db;

-- 1. USERS TABLE
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('consumer', 'seller', 'admin') DEFAULT 'consumer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. CATEGORIES TABLE (Dynamic, managed by admin)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. PRODUCTS TABLE
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) DEFAULT NULL,
    category_id INT NOT NULL,
    condition_status ENUM('New', 'Like New', 'Good', 'Fair') DEFAULT 'Good',
    location VARCHAR(100) DEFAULT NULL,
    seller_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_category (category_id)
) ENGINE=InnoDB;

-- 4. ORDERS TABLE (Mock checkout flow)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending_approval', 'approved', 'cancelled', 'completed') DEFAULT 'pending_approval',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. ORDER ITEMS TABLE
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price_at_purchase DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================================
-- SEED DATA (FOR TESTING)
-- ==========================================

-- Users (password for all is '123')
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@maltech.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('seller1', 'seller@maltech.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller'),
('consumer1', 'buyer@maltech.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer');

-- Categories
INSERT INTO categories (name, slug) VALUES
('Graphics Cards', 'gpu'),
('Processors', 'cpu'),
('Memory', 'ram'),
('Storage', 'storage'),
('Peripherals', 'peripherals');

-- Sample Products (All approved for immediate UI testing)
INSERT INTO products (name, price, description, category_id, condition_status, location, seller_id, status) VALUES
('ASUS RTX 3080 OC', 299.99, 'Preloved GPU from personal build. Runs cool, no coil whine.', 1, 'Like New', 'Kuala Lumpur', 2, 'approved'),
('AMD Ryzen 7 5800X', 189.50, 'Excellent gaming CPU. Includes stock cooler.', 2, 'Good', 'Penang', 2, 'approved'),
('Corsair Vengeance 32GB DDR4', 79.99, '2x16GB 3200MHz kit. Zero issues.', 3, 'Like New', 'KL', 2, 'approved'),
('Samsung 970 EVO Plus 1TB', 65.00, 'NVMe SSD, 95% health remaining.', 4, 'Good', 'Selangor', 2, 'approved'),
('Keychron V2 Mechanical', 120.99, 'Hot-swappable, Gateron Brown switches.', 5, 'New', 'JB', 2, 'approved');