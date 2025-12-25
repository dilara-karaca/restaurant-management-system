DROP DATABASE IF EXISTS restaurant_db;

-- Yeni veritabanı oluştur
CREATE DATABASE restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurant_db;

-- ============================================
-- 1. ROLES TABLE
-- ============================================
CREATE TABLE Roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- 2. USERS TABLE
-- ============================================
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES Roles(role_id) ON DELETE RESTRICT,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- ============================================
-- 3. CUSTOMERS TABLE
-- ============================================
CREATE TABLE Customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- 4. PERSONNEL TABLE
-- ============================================
CREATE TABLE Personnel (
    personnel_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    position VARCHAR(50) NOT NULL,
    salary DECIMAL(10,2),
    hire_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- 5. TABLES (Restaurant Tables)
-- ============================================
CREATE TABLE Tables (
    table_id INT AUTO_INCREMENT PRIMARY KEY,
    table_number INT NOT NULL UNIQUE,
    capacity INT NOT NULL,
    status ENUM('Available', 'Occupied', 'Reserved') DEFAULT 'Available',
    location VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- 6. SUPPLIERS TABLE
-- ============================================
CREATE TABLE Suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(100) NOT NULL UNIQUE,
    contact_person VARCHAR(100),
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- 7. INGREDIENTS TABLE
-- ============================================
CREATE TABLE Ingredients (
    ingredient_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    ingredient_name VARCHAR(100) NOT NULL UNIQUE,
    unit VARCHAR(20) NOT NULL,
    unit_price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES Suppliers(supplier_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================
-- 8. STOCKS TABLE
-- ============================================
CREATE TABLE Stocks (
    stock_id INT AUTO_INCREMENT PRIMARY KEY,
    ingredient_id INT NOT NULL UNIQUE,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
    minimum_quantity DECIMAL(10,2) NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ingredient_id) REFERENCES Ingredients(ingredient_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- 8.1 STOCK MOVEMENTS TABLE
-- ============================================
CREATE TABLE StockMovements (
    movement_id INT AUTO_INCREMENT PRIMARY KEY,
    ingredient_id INT NOT NULL,
    movement_type ENUM('IN', 'OUT', 'USED') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ingredient_id) REFERENCES Ingredients(ingredient_id) ON DELETE CASCADE,
    INDEX idx_movement_date (created_at)
) ENGINE=InnoDB;

-- ============================================
-- 9. MENU CATEGORIES TABLE
-- ============================================
CREATE TABLE MenuCategories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- 10. MENU PRODUCTS TABLE
-- ============================================
CREATE TABLE MenuProducts (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES MenuCategories(category_id) ON DELETE RESTRICT,
    INDEX idx_category (category_id)
) ENGINE=InnoDB;

-- ============================================
-- 11. PRODUCT INGREDIENTS (Junction Table)
-- ============================================
CREATE TABLE ProductIngredients (
    product_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity_required DECIMAL(10,3) NOT NULL,
    PRIMARY KEY (product_id, ingredient_id),
    FOREIGN KEY (product_id) REFERENCES MenuProducts(product_id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES Ingredients(ingredient_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================
-- 12. ORDERS TABLE
-- ============================================
CREATE TABLE Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    table_id INT NOT NULL,
    served_by INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('Pending', 'Preparing', 'Served', 'Completed', 'Cancelled') DEFAULT 'Pending',
    payment_method ENUM('Cash', 'Credit Card', 'Debit Card', 'Mobile Payment'),
    FOREIGN KEY (customer_id) REFERENCES Customers(customer_id) ON DELETE RESTRICT,
    FOREIGN KEY (table_id) REFERENCES Tables(table_id) ON DELETE RESTRICT,
    FOREIGN KEY (served_by) REFERENCES Personnel(personnel_id) ON DELETE RESTRICT,
    INDEX idx_customer (customer_id),
    INDEX idx_order_date (order_date)
) ENGINE=InnoDB;

-- ============================================
-- 13. ORDER DETAILS TABLE
-- ============================================
CREATE TABLE OrderDetails (
    order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    special_instructions TEXT,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES MenuProducts(product_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================
-- SAMPLE DATA INSERTION
-- ============================================

-- 1. ROLES
INSERT INTO Roles (role_name, description) VALUES
('Admin', 'Full system access - can manage everything'),
('Manager', 'Restaurant manager - can view reports and manage staff'),
('Waiter', 'Waiter - can take orders and serve customers'),
('Customer', 'Regular customer - can place orders');

-- 2. USERS (Şifreler: "password123" için hash)
-- Gerçek projede password_hash() kullan!
INSERT INTO Users (role_id, username, password, email) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@restaurant.com'),
(2, 'manager1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager@restaurant.com'),
(3, 'waiter1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'waiter1@restaurant.com'),
(3, 'waiter2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'waiter2@restaurant.com'),
(3, 'waiter3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'waiter3@restaurant.com'),
(4, 'customer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ahmet.yilmaz@email.com'),
(4, 'customer2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ayse.kaya@email.com'),
(4, 'customer3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mehmet.demir@email.com'),
(4, 'customer4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'fatma.celik@email.com'),
(4, 'customer5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ali.ozturk@email.com');

-- 3. PERSONNEL
INSERT INTO Personnel (user_id, first_name, last_name, position, salary, hire_date) VALUES
(2, 'Ahmet', 'Yılmaz', 'Manager', 15000.00, '2023-01-15'),
(3, 'Mehmet', 'Kara', 'Waiter', 8000.00, '2023-03-20'),
(4, 'Ayşe', 'Demir', 'Waiter', 8000.00, '2023-05-10'),
(5, 'Fatma', 'Şahin', 'Waiter', 8500.00, '2023-02-28');

-- 4. CUSTOMERS
INSERT INTO Customers (user_id, first_name, last_name, phone, address) VALUES
(6, 'Ahmet', 'Yılmaz', '5551234567', 'Kadıköy, İstanbul'),
(7, 'Ayşe', 'Kaya', '5559876543', 'Beşiktaş, İstanbul'),
(8, 'Mehmet', 'Demir', '5555554444', 'Şişli, İstanbul'),
(9, 'Fatma', 'Çelik', '5553332211', 'Üsküdar, İstanbul'),
(10, 'Ali', 'Öztürk', '5556667788', 'Bakırköy, İstanbul');

-- 5. TABLES
INSERT INTO Tables (table_number, capacity, status, location) VALUES
(1, 2, 'Available', 'Window Side'),
(2, 4, 'Available', 'Main Hall'),
(3, 4, 'Available', 'Main Hall'),
(4, 6, 'Occupied', 'Main Hall'),
(5, 2, 'Available', 'Patio'),
(6, 8, 'Reserved', 'VIP Section'),
(7, 4, 'Available', 'Patio'),
(8, 2, 'Available', 'Bar Area');

-- 6. SUPPLIERS
INSERT INTO Suppliers (supplier_name, contact_person, phone, email, address) VALUES
('Fresh Produce Co.', 'Mehmet Öz', '02121234567', 'info@freshproduce.com', 'Bayrampaşa, İstanbul'),
('Quality Meats Ltd.', 'Ahmet Kaya', '02169876543', 'sales@qualitymeats.com', 'Pendik, İstanbul'),
('Dairy Delights', 'Ayşe Yılmaz', '02125556677', 'contact@dairydelights.com', 'Esenyurt, İstanbul'),
('Spice Masters', 'Fatma Demir', '02163334455', 'orders@spicemasters.com', 'Kartal, İstanbul');

-- 7. MENU CATEGORIES
INSERT INTO MenuCategories (category_name, description, display_order) VALUES
('Appetizers', 'Starters and small plates', 1),
('Soups', 'Hot and cold soups', 2),
('Main Courses', 'Main dishes and entrées', 3),
('Desserts', 'Sweet treats and desserts', 4),
('Beverages', 'Hot and cold drinks', 5);

-- 8. INGREDIENTS
INSERT INTO Ingredients (supplier_id, ingredient_name, unit, unit_price) VALUES
(1, 'Tomatoes', 'kg', 15.50),
(1, 'Lettuce', 'kg', 12.00),
(1, 'Onions', 'kg', 8.50),
(1, 'Potatoes', 'kg', 6.00),
(2, 'Chicken Breast', 'kg', 45.00),
(2, 'Beef Steak', 'kg', 180.00),
(2, 'Ground Beef', 'kg', 95.00),
(3, 'Mozzarella Cheese', 'kg', 85.00),
(3, 'Milk', 'liter', 18.00),
(3, 'Butter', 'kg', 120.00),
(4, 'Black Pepper', 'kg', 250.00),
(4, 'Salt', 'kg', 5.00),
(4, 'Olive Oil', 'liter', 95.00),
(1, 'Garlic', 'kg', 35.00),
(1, 'Bell Peppers', 'kg', 22.00);

-- 9. STOCKS
INSERT INTO Stocks (ingredient_id, quantity, minimum_quantity) VALUES
(1, 50.00, 10.00),
(2, 30.00, 5.00),
(3, 25.00, 8.00),
(4, 100.00, 20.00),
(5, 40.00, 10.00),
(6, 25.00, 5.00),
(7, 35.00, 10.00),
(8, 20.00, 5.00),
(9, 60.00, 15.00),
(10, 15.00, 5.00),
(11, 3.00, 1.00),
(12, 10.00, 2.00),
(13, 25.00, 5.00),
(14, 8.00, 2.00),
(15, 18.00, 5.00);

-- 10. MENU PRODUCTS
INSERT INTO MenuProducts (category_id, product_name, description, price, is_available) VALUES
-- Appetizers
(1, 'Caesar Salad', 'Fresh romaine lettuce with parmesan and croutons', 65.00, TRUE),
(1, 'Bruschetta', 'Toasted bread with tomatoes and garlic', 55.00, TRUE),
(1, 'Chicken Wings', 'Spicy buffalo wings with ranch dressing', 75.00, TRUE),
(1, 'French Fries', 'Crispy golden fries with ketchup', 45.00, TRUE),

-- Soups
(2, 'Tomato Soup', 'Creamy tomato soup with basil', 50.00, TRUE),
(2, 'Chicken Soup', 'Homemade chicken soup with vegetables', 60.00, TRUE),

-- Main Courses
(3, 'Grilled Chicken', 'Marinated grilled chicken with vegetables', 140.00, TRUE),
(3, 'Beef Steak', 'Premium beef steak cooked to perfection', 280.00, TRUE),
(3, 'Spaghetti Bolognese', 'Classic pasta with meat sauce', 95.00, TRUE),
(3, 'Margherita Pizza', 'Fresh mozzarella, tomato sauce, and basil', 110.00, TRUE),
(3, 'Cheeseburger', 'Juicy burger with cheese and fries', 120.00, TRUE),

-- Desserts
(4, 'Tiramisu', 'Classic Italian dessert with coffee', 70.00, TRUE),
(4, 'Chocolate Cake', 'Rich chocolate cake with ice cream', 65.00, TRUE),
(4, 'Cheesecake', 'New York style cheesecake', 75.00, TRUE),

-- Beverages
(5, 'Coca Cola', 'Cold soft drink (330ml)', 25.00, TRUE),
(5, 'Fresh Orange Juice', 'Freshly squeezed orange juice', 40.00, TRUE),
(5, 'Turkish Coffee', 'Traditional Turkish coffee', 30.00, TRUE),
(5, 'Cappuccino', 'Italian coffee with steamed milk', 45.00, TRUE);

-- 11. PRODUCT INGREDIENTS (Recipe relations)
-- Caesar Salad
INSERT INTO ProductIngredients (product_id, ingredient_id, quantity_required) VALUES
(1, 2, 0.150), -- Lettuce
(1, 8, 0.030), -- Mozzarella Cheese
(1, 13, 0.010); -- Olive Oil

-- Bruschetta
INSERT INTO ProductIngredients (product_id, ingredient_id, quantity_required) VALUES
(2, 1, 0.100), -- Tomatoes
(2, 14, 0.020), -- Garlic
(2, 13, 0.015); -- Olive Oil

-- Chicken Wings
INSERT INTO ProductIngredients (product_id, ingredient_id, quantity_required) VALUES
(3, 5, 0.300), -- Chicken Breast
(3, 11, 0.005), -- Black Pepper
(3, 12, 0.003); -- Salt

-- Grilled Chicken
INSERT INTO ProductIngredients (product_id, ingredient_id, quantity_required) VALUES
(7, 5, 0.250), -- Chicken Breast
(7, 13, 0.020), -- Olive Oil
(7, 11, 0.005); -- Black Pepper

-- Beef Steak
INSERT INTO ProductIngredients (product_id, ingredient_id, quantity_required) VALUES
(8, 6, 0.300), -- Beef Steak
(8, 10, 0.015), -- Butter
(8, 14, 0.010); -- Garlic

-- Cheeseburger
INSERT INTO ProductIngredients (product_id, ingredient_id, quantity_required) VALUES
(11, 7, 0.200), -- Ground Beef
(11, 8, 0.050), -- Mozzarella Cheese
(11, 1, 0.050), -- Tomatoes
(11, 2, 0.030); -- Lettuce

-- 12. SAMPLE ORDERS
INSERT INTO Orders (customer_id, table_id, served_by, total_amount, status, payment_method) VALUES
(1, 4, 2, 375.00, 'Completed', 'Credit Card'),
(2, 2, 3, 220.00, 'Served', 'Cash'),
(3, 1, 4, 185.00, 'Preparing', 'Mobile Payment'),
(4, 7, NULL, 450.00, 'Pending', NULL),
(5, 5, 3, 160.00, 'Completed', 'Debit Card'),
(1, 3, NULL, 160.00, 'Pending', NULL),
(2, 6, NULL, 225.00, 'Pending', NULL),
(3, 8, 2, 170.00, 'Preparing', NULL);

-- 13. ORDER DETAILS
-- Order 1
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(1, 1, 1, 65.00, 65.00),
(1, 7, 2, 140.00, 280.00),
(1, 15, 2, 25.00, 50.00);

-- Order 2
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(2, 3, 1, 75.00, 75.00),
(2, 9, 1, 95.00, 95.00),
(2, 17, 2, 45.00, 90.00);

-- Order 3
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(3, 8, 1, 280.00, 280.00),
(3, 12, 1, 70.00, 70.00);

-- Order 4
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(4, 10, 2, 110.00, 220.00),
(4, 14, 2, 65.00, 130.00),
(4, 16, 2, 40.00, 80.00);

-- Order 5
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(5, 11, 1, 120.00, 120.00),
(5, 15, 2, 25.00, 50.00);

-- Order 6 (Unassigned)
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(6, 4, 1, 90.00, 90.00),
(6, 10, 2, 35.00, 70.00);

-- Order 7 (Unassigned)
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(7, 6, 1, 120.00, 120.00),
(7, 12, 1, 55.00, 55.00),
(7, 18, 2, 25.00, 50.00);

-- Order 8
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(8, 5, 1, 110.00, 110.00),
(8, 13, 1, 60.00, 60.00);

-- 14. EXTRA SAMPLE ORDERS (Assigned + Unassigned)
INSERT INTO Orders (customer_id, table_id, served_by, total_amount, status, payment_method) VALUES
(1, 2, 2, 150.00, 'Pending', NULL),
(2, 3, 3, 215.00, 'Preparing', NULL),
(3, 4, 4, 180.00, 'Served', 'Cash'),
(4, 5, 2, 260.00, 'Completed', 'Credit Card'),
(5, 6, 3, 140.00, 'Pending', NULL),
(1, 7, 4, 205.00, 'Preparing', NULL),
(2, 8, 2, 175.00, 'Served', 'Debit Card'),
(3, 1, 3, 230.00, 'Completed', 'Mobile Payment'),
(4, 2, 4, 155.00, 'Pending', NULL),
(5, 3, 2, 190.00, 'Preparing', NULL),
(1, 4, NULL, 160.00, 'Pending', NULL),
(2, 5, NULL, 225.00, 'Pending', NULL),
(3, 6, NULL, 210.00, 'Preparing', NULL),
(4, 7, NULL, 195.00, 'Pending', NULL),
(5, 8, NULL, 240.00, 'Served', NULL),
(1, 1, NULL, 170.00, 'Pending', NULL),
(2, 2, NULL, 200.00, 'Preparing', NULL),
(3, 3, NULL, 185.00, 'Pending', NULL),
(4, 4, NULL, 230.00, 'Served', NULL),
(5, 5, NULL, 150.00, 'Pending', NULL);

-- Order 9
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(9, 1, 1, 65.00, 65.00),
(9, 7, 1, 85.00, 85.00);

-- Order 10
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(10, 3, 1, 75.00, 75.00),
(10, 9, 1, 95.00, 95.00),
(10, 17, 1, 45.00, 45.00);

-- Order 11
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(11, 10, 1, 110.00, 110.00),
(11, 16, 1, 40.00, 40.00),
(11, 15, 1, 30.00, 30.00);

-- Order 12
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(12, 8, 1, 280.00, 280.00),
(12, 18, 2, 25.00, 50.00);

-- Order 13
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(13, 6, 1, 120.00, 120.00),
(13, 15, 1, 20.00, 20.00);

-- Order 14
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(14, 5, 1, 110.00, 110.00),
(14, 12, 1, 70.00, 70.00),
(14, 19, 1, 25.00, 25.00);

-- Order 15
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(15, 2, 1, 85.00, 85.00),
(15, 14, 1, 65.00, 65.00),
(15, 17, 1, 25.00, 25.00);

-- Order 16
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(16, 4, 1, 90.00, 90.00),
(16, 13, 1, 60.00, 60.00),
(16, 18, 2, 25.00, 50.00);

-- Order 17
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(17, 11, 1, 120.00, 120.00),
(17, 15, 1, 35.00, 35.00);

-- Order 18
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(18, 9, 1, 95.00, 95.00),
(18, 14, 1, 60.00, 60.00);

-- Order 19 (Unassigned)
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(19, 1, 1, 65.00, 65.00),
(19, 12, 1, 70.00, 70.00),
(19, 15, 1, 25.00, 25.00);

-- Order 20 (Unassigned)
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(20, 3, 1, 75.00, 75.00),
(20, 9, 1, 95.00, 95.00),
(20, 17, 1, 45.00, 45.00),
(20, 18, 1, 10.00, 10.00);

-- Order 21 (Unassigned)
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(21, 4, 1, 90.00, 90.00),
(21, 10, 1, 110.00, 110.00),
(21, 19, 1, 10.00, 10.00);

-- Order 22 (Unassigned)
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(22, 5, 1, 110.00, 110.00),
(22, 13, 1, 60.00, 60.00),
(22, 18, 1, 25.00, 25.00);

-- Order 23 (Unassigned)
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(23, 6, 1, 120.00, 120.00),
(23, 14, 1, 65.00, 65.00),
(23, 15, 1, 25.00, 25.00),
(23, 17, 1, 30.00, 30.00);

-- Order 24 (Unassigned)
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(24, 7, 1, 85.00, 85.00),
(24, 12, 1, 70.00, 70.00),
(24, 19, 1, 15.00, 15.00);

-- Order 25 (Unassigned)
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(25, 8, 1, 280.00, 280.00),
(25, 18, 1, 25.00, 25.00);

-- Order 26 (Unassigned)
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(26, 9, 1, 95.00, 95.00),
(26, 10, 1, 110.00, 110.00);

-- Order 27 (Unassigned)
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(27, 11, 1, 120.00, 120.00),
(27, 13, 1, 60.00, 60.00),
(27, 15, 1, 5.00, 5.00);

-- Order 28 (Unassigned)
INSERT INTO OrderDetails (order_id, product_id, quantity, unit_price, subtotal) VALUES
(28, 2, 1, 85.00, 85.00),
(28, 14, 1, 65.00, 65.00);

-- ============================================
-- STORED PROCEDURES & TRIGGERS (ASSIGNMENT)
-- ============================================
-- Not: Bu blok 6, 7 ve 8. maddeler için hazırlanmıştır.

DELIMITER $$

-- SP-1: Sipariş genel raporu (Orders + Customers + Tables + Personnel + OrderDetails)
DROP PROCEDURE IF EXISTS sp_report_orders_overview$$
CREATE PROCEDURE sp_report_orders_overview(IN p_status VARCHAR(20))
BEGIN
    SELECT
        o.order_id,
        o.order_date,
        o.status,
        o.total_amount,
        t.table_number,
        CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
        CONCAT(p.first_name, ' ', p.last_name) AS waiter_name,
        COUNT(od.order_detail_id) AS item_count
    FROM Orders o
    JOIN Tables t ON o.table_id = t.table_id
    JOIN Customers c ON o.customer_id = c.customer_id
    LEFT JOIN Personnel p ON o.served_by = p.personnel_id
    LEFT JOIN OrderDetails od ON o.order_id = od.order_id
    WHERE (p_status IS NULL OR p_status = '' OR o.status = p_status)
    GROUP BY o.order_id, o.order_date, o.status, o.total_amount, t.table_number, c.first_name, c.last_name, p.first_name, p.last_name
    ORDER BY o.order_date DESC
    LIMIT 100; -- Trafiği azaltmak için limit
END$$

-- SP-2: Sipariş kalem raporu (Orders + OrderDetails + MenuProducts + MenuCategories)
DROP PROCEDURE IF EXISTS sp_report_order_items$$
CREATE PROCEDURE sp_report_order_items(IN p_order_id INT)
BEGIN
    SELECT
        o.order_id,
        o.order_date,
        mc.category_name,
        mp.product_name,
        od.quantity,
        od.unit_price,
        od.subtotal
    FROM Orders o
    JOIN OrderDetails od ON o.order_id = od.order_id
    JOIN MenuProducts mp ON od.product_id = mp.product_id
    JOIN MenuCategories mc ON mp.category_id = mc.category_id
    WHERE (p_order_id IS NULL OR p_order_id = 0 OR o.order_id = p_order_id)
    ORDER BY o.order_date DESC, od.order_detail_id ASC
    LIMIT 200;
END$$

-- SP-3: Stok özeti raporu (Stocks + Ingredients + Suppliers)
DROP PROCEDURE IF EXISTS sp_report_stock_summary$$
CREATE PROCEDURE sp_report_stock_summary()
BEGIN
    SELECT
        s.stock_id,
        i.ingredient_name,
        i.unit,
        s.quantity,
        s.minimum_quantity,
        sup.supplier_name
    FROM Stocks s
    JOIN Ingredients i ON s.ingredient_id = i.ingredient_id
    LEFT JOIN Suppliers sup ON i.supplier_id = sup.supplier_id
    ORDER BY i.ingredient_name ASC;
END$$

-- SP-4: Stok hareket raporu (StockMovements + Ingredients + Suppliers)
DROP PROCEDURE IF EXISTS sp_report_stock_movements$$
CREATE PROCEDURE sp_report_stock_movements(
    IN p_ingredient_id INT,
    IN p_movement_type VARCHAR(10),
    IN p_date_from DATE,
    IN p_date_to DATE,
    IN p_limit INT
)
BEGIN
    DECLARE v_limit INT DEFAULT 50;
    SET v_limit = IFNULL(p_limit, 50);
    IF v_limit <= 0 THEN
        SET v_limit = 50;
    END IF;

    SELECT
        sm.movement_id,
        sm.movement_type,
        sm.quantity,
        sm.note,
        sm.created_at,
        i.ingredient_name,
        sup.supplier_name
    FROM StockMovements sm
    JOIN Ingredients i ON sm.ingredient_id = i.ingredient_id
    LEFT JOIN Suppliers sup ON i.supplier_id = sup.supplier_id
    WHERE (p_ingredient_id IS NULL OR p_ingredient_id = 0 OR sm.ingredient_id = p_ingredient_id)
      AND (p_movement_type IS NULL OR p_movement_type = '' OR sm.movement_type = p_movement_type)
      AND (p_date_from IS NULL OR DATE(sm.created_at) >= p_date_from)
      AND (p_date_to IS NULL OR DATE(sm.created_at) <= p_date_to)
    ORDER BY sm.created_at DESC
    LIMIT v_limit;
END$$

-- SP-5: Menü reçete raporu (MenuProducts + MenuCategories + ProductIngredients + Ingredients)
DROP PROCEDURE IF EXISTS sp_report_menu_composition$$
CREATE PROCEDURE sp_report_menu_composition(IN p_category_id INT)
BEGIN
    SELECT
        mp.product_id,
        mp.product_name,
        mc.category_name,
        i.ingredient_name,
        pi.quantity_required,
        i.unit
    FROM MenuProducts mp
    JOIN MenuCategories mc ON mp.category_id = mc.category_id
    JOIN ProductIngredients pi ON mp.product_id = pi.product_id
    JOIN Ingredients i ON pi.ingredient_id = i.ingredient_id
    WHERE (p_category_id IS NULL OR p_category_id = 0 OR mp.category_id = p_category_id)
    ORDER BY mc.category_name, mp.product_name;
END$$

-- SP-6: Müşteri sipariş geçmişi (Customers + Orders + OrderDetails + MenuProducts)
DROP PROCEDURE IF EXISTS sp_report_customer_history$$
CREATE PROCEDURE sp_report_customer_history(IN p_customer_id INT)
BEGIN
    SELECT
        c.customer_id,
        CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
        o.order_id,
        o.order_date,
        mp.product_name,
        od.quantity,
        od.subtotal
    FROM Customers c
    JOIN Orders o ON c.customer_id = o.customer_id
    JOIN OrderDetails od ON o.order_id = od.order_id
    JOIN MenuProducts mp ON od.product_id = mp.product_id
    WHERE (p_customer_id IS NULL OR p_customer_id = 0 OR c.customer_id = p_customer_id)
    ORDER BY o.order_date DESC
    LIMIT 200;
END$$

-- SP-7: Personel performans raporu (Personnel + Orders + OrderDetails + MenuProducts)
DROP PROCEDURE IF EXISTS sp_report_personnel_performance$$
CREATE PROCEDURE sp_report_personnel_performance(IN p_personnel_id INT)
BEGIN
    SELECT
        p.personnel_id,
        CONCAT(p.first_name, ' ', p.last_name) AS personnel_name,
        COUNT(DISTINCT o.order_id) AS total_orders,
        SUM(od.subtotal) AS total_sales,
        COUNT(od.order_detail_id) AS total_items
    FROM Personnel p
    JOIN Orders o ON p.personnel_id = o.served_by
    JOIN OrderDetails od ON o.order_id = od.order_id
    JOIN MenuProducts mp ON od.product_id = mp.product_id
    WHERE (p_personnel_id IS NULL OR p_personnel_id = 0 OR p.personnel_id = p_personnel_id)
    GROUP BY p.personnel_id, p.first_name, p.last_name
    ORDER BY total_sales DESC;
END$$

-- SP-8: Kategori listesi (tek tablo)
DROP PROCEDURE IF EXISTS sp_list_categories$$
CREATE PROCEDURE sp_list_categories()
BEGIN
    SELECT category_id, category_name, description, display_order, created_at
    FROM MenuCategories
    ORDER BY display_order ASC, category_name ASC;
END$$

-- SP-9: Menü ürün listesi (tek tablo)
DROP PROCEDURE IF EXISTS sp_list_menu_products$$
CREATE PROCEDURE sp_list_menu_products(IN p_category_id INT)
BEGIN
    SELECT product_id, category_id, product_name, description, price, is_available, image_url, created_at
    FROM MenuProducts
    WHERE (p_category_id IS NULL OR p_category_id = 0 OR category_id = p_category_id)
    ORDER BY product_name ASC;
END$$

-- SP-10: Menü ürün malzemeleri (ProductIngredients + Ingredients)
DROP PROCEDURE IF EXISTS sp_list_menu_ingredients$$
CREATE PROCEDURE sp_list_menu_ingredients(IN p_product_id INT)
BEGIN
    SELECT pi.ingredient_id, i.ingredient_name, pi.quantity_required
    FROM ProductIngredients pi
    JOIN Ingredients i ON pi.ingredient_id = i.ingredient_id
    WHERE (p_product_id IS NULL OR p_product_id = 0 OR pi.product_id = p_product_id);
END$$

-- SP-11: Malzeme listesi (Ingredients + Suppliers)
DROP PROCEDURE IF EXISTS sp_list_ingredients$$
CREATE PROCEDURE sp_list_ingredients()
BEGIN
    SELECT i.ingredient_id, i.ingredient_name, i.unit, i.unit_price, s.supplier_name
    FROM Ingredients i
    LEFT JOIN Suppliers s ON i.supplier_id = s.supplier_id
    ORDER BY i.ingredient_name ASC;
END$$

-- SP-12: Tedarikçi listesi (tek tablo)
DROP PROCEDURE IF EXISTS sp_list_suppliers$$
CREATE PROCEDURE sp_list_suppliers()
BEGIN
    SELECT supplier_id, supplier_name, contact_person, phone, email, address, created_at
    FROM Suppliers
    ORDER BY supplier_name ASC;
END$$

-- SP-13: Stok listesi (Stocks + Ingredients + Suppliers)
DROP PROCEDURE IF EXISTS sp_list_stocks$$
CREATE PROCEDURE sp_list_stocks()
BEGIN
    SELECT s.stock_id, s.ingredient_id, i.ingredient_name, i.unit, i.unit_price, s.quantity, s.minimum_quantity, s.last_updated, sup.supplier_name
    FROM Stocks s
    JOIN Ingredients i ON s.ingredient_id = i.ingredient_id
    LEFT JOIN Suppliers sup ON i.supplier_id = sup.supplier_id
    ORDER BY i.ingredient_name ASC;
END$$

-- SP-14: Sipariş listesi (Orders + Tables + Customers + Personnel)
DROP PROCEDURE IF EXISTS sp_list_orders$$
CREATE PROCEDURE sp_list_orders(IN p_status VARCHAR(20))
BEGIN
    SELECT
        o.order_id,
        o.order_date,
        o.total_amount,
        o.status,
        o.payment_method,
        t.table_number,
        c.first_name AS customer_first_name,
        c.last_name AS customer_last_name,
        p.first_name AS waiter_first_name,
        p.last_name AS waiter_last_name
    FROM Orders o
    JOIN Tables t ON o.table_id = t.table_id
    JOIN Customers c ON o.customer_id = c.customer_id
    LEFT JOIN Personnel p ON o.served_by = p.personnel_id
    WHERE (p_status IS NULL OR p_status = '' OR o.status = p_status)
    ORDER BY o.order_date DESC;
END$$

-- SP-15: Personel sipariş listesi (Orders + Tables + Customers)
DROP PROCEDURE IF EXISTS sp_list_orders_by_personnel$$
CREATE PROCEDURE sp_list_orders_by_personnel(IN p_personnel_id INT, IN p_status VARCHAR(20))
BEGIN
    SELECT
        o.order_id,
        o.order_date,
        o.total_amount,
        o.status,
        o.payment_method,
        t.table_number,
        c.first_name AS customer_first_name,
        c.last_name AS customer_last_name
    FROM Orders o
    JOIN Tables t ON o.table_id = t.table_id
    JOIN Customers c ON o.customer_id = c.customer_id
    WHERE o.served_by = p_personnel_id
      AND (p_status IS NULL OR p_status = '' OR o.status = p_status)
    ORDER BY o.order_date DESC;
END$$

-- SP-16: Atanmamış sipariş listesi (Orders + Tables + Customers)
DROP PROCEDURE IF EXISTS sp_list_orders_unassigned$$
CREATE PROCEDURE sp_list_orders_unassigned(IN p_status VARCHAR(20))
BEGIN
    SELECT
        o.order_id,
        o.order_date,
        o.total_amount,
        o.status,
        o.payment_method,
        t.table_number,
        c.first_name AS customer_first_name,
        c.last_name AS customer_last_name
    FROM Orders o
    JOIN Tables t ON o.table_id = t.table_id
    JOIN Customers c ON o.customer_id = c.customer_id
    WHERE o.served_by IS NULL
      AND (p_status IS NULL OR p_status = '' OR o.status = p_status)
    ORDER BY o.order_date DESC;
END$$

-- SP-17: Sipariş kalemleri (OrderDetails + MenuProducts)
DROP PROCEDURE IF EXISTS sp_list_order_items_for_orders$$
CREATE PROCEDURE sp_list_order_items_for_orders(IN p_order_ids TEXT)
BEGIN
    SELECT
        od.order_detail_id,
        od.order_id,
        od.product_id,
        mp.product_name,
        od.quantity,
        od.unit_price,
        od.subtotal,
        od.special_instructions
    FROM OrderDetails od
    JOIN MenuProducts mp ON od.product_id = mp.product_id
    WHERE FIND_IN_SET(od.order_id, p_order_ids)
    ORDER BY od.order_detail_id ASC;
END$$

DELIMITER ;

-- ============================================
-- TRIGGERS (ASSIGNMENT)
-- ============================================
-- TR-1: Sipariş kalemi eklenince toplam ve stok güncelle
DELIMITER $$
DROP TRIGGER IF EXISTS trg_orderdetails_ai$$
CREATE TRIGGER trg_orderdetails_ai
AFTER INSERT ON OrderDetails
FOR EACH ROW
BEGIN
    -- Sipariş toplamını güncelle
    UPDATE Orders
    SET total_amount = (
        SELECT COALESCE(SUM(subtotal), 0)
        FROM OrderDetails
        WHERE order_id = NEW.order_id
    )
    WHERE order_id = NEW.order_id;

    -- Stoktan düş ve hareket kaydı oluştur
    UPDATE Stocks s
    JOIN ProductIngredients pi ON s.ingredient_id = pi.ingredient_id
    SET s.quantity = s.quantity - (pi.quantity_required * NEW.quantity)
    WHERE pi.product_id = NEW.product_id;

    INSERT INTO StockMovements (ingredient_id, movement_type, quantity, note)
    SELECT
        pi.ingredient_id,
        'USED',
        (pi.quantity_required * NEW.quantity),
        CONCAT('Order #', NEW.order_id)
    FROM ProductIngredients pi
    WHERE pi.product_id = NEW.product_id;
END$$

-- TR-2: Sipariş kalemi güncellenince toplam güncelle
DROP TRIGGER IF EXISTS trg_orderdetails_au$$
CREATE TRIGGER trg_orderdetails_au
AFTER UPDATE ON OrderDetails
FOR EACH ROW
BEGIN
    UPDATE Orders
    SET total_amount = (
        SELECT COALESCE(SUM(subtotal), 0)
        FROM OrderDetails
        WHERE order_id = NEW.order_id
    )
    WHERE order_id = NEW.order_id;
END$$

-- TR-3: Sipariş kalemi silinince toplam güncelle
DROP TRIGGER IF EXISTS trg_orderdetails_ad$$
CREATE TRIGGER trg_orderdetails_ad
AFTER DELETE ON OrderDetails
FOR EACH ROW
BEGIN
    UPDATE Orders
    SET total_amount = (
        SELECT COALESCE(SUM(subtotal), 0)
        FROM OrderDetails
        WHERE order_id = OLD.order_id
    )
    WHERE order_id = OLD.order_id;
END$$
DELIMITER ;

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Tablo sayısını kontrol et
SELECT 'Total Tables Created' AS Info, COUNT(*) AS Count 
FROM information_schema.tables 
WHERE table_schema = 'restaurant_db';

-- Her tablodaki kayıt sayısını göster
SELECT 'Roles' AS TableName, COUNT(*) AS RecordCount FROM Roles
UNION ALL
SELECT 'Users', COUNT(*) FROM Users
UNION ALL
SELECT 'Customers', COUNT(*) FROM Customers
UNION ALL
SELECT 'Personnel', COUNT(*) FROM Personnel
UNION ALL
SELECT 'Tables', COUNT(*) FROM Tables
UNION ALL
SELECT 'Suppliers', COUNT(*) FROM Suppliers
UNION ALL
SELECT 'Ingredients', COUNT(*) FROM Ingredients
UNION ALL
SELECT 'Stocks', COUNT(*) FROM Stocks
UNION ALL
SELECT 'MenuCategories', COUNT(*) FROM MenuCategories
UNION ALL
SELECT 'MenuProducts', COUNT(*) FROM MenuProducts
UNION ALL
SELECT 'ProductIngredients', COUNT(*) FROM ProductIngredients
UNION ALL
SELECT 'Orders', COUNT(*) FROM Orders
UNION ALL
SELECT 'OrderDetails', COUNT(*) FROM OrderDetails;
