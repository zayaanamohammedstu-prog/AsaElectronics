<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private $config;

    public function __construct() {
        $this->config = require __DIR__ . '/../config/config.php';
    }

    public function generateToken($userId, $email, $role) {
        $payload = [
            'iss' => $this->config['app']['url'],
            'iat' => time(),
            'exp' => time() + $this->config['jwt']['expiration'],
            'userId' => $userId,
            'email' => $email,
            'role' => $role
        ];

        return JWT::encode($payload, $this->config['jwt']['secret'], $this->config['jwt']['algorithm']);
    }

    public function verifyToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->config['jwt']['secret'], $this->config['jwt']['algorithm']));
            return (array) $decoded;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getAuthorizationToken() {
        // Try to get headers from apache_request_headers first (if available)
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            // Fallback for non-Apache servers
            $headers = [];
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) === 'HTTP_') {
                    $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                    $headers[$header] = $value;
                }
            }
        }
        
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }

    public function requireAuth() {
        $token = $this->getAuthorizationToken();
        
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided']);
            exit;
        }

        $decoded = $this->verifyToken($token);
        
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }

        return $decoded;
    }

    public function requireAdmin() {
        $user = $this->requireAuth();
        
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            exit;
        }

        return $user;
    }

    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}
