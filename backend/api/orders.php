<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../middleware/CORS.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Product.php';

$cors = new CORS();
$cors->handleCORS();

$auth = new Auth();
$database = new Database();
$db = $database->getConnection();
$orderModel = new Order($db);
$productModel = new Product($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

try {
    if ($method === 'POST' && end($pathParts) === 'orders') {
        // Create order
        $currentUser = $auth->requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['items']) || empty($data['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Order items are required']);
            exit;
        }

        // Validate items and calculate total
        $totalAmount = 0;
        $orderItems = [];

        foreach ($data['items'] as $item) {
            $product = $productModel->getById($item['product_id']);
            
            if (!$product) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid product ID: ' . $item['product_id']]);
                exit;
            }

            if ($product['stock_quantity'] < $item['quantity']) {
                http_response_code(400);
                echo json_encode(['error' => 'Insufficient stock for product: ' . $product['name']]);
                exit;
            }

            $itemTotal = $product['price'] * $item['quantity'];
            $totalAmount += $itemTotal;

            $orderItems[] = [
                'product_id' => $product['id'],
                'quantity' => $item['quantity'],
                'price' => $product['price']
            ];
        }

        $orderId = $orderModel->create(
            $currentUser['userId'],
            $data['address_id'] ?? null,
            $totalAmount,
            $orderItems
        );

        if ($orderId) {
            $order = $orderModel->getById($orderId);
            http_response_code(201);
            echo json_encode(['order' => $order]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create order']);
        }

    } elseif ($method === 'GET' && end($pathParts) === 'orders') {
        // List orders
        $currentUser = $auth->requireAuth();

        if ($currentUser['role'] === 'admin') {
            // Admin can see all orders
            $filters = [];
            
            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            
            if (isset($_GET['payment_status'])) {
                $filters['payment_status'] = $_GET['payment_status'];
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

            $orders = $orderModel->getAll($filters);
            $total = $orderModel->getCount($filters);
        } else {
            // Customers can only see their own orders
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $offset = ($page - 1) * $limit;

            $orders = $orderModel->getByUserId($currentUser['userId'], $limit, $offset);
            $total = count($orders);
        }

        echo json_encode([
            'orders' => $orders,
            'total' => $total
        ]);

    } elseif ($method === 'GET' && is_numeric(end($pathParts))) {
        // Get single order
        $currentUser = $auth->requireAuth();
        $orderId = (int)end($pathParts);
        $order = $orderModel->getById($orderId);

        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            exit;
        }

        // Check if user has permission to view this order
        if ($currentUser['role'] !== 'admin' && $order['user_id'] != $currentUser['userId']) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit;
        }

        echo json_encode(['order' => $order]);

    } elseif ($method === 'PUT' && preg_match('/\/orders\/(\d+)\/status$/', $path, $matches)) {
        // Update order status (admin only)
        $auth->requireAdmin();
        
        $orderId = (int)$matches[1];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Status is required']);
            exit;
        }

        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($data['status'], $validStatuses)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status']);
            exit;
        }

        if ($orderModel->updateStatus($orderId, $data['status'])) {
            $order = $orderModel->getById($orderId);
            echo json_encode(['order' => $order]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update order status']);
        }

    } elseif ($method === 'GET' && end($pathParts) === 'analytics') {
        // Get sales analytics (admin only)
        $auth->requireAdmin();
        
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        $analytics = $orderModel->getSalesAnalytics($startDate, $endDate);
        echo json_encode(['analytics' => $analytics]);

    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Order API error: " . $e->getMessage());
    echo json_encode(['error' => 'Internal server error']);
}
