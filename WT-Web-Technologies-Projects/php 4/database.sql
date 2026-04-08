-- Database for PATEL Premium Shoe Store (php 4)
CREATE DATABASE IF NOT EXISTS shoe_store4;
USE shoe_store4;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    image VARCHAR(255) DEFAULT '',
    features TEXT NOT NULL,
    price INT NOT NULL,
    sort_order INT DEFAULT 0
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    address TEXT NOT NULL,
    placed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Seed products (features as pipe-separated string)
INSERT INTO products (name, image, features, price, sort_order) VALUES
('Nike Air Max',      'nike.jpg',   'Lightweight|Air Cushion Technology|Perfect for Running|Breathable Mesh Upper',    4999, 1),
('Adidas Ultraboost', 'adidas.jpg', 'High Performance|Comfortable Fit|Durable Sole|Boost Energy Return',               5499, 2),
('Puma Sneakers',     'puma.jpg',   'Stylish Design|Daily Wear|Budget Friendly|SOFTFOAM+ Sockliner',                   3999, 3);
