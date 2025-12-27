<?php
class CORS {
    private $config;

    public function __construct() {
        $this->config = require __DIR__ . '/../config/config.php';
    }

    public function handleCORS() {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array($origin, $this->config['cors']['allowed_origins']) || in_array('*', $this->config['cors']['allowed_origins'])) {
            header("Access-Control-Allow-Origin: $origin");
        }

        header("Access-Control-Allow-Methods: " . implode(', ', $this->config['cors']['allowed_methods']));
        header("Access-Control-Allow-Headers: " . implode(', ', $this->config['cors']['allowed_headers']));
        header("Access-Control-Max-Age: " . $this->config['cors']['max_age']);
        header("Access-Control-Allow-Credentials: true");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
