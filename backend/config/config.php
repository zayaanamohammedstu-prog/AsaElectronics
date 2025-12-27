<?php
// Application Configuration
return [
    'app' => [
        'name' => 'Asa Electronics',
        'env' => getenv('APP_ENV') ?: 'development',
        'debug' => getenv('APP_DEBUG') === 'true',
        'url' => getenv('APP_URL') ?: 'http://localhost'
    ],
    
    'jwt' => [
        'secret' => getenv('JWT_SECRET') ?: 'your-secret-key-change-in-production',
        'algorithm' => 'HS256',
        'expiration' => 86400 // 24 hours
    ],
    
    'paystack' => [
        'secret_key' => getenv('PAYSTACK_SECRET_KEY') ?: '',
        'public_key' => getenv('PAYSTACK_PUBLIC_KEY') ?: '',
        'callback_url' => getenv('PAYSTACK_CALLBACK_URL') ?: ''
    ],
    
    'google_analytics' => [
        'tracking_id' => getenv('GA_TRACKING_ID') ?: ''
    ],
    
    'oauth' => [
        'google' => [
            'client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
            'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
            'redirect_uri' => getenv('GOOGLE_REDIRECT_URI') ?: ''
        ]
    ],
    
    'cors' => [
        'allowed_origins' => explode(',', getenv('CORS_ALLOWED_ORIGINS') ?: 'http://localhost:3000'),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization'],
        'max_age' => 3600
    ],
    
    'upload' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'path' => __DIR__ . '/../../uploads/'
    ]
];
