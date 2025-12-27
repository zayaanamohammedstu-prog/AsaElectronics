<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../middleware/CORS.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../config/config.php';

$cors = new CORS();
$cors->handleCORS();

$auth = new Auth();
$config = require __DIR__ . '/../config/config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST' && strpos($_SERVER['REQUEST_URI'], '/initialize') !== false) {
        // Initialize payment
        $currentUser = $auth->requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['amount']) || !isset($data['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Amount and email are required']);
            exit;
        }

        // Convert amount to kobo (PayStack uses smallest currency unit)
        $amountInKobo = $data['amount'] * 100;

        $postData = [
            'email' => $data['email'],
            'amount' => $amountInKobo,
            'metadata' => [
                'order_id' => $data['order_id'] ?? null,
                'user_id' => $currentUser['userId']
            ]
        ];

        if (isset($data['callback_url'])) {
            $postData['callback_url'] = $data['callback_url'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transaction/initialize');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $config['paystack']['secret_key'],
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            echo $response;
        } else {
            http_response_code($httpCode);
            echo $response;
        }

    } elseif ($method === 'GET' && strpos($_SERVER['REQUEST_URI'], '/verify') !== false) {
        // Verify payment
        $auth->requireAuth();
        
        $reference = $_GET['reference'] ?? null;

        if (!$reference) {
            http_response_code(400);
            echo json_encode(['error' => 'Payment reference is required']);
            exit;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transaction/verify/' . $reference);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $config['paystack']['secret_key']
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            
            // Update order payment status if order_id is in metadata
            if ($result['status'] && $result['data']['status'] === 'success') {
                $orderId = $result['data']['metadata']['order_id'] ?? null;
                
                if ($orderId) {
                    require_once __DIR__ . '/../config/database.php';
                    require_once __DIR__ . '/../models/Order.php';
                    
                    $database = new Database();
                    $db = $database->getConnection();
                    $orderModel = new Order($db);
                    
                    $orderModel->updatePaymentStatus($orderId, 'completed', $reference);
                }
            }

            echo $response;
        } else {
            http_response_code($httpCode);
            echo $response;
        }

    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Payment API error: " . $e->getMessage());
    echo json_encode(['error' => 'Internal server error']);
}
