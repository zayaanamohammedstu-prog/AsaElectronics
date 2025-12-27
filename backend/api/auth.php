<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../middleware/CORS.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

$cors = new CORS();
$cors->handleCORS();

$auth = new Auth();
$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

try {
    if ($method === 'POST' && end($pathParts) === 'login') {
        // Login
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            exit;
        }

        $user = $userModel->findByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            exit;
        }

        $token = $auth->generateToken($user['id'], $user['email'], $user['role']);

        echo json_encode([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => $user['role']
            ]
        ]);
        
    } elseif ($method === 'POST' && end($pathParts) === 'register') {
        // Register
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['email']) || !isset($data['password']) || !isset($data['first_name']) || !isset($data['last_name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'All fields are required']);
            exit;
        }

        // Check if user exists
        $existingUser = $userModel->findByEmail($data['email']);
        if ($existingUser) {
            http_response_code(409);
            echo json_encode(['error' => 'User already exists']);
            exit;
        }

        $userId = $userModel->create(
            $data['email'],
            $data['password'],
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null
        );

        if ($userId) {
            $user = $userModel->findById($userId);
            $token = $auth->generateToken($user['id'], $user['email'], $user['role']);

            echo json_encode([
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create user']);
        }

    } elseif ($method === 'GET' && end($pathParts) === 'me') {
        // Get current user
        $currentUser = $auth->requireAuth();
        $user = $userModel->findById($currentUser['userId']);

        if ($user) {
            echo json_encode(['user' => $user]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
        }

    } elseif ($method === 'PUT' && end($pathParts) === 'me') {
        // Update current user
        $currentUser = $auth->requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);

        if ($userModel->update($currentUser['userId'], $data)) {
            $user = $userModel->findById($currentUser['userId']);
            echo json_encode(['user' => $user]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update user']);
        }

    } elseif ($method === 'GET' && end($pathParts) === 'users') {
        // List users (admin only)
        $auth->requireAdmin();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = ($page - 1) * $limit;

        $users = $userModel->getAll($limit, $offset);
        echo json_encode(['users' => $users]);

    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Auth API error: " . $e->getMessage());
    echo json_encode(['error' => 'Internal server error']);
}
