-- Sample Products Data
-- This file contains sample products to populate the database for testing

USE asa_electronics;

-- Insert sample products for Smartphones category
INSERT INTO products (category_id, name, description, price, stock_quantity, image_url, sku, is_active) VALUES
(1, 'iPhone 15 Pro', 'Latest flagship iPhone with A17 Pro chip, titanium design, and advanced camera system', 999.99, 50, 'https://via.placeholder.com/400x400?text=iPhone+15+Pro', 'IPH15PRO-128', 1),
(1, 'Samsung Galaxy S24 Ultra', 'Premium Android phone with S Pen, 200MP camera, and stunning display', 1199.99, 40, 'https://via.placeholder.com/400x400?text=Galaxy+S24', 'SGS24U-256', 1),
(1, 'Google Pixel 8 Pro', 'Pure Android experience with exceptional camera and AI features', 899.99, 35, 'https://via.placeholder.com/400x400?text=Pixel+8+Pro', 'GPX8P-128', 1),
(1, 'OnePlus 12', 'Flagship killer with Snapdragon 8 Gen 3 and fast charging', 799.99, 30, 'https://via.placeholder.com/400x400?text=OnePlus+12', 'OP12-256', 1);

-- Insert sample products for Laptops category
INSERT INTO products (category_id, name, description, price, stock_quantity, image_url, sku, is_active) VALUES
(2, 'MacBook Pro 16" M3', 'Powerful laptop for professionals with M3 chip and stunning display', 2499.99, 25, 'https://via.placeholder.com/400x400?text=MacBook+Pro', 'MBP16-M3-512', 1),
(2, 'Dell XPS 15', 'Premium Windows laptop with InfinityEdge display and powerful performance', 1799.99, 30, 'https://via.placeholder.com/400x400?text=Dell+XPS+15', 'DXPS15-512', 1),
(2, 'HP Spectre x360', 'Versatile 2-in-1 laptop with stunning design and long battery life', 1499.99, 20, 'https://via.placeholder.com/400x400?text=HP+Spectre', 'HPSX360-512', 1),
(2, 'Lenovo ThinkPad X1 Carbon', 'Business laptop with excellent keyboard and durability', 1699.99, 28, 'https://via.placeholder.com/400x400?text=ThinkPad+X1', 'TPX1C-512', 1);

-- Insert sample products for Tablets category
INSERT INTO products (category_id, name, description, price, stock_quantity, image_url, sku, is_active) VALUES
(3, 'iPad Pro 12.9"', 'Professional tablet with M2 chip and ProMotion display', 1099.99, 35, 'https://via.placeholder.com/400x400?text=iPad+Pro', 'IPADP12-256', 1),
(3, 'Samsung Galaxy Tab S9', 'Premium Android tablet with S Pen and DeX mode', 799.99, 40, 'https://via.placeholder.com/400x400?text=Galaxy+Tab', 'SGTS9-256', 1),
(3, 'Microsoft Surface Pro 9', 'Versatile 2-in-1 tablet that runs full Windows', 999.99, 25, 'https://via.placeholder.com/400x400?text=Surface+Pro', 'MSFP9-256', 1);

-- Insert sample products for Accessories category
INSERT INTO products (category_id, name, description, price, stock_quantity, image_url, sku, is_active) VALUES
(4, 'Apple AirPods Pro 2', 'Premium wireless earbuds with active noise cancellation', 249.99, 100, 'https://via.placeholder.com/400x400?text=AirPods+Pro', 'AAPP2-WHT', 1),
(4, 'Anker PowerBank 20000mAh', 'High-capacity portable charger for all your devices', 49.99, 150, 'https://via.placeholder.com/400x400?text=PowerBank', 'ANKPB-20K', 1),
(4, 'Logitech MX Master 3S', 'Premium wireless mouse for productivity', 99.99, 80, 'https://via.placeholder.com/400x400?text=MX+Master', 'LGMXM3S-BLK', 1),
(4, 'USB-C Hub 7-in-1', 'Versatile hub with multiple ports for modern laptops', 39.99, 120, 'https://via.placeholder.com/400x400?text=USB-C+Hub', 'USBC-HUB7', 1);

-- Insert sample products for Audio category
INSERT INTO products (category_id, name, description, price, stock_quantity, image_url, sku, is_active) VALUES
(5, 'Sony WH-1000XM5', 'Industry-leading noise cancelling headphones', 399.99, 60, 'https://via.placeholder.com/400x400?text=Sony+XM5', 'SNXM5-BLK', 1),
(5, 'Bose QuietComfort 45', 'Premium wireless headphones with excellent ANC', 329.99, 50, 'https://via.placeholder.com/400x400?text=Bose+QC45', 'BQCF45-BLK', 1),
(5, 'JBL Flip 6', 'Portable Bluetooth speaker with powerful sound', 129.99, 90, 'https://via.placeholder.com/400x400?text=JBL+Flip+6', 'JBLF6-BLK', 1),
(5, 'Sonos One SL', 'Smart speaker for multi-room audio', 219.99, 45, 'https://via.placeholder.com/400x400?text=Sonos+One', 'SNOS1-WHT', 1);

-- Insert sample products for Smart Home category
INSERT INTO products (category_id, name, description, price, stock_quantity, image_url, sku, is_active) VALUES
(6, 'Amazon Echo Dot 5th Gen', 'Smart speaker with Alexa voice assistant', 49.99, 100, 'https://via.placeholder.com/400x400?text=Echo+Dot', 'AMED5-BLK', 1),
(6, 'Google Nest Hub 2nd Gen', 'Smart display for your smart home', 99.99, 70, 'https://via.placeholder.com/400x400?text=Nest+Hub', 'GNHUB2-GRY', 1),
(6, 'Philips Hue Starter Kit', 'Smart lighting system with color changing bulbs', 199.99, 55, 'https://via.placeholder.com/400x400?text=Hue+Kit', 'PHHUE-START', 1),
(6, 'Ring Video Doorbell Pro', 'Smart doorbell with HD video and motion detection', 249.99, 65, 'https://via.placeholder.com/400x400?text=Ring+Pro', 'RVDP-BLK', 1);
