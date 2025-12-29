<?php
// Configuration for PHP Frontend
session_start();

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'asa_electronics');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASSWORD') ?: '');

// PayStack Configuration
define('PAYSTACK_SECRET_KEY', getenv('PAYSTACK_SECRET_KEY') ?: '');
define('PAYSTACK_PUBLIC_KEY', getenv('PAYSTACK_PUBLIC_KEY') ?: '');

// Site Configuration
define('SITE_NAME', 'Asa Electronics');
define('SITE_URL', getenv('APP_URL') ?: 'http://localhost');
define('CURRENCY', '₦'); // Nigerian Naira
define('CURRENCY_CODE', 'NGN');

// File Upload Configuration
define('UPLOAD_DIR', dirname(__DIR__, 2) . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');

// Initialize database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Generate CSRF token if not exists
if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}
