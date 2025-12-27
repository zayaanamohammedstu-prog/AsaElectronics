<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../middleware/CORS.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';

$cors = new CORS();
$cors->handleCORS();

$auth = new Auth();
$database = new Database();
$db = $database->getConnection();
$productModel = new Product($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

try {
    if ($method === 'GET' && end($pathParts) === 'products') {
        // List products
        $filters = [];
        
        if (isset($_GET['category_id'])) {
            $filters['category_id'] = (int)$_GET['category_id'];
        }
        
        if (isset($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        
        if (isset($_GET['is_active'])) {
            $filters['is_active'] = (int)$_GET['is_active'];
        } else {
            $filters['is_active'] = 1; // Default to active products
        }
        
        if (isset($_GET['limit'])) {
            $filters['limit'] = (int)$_GET['limit'];
        } else {
            $filters['limit'] = 50;
        }
        
        if (isset($_GET['page'])) {
            $page = (int)$_GET['page'];
            $filters['offset'] = ($page - 1) * $filters['limit'];
        }

        $products = $productModel->getAll($filters);
        $total = $productModel->getCount($filters);

        echo json_encode([
            'products' => $products,
            'total' => $total,
            'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'limit' => $filters['limit']
        ]);

    } elseif ($method === 'GET' && is_numeric(end($pathParts))) {
        // Get single product
        $productId = (int)end($pathParts);
        $product = $productModel->getById($productId);

        if ($product) {
            echo json_encode(['product' => $product]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
        }

    } elseif ($method === 'POST' && end($pathParts) === 'products') {
        // Create product (admin only)
        $auth->requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);

        $required = ['name', 'price', 'stock_quantity'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Field '$field' is required"]);
                exit;
            }
        }

        $productData = [
            'category_id' => $data['category_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'price' => $data['price'],
            'stock_quantity' => $data['stock_quantity'],
            'image_url' => $data['image_url'] ?? '',
            'sku' => $data['sku'] ?? 'SKU-' . time() . '-' . rand(1000, 9999),
            'is_active' => $data['is_active'] ?? 1
        ];

        $productId = $productModel->create($productData);

        if ($productId) {
            $product = $productModel->getById($productId);
            http_response_code(201);
            echo json_encode(['product' => $product]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create product']);
        }

    } elseif ($method === 'PUT' && is_numeric(end($pathParts))) {
        // Update product (admin only)
        $auth->requireAdmin();
        
        $productId = (int)end($pathParts);
        $data = json_decode(file_get_contents('php://input'), true);

        if ($productModel->update($productId, $data)) {
            $product = $productModel->getById($productId);
            echo json_encode(['product' => $product]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update product']);
        }

    } elseif ($method === 'DELETE' && is_numeric(end($pathParts))) {
        // Delete product (admin only)
        $auth->requireAdmin();
        
        $productId = (int)end($pathParts);

        if ($productModel->delete($productId)) {
            echo json_encode(['message' => 'Product deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete product']);
        }

    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Product API error: " . $e->getMessage());
    echo json_encode(['error' => 'Internal server error']);
}
