CREATE DATABASE IF NOT EXISTS shoe_store;
USE shoe_store;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    brand VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    old_price DECIMAL(10,2) DEFAULT NULL,
    image VARCHAR(255) NOT NULL,
    is_new TINYINT(1) NOT NULL DEFAULT 0,
    on_sale TINYINT(1) NOT NULL DEFAULT 0,
    sale_badge VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(128) NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_session_product (session_id, product_id),
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

INSERT INTO products (name, brand, price, old_price, image, is_new, on_sale, sale_badge)
SELECT 'Nike Air Max', 'Nike', 4999, 5999, 'nike.jpg', 0, 1, '20% OFF'
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Nike Air Max');

INSERT INTO products (name, brand, price, old_price, image, is_new, on_sale, sale_badge)
SELECT 'Adidas Ultraboost 2026', 'Adidas', 5499, NULL, 'adidas.jpg', 1, 0, NULL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Adidas Ultraboost 2026');

INSERT INTO products (name, brand, price, old_price, image, is_new, on_sale, sale_badge)
SELECT 'Puma Sneakers', 'Puma', 3999, NULL, 'puma.jpg', 0, 0, NULL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Puma Sneakers');
