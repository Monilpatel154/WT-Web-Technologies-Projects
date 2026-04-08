CREATE DATABASE IF NOT EXISTS shoe_store3;
USE shoe_store3;

-- Products catalog
CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)    NOT NULL,
    image       VARCHAR(255)    NOT NULL,
    features    TEXT            NOT NULL,
    price       DECIMAL(10,2)   NOT NULL,
    sort_order  INT             NOT NULL DEFAULT 0
);

-- Customer orders
CREATE TABLE IF NOT EXISTS orders (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100)  NOT NULL,
    product_id   INT            NOT NULL,
    quantity     INT            NOT NULL DEFAULT 1,
    address      TEXT           NOT NULL,
    placed_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Seed products
INSERT INTO products (name, image, features, price, sort_order)
SELECT 'Nike Air Max', 'nike.jpg',
       'Lightweight|Air Cushion Technology|Perfect for Running',
       4999, 1
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Nike Air Max');

INSERT INTO products (name, image, features, price, sort_order)
SELECT 'Adidas Ultraboost', 'adidas.jpg',
       'High Performance|Comfortable Fit|Durable Sole',
       5499, 2
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Adidas Ultraboost');

INSERT INTO products (name, image, features, price, sort_order)
SELECT 'Puma Sneakers', 'puma.jpg',
       'Stylish Design|Daily Wear|Budget Friendly',
       3999, 3
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Puma Sneakers');
