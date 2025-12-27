<?php
// Simple autoloader for the backend
spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/';
    
    // Map class names to files
    $classMap = [
        'Database' => 'config/database.php',
        'Auth' => 'middleware/Auth.php',
        'CORS' => 'middleware/CORS.php',
        'User' => 'models/User.php',
        'Product' => 'models/Product.php',
        'Order' => 'models/Order.php',
        'Category' => 'models/Category.php'
    ];
    
    if (isset($classMap[$class])) {
        require_once $baseDir . $classMap[$class];
    }
});

// Try to load composer autoloader if available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
