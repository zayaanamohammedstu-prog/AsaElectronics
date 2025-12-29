-- Sample data for Asa Electronics
-- Run this after importing schema.sql

USE asa_electronics;

-- Insert admin user (password: admin123)
INSERT INTO users (email, password_hash, first_name, last_name, phone, role) VALUES
('admin@asaelectronics.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '+234 800 000 0001', 'admin');

-- Insert sample customers (password: password123)
INSERT INTO users (email, password_hash, first_name, last_name, phone, role) VALUES
('john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '+234 800 000 0002', 'customer'),
('jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', '+234 800 000 0003', 'customer'),
('mike.jones@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike', 'Jones', '+234 800 000 0004', 'customer');

-- Insert categories
INSERT INTO categories (name, description, slug) VALUES
('Laptops', 'High-performance laptops and notebooks for work and gaming', 'laptops'),
('Smartphones', 'Latest smartphones from top brands', 'smartphones'),
('Tablets', 'Tablets and iPads for productivity and entertainment', 'tablets'),
('Accessories', 'Tech accessories, cables, cases, and more', 'accessories'),
('Audio', 'Headphones, speakers, and audio equipment', 'audio'),
('Cameras', 'Digital cameras and photography equipment', 'cameras');

-- Insert sample products
INSERT INTO products (category_id, name, description, price, stock_quantity, sku, is_active) VALUES
-- Laptops
(1, 'Dell XPS 15', 'Powerful 15-inch laptop with Intel i7 processor, 16GB RAM, and 512GB SSD. Perfect for professionals and content creators.', 1250000.00, 15, 'DELL-XPS15-001', 1),
(1, 'MacBook Pro 14"', 'Apple M2 Pro chip, 16GB unified memory, 512GB SSD. The ultimate laptop for creative professionals.', 1850000.00, 8, 'APPLE-MBP14-001', 1),
(1, 'HP Pavilion 15', 'Budget-friendly laptop with AMD Ryzen 5, 8GB RAM, 256GB SSD. Great for students and everyday use.', 485000.00, 25, 'HP-PAV15-001', 1),
(1, 'Lenovo ThinkPad X1', 'Business-class laptop with Intel i7, 16GB RAM, 1TB SSD. Built for professionals who need reliability.', 1550000.00, 12, 'LENOVO-X1-001', 1),
(1, 'Asus ROG Strix', 'Gaming laptop with RTX 4060, Intel i7, 16GB RAM, 1TB SSD. Dominate your games with this powerhouse.', 1750000.00, 10, 'ASUS-ROG-001', 1),

-- Smartphones
(2, 'iPhone 15 Pro', 'Latest iPhone with A17 Pro chip, 256GB storage, ProMotion display. The most advanced iPhone ever.', 975000.00, 20, 'APPLE-IP15P-256', 1),
(2, 'Samsung Galaxy S24', 'Flagship Android with Snapdragon 8 Gen 3, 256GB storage, 120Hz display. Photography redefined.', 825000.00, 18, 'SAMSUNG-S24-256', 1),
(2, 'Google Pixel 8', 'Pure Android experience with Tensor G3, 128GB storage, amazing camera. Made by Google.', 625000.00, 15, 'GOOGLE-P8-128', 1),
(2, 'OnePlus 12', 'Flagship killer with Snapdragon 8 Gen 3, 256GB storage, 100W fast charging. Speed meets style.', 575000.00, 22, 'ONEPLUS-12-256', 1),
(2, 'Xiaomi 14 Pro', 'Premium Android with Leica cameras, 512GB storage, 120W charging. Photography excellence.', 685000.00, 16, 'XIAOMI-14P-512', 1),

-- Tablets
(3, 'iPad Pro 12.9"', 'Apple M2 chip, 256GB storage, Liquid Retina XDR display. The ultimate tablet experience.', 985000.00, 12, 'APPLE-IPP129-256', 1),
(3, 'Samsung Galaxy Tab S9', 'Android tablet with Snapdragon 8 Gen 2, 256GB storage, 120Hz display. Productivity on the go.', 625000.00, 14, 'SAMSUNG-TABS9-256', 1),
(3, 'iPad Air', 'M1 chip, 128GB storage, 10.9-inch display. Perfect balance of power and portability.', 525000.00, 18, 'APPLE-IPAIR-128', 1),

-- Accessories
(4, 'AirPods Pro 2', 'Active noise cancellation, spatial audio, USB-C charging. Premium wireless earbuds.', 215000.00, 30, 'APPLE-APP2-001', 1),
(4, 'Magic Keyboard', 'Wireless keyboard with numeric keypad. Perfect for Mac users.', 125000.00, 25, 'APPLE-MK-001', 1),
(4, 'USB-C Hub 7-in-1', 'Multi-port adapter with HDMI, USB 3.0, SD card reader. Essential for laptops.', 35000.00, 50, 'GENERIC-USBHUB-001', 1),
(4, 'Wireless Mouse', 'Ergonomic wireless mouse with precision tracking. Comfortable for all-day use.', 28000.00, 45, 'GENERIC-WMOUSE-001', 1),
(4, 'Laptop Stand', 'Aluminum laptop stand with adjustable height. Improve your posture and workspace.', 42000.00, 35, 'GENERIC-LPSTAND-001', 1),

-- Audio
(5, 'Sony WH-1000XM5', 'Industry-leading noise cancellation headphones. Exceptional sound quality.', 285000.00, 20, 'SONY-WH1000XM5-001', 1),
(5, 'Bose QuietComfort', 'Premium noise-cancelling headphones with legendary Bose sound. All-day comfort.', 275000.00, 18, 'BOSE-QC-001', 1),
(5, 'JBL Flip 6', 'Portable Bluetooth speaker with powerful sound. Perfect for outdoor adventures.', 95000.00, 30, 'JBL-FLIP6-001', 1),
(5, 'Apple HomePod Mini', 'Smart speaker with Siri integration. Fill your room with amazing sound.', 85000.00, 25, 'APPLE-HPMINI-001', 1),

-- Cameras
(6, 'Canon EOS R6', 'Full-frame mirrorless camera with 4K 60fps video. Professional photography made accessible.', 2850000.00, 5, 'CANON-R6-001', 1),
(6, 'Sony A7 IV', 'Versatile full-frame camera with 33MP sensor. The hybrid shooting powerhouse.', 2650000.00, 6, 'SONY-A7IV-001', 1),
(6, 'GoPro Hero 12', 'Action camera with 5.3K video, waterproof design. Capture your adventures.', 425000.00, 15, 'GOPRO-H12-001', 1),
(6, 'DJI Osmo Pocket 3', 'Compact gimbal camera with 4K video. Cinematic footage in your pocket.', 485000.00, 12, 'DJI-OP3-001', 1);

-- Insert sample addresses for customers
INSERT INTO addresses (user_id, address_line1, address_line2, city, state, country, postal_code, is_default) VALUES
(2, '45 Victoria Island Road', 'Apartment 12B', 'Lagos', 'Lagos', 'Nigeria', '101241', 1),
(3, '23 Independence Avenue', 'Floor 3', 'Abuja', 'FCT', 'Nigeria', '900001', 1),
(4, '78 Port Harcourt Street', '', 'Port Harcourt', 'Rivers', 'Nigeria', '500001', 1);

-- Insert sample orders (completed)
INSERT INTO orders (user_id, address_id, total_amount, status, payment_status, payment_reference, created_at) VALUES
(2, 1, 1250000.00, 'delivered', 'completed', 'ASA-1-' || UNIX_TIMESTAMP(), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 2, 975000.00, 'delivered', 'completed', 'ASA-2-' || UNIX_TIMESTAMP(), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(4, 3, 625000.00, 'shipped', 'completed', 'ASA-3-' || UNIX_TIMESTAMP(), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 1, 310000.00, 'processing', 'completed', 'ASA-4-' || UNIX_TIMESTAMP(), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 2, 95000.00, 'pending', 'pending', 'ASA-5-' || UNIX_TIMESTAMP(), NOW());

-- Insert order items
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
-- Order 1
(1, 1, 1, 1250000.00),
-- Order 2
(2, 6, 1, 975000.00),
-- Order 3
(3, 8, 1, 625000.00),
-- Order 4
(4, 14, 1, 215000.00),
(4, 21, 1, 95000.00),
-- Order 5
(5, 21, 1, 95000.00);

-- Success message
SELECT 'Sample data inserted successfully!' AS message;
SELECT COUNT(*) AS total_products FROM products;
SELECT COUNT(*) AS total_users FROM users;
SELECT COUNT(*) AS total_orders FROM orders;
