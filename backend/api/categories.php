<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../middleware/CORS.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Category.php';

$cors = new CORS();
$cors->handleCORS();

$database = new Database();
$db = $database->getConnection();
$categoryModel = new Category($db);

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Get all categories
        $categories = $categoryModel->getAll();
        echo json_encode(['categories' => $categories]);

    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Category API error: " . $e->getMessage());
    echo json_encode(['error' => 'Internal server error']);
}
