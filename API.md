# Asa Electronics API Documentation

Base URL: `http://localhost:8000/api` (Development) or `https://yourdomain.com/api` (Production)

All endpoints require proper CORS headers and most require authentication via JWT token in the Authorization header.

## Authentication

### Register New User
**POST** `/auth.php/register`

Request Body:
```json
{
  "email": "user@example.com",
  "password": "password123",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+1234567890"
}
```

Response (200):
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "role": "customer"
  }
}
```

### Login
**POST** `/auth.php/login`

Request Body:
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

Response (200):
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "role": "customer"
  }
}
```

### Get Current User
**GET** `/auth.php/me`

Headers:
```
Authorization: Bearer <token>
```

Response (200):
```json
{
  "user": {
    "id": 1,
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+1234567890",
    "role": "customer",
    "created_at": "2024-01-01 00:00:00"
  }
}
```

### Update Current User
**PUT** `/auth.php/me`

Headers:
```
Authorization: Bearer <token>
```

Request Body:
```json
{
  "first_name": "Jane",
  "last_name": "Smith",
  "phone": "+9876543210"
}
```

### List All Users (Admin Only)
**GET** `/auth.php/users?page=1&limit=50`

Headers:
```
Authorization: Bearer <admin-token>
```

## Products

### List Products
**GET** `/products.php/products?category_id=1&search=laptop&is_active=1&page=1&limit=20`

Query Parameters:
- `category_id` (optional): Filter by category ID
- `search` (optional): Search in product name and description
- `is_active` (optional): 1 for active products, 0 for inactive (default: 1)
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 50, max: 100)

Response (200):
```json
{
  "products": [
    {
      "id": 1,
      "name": "iPhone 15 Pro",
      "description": "Latest iPhone with A17 chip",
      "price": "999.99",
      "stock_quantity": 50,
      "image_url": "https://example.com/image.jpg",
      "sku": "IPH15PRO-128",
      "is_active": 1,
      "category_id": 1,
      "category_name": "Smartphones",
      "category_slug": "smartphones"
    }
  ],
  "total": 100,
  "page": 1,
  "limit": 20
}
```

### Get Single Product
**GET** `/products.php/{id}`

Response (200):
```json
{
  "product": {
    "id": 1,
    "name": "iPhone 15 Pro",
    "description": "Latest iPhone with A17 chip",
    "price": "999.99",
    "stock_quantity": 50,
    "image_url": "https://example.com/image.jpg",
    "sku": "IPH15PRO-128",
    "is_active": 1,
    "category_id": 1,
    "category_name": "Smartphones",
    "category_slug": "smartphones"
  }
}
```

### Create Product (Admin Only)
**POST** `/products.php/products`

Headers:
```
Authorization: Bearer <admin-token>
```

Request Body:
```json
{
  "name": "iPhone 15 Pro",
  "description": "Latest iPhone with A17 chip",
  "price": 999.99,
  "stock_quantity": 50,
  "category_id": 1,
  "image_url": "https://example.com/image.jpg",
  "sku": "IPH15PRO-128",
  "is_active": 1
}
```

### Update Product (Admin Only)
**PUT** `/products.php/{id}`

Headers:
```
Authorization: Bearer <admin-token>
```

Request Body (all fields optional):
```json
{
  "name": "iPhone 15 Pro Max",
  "price": 1099.99,
  "stock_quantity": 30
}
```

### Delete Product (Admin Only)
**DELETE** `/products.php/{id}`

Headers:
```
Authorization: Bearer <admin-token>
```

## Categories

### List Categories
**GET** `/categories.php`

Response (200):
```json
{
  "categories": [
    {
      "id": 1,
      "name": "Smartphones",
      "description": "Latest smartphones and mobile devices",
      "slug": "smartphones",
      "product_count": 25
    }
  ]
}
```

## Orders

### Create Order
**POST** `/orders.php/orders`

Headers:
```
Authorization: Bearer <token>
```

Request Body:
```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 3,
      "quantity": 1
    }
  ],
  "address_id": 1
}
```

Response (201):
```json
{
  "order": {
    "id": 1,
    "user_id": 1,
    "total_amount": "2999.97",
    "status": "pending",
    "payment_status": "pending",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "name": "iPhone 15 Pro",
        "quantity": 2,
        "price": "999.99"
      }
    ]
  }
}
```

### List Orders
**GET** `/orders.php/orders?status=pending&page=1&limit=50`

Headers:
```
Authorization: Bearer <token>
```

Query Parameters (Admin only):
- `status` (optional): Filter by order status
- `payment_status` (optional): Filter by payment status
- `page` (optional): Page number
- `limit` (optional): Items per page

Response (200):
```json
{
  "orders": [
    {
      "id": 1,
      "user_id": 1,
      "email": "user@example.com",
      "first_name": "John",
      "last_name": "Doe",
      "total_amount": "2999.97",
      "status": "pending",
      "payment_status": "pending",
      "created_at": "2024-01-01 00:00:00"
    }
  ],
  "total": 10
}
```

### Get Order Details
**GET** `/orders.php/{id}`

Headers:
```
Authorization: Bearer <token>
```

Response (200):
```json
{
  "order": {
    "id": 1,
    "user_id": 1,
    "total_amount": "2999.97",
    "status": "pending",
    "payment_status": "pending",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "name": "iPhone 15 Pro",
        "quantity": 2,
        "price": "999.99",
        "image_url": "https://example.com/image.jpg",
        "sku": "IPH15PRO-128"
      }
    ],
    "address_line1": "123 Main St",
    "city": "New York",
    "country": "USA"
  }
}
```

### Update Order Status (Admin Only)
**PUT** `/orders.php/{id}/status`

Headers:
```
Authorization: Bearer <admin-token>
```

Request Body:
```json
{
  "status": "processing"
}
```

Valid statuses: `pending`, `processing`, `shipped`, `delivered`, `cancelled`

### Get Sales Analytics (Admin Only)
**GET** `/orders.php/analytics?start_date=2024-01-01&end_date=2024-12-31`

Headers:
```
Authorization: Bearer <admin-token>
```

Response (200):
```json
{
  "analytics": [
    {
      "date": "2024-01-01",
      "order_count": 10,
      "total_sales": "5999.90",
      "avg_order_value": "599.99"
    }
  ]
}
```

## Payments (PayStack Integration)

### Initialize Payment
**POST** `/payments.php/initialize`

Headers:
```
Authorization: Bearer <token>
```

Request Body:
```json
{
  "amount": 2999.97,
  "email": "user@example.com",
  "order_id": 1,
  "callback_url": "https://yoursite.com/payment/callback"
}
```

Response (200):
```json
{
  "status": true,
  "message": "Authorization URL created",
  "data": {
    "authorization_url": "https://checkout.paystack.com/xxxxx",
    "access_code": "xxxxx",
    "reference": "xxxxx"
  }
}
```

### Verify Payment
**GET** `/payments.php/verify?reference=xxxxx`

Headers:
```
Authorization: Bearer <token>
```

Response (200):
```json
{
  "status": true,
  "message": "Verification successful",
  "data": {
    "status": "success",
    "reference": "xxxxx",
    "amount": 299997,
    "metadata": {
      "order_id": 1,
      "user_id": 1
    }
  }
}
```

## Error Responses

All endpoints may return these error responses:

**400 Bad Request**
```json
{
  "error": "Invalid input data"
}
```

**401 Unauthorized**
```json
{
  "error": "No token provided"
}
```

**403 Forbidden**
```json
{
  "error": "Admin access required"
}
```

**404 Not Found**
```json
{
  "error": "Resource not found"
}
```

**500 Internal Server Error**
```json
{
  "error": "Internal server error"
}
```

## Rate Limiting

Currently no rate limiting is implemented. Consider adding rate limiting for production use.

## Testing with cURL

### Login Example
```bash
curl -X POST http://localhost:8000/api/auth.php/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@asaelectronics.com","password":"admin123"}'
```

### Get Products Example
```bash
curl -X GET http://localhost:8000/api/products.php/products?limit=10
```

### Create Order Example
```bash
curl -X POST http://localhost:8000/api/orders.php/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{"items":[{"product_id":1,"quantity":2}]}'
```

## Webhooks

PayStack webhooks can be configured to notify your application of payment events. Set up a webhook endpoint in your PayStack dashboard pointing to:

`https://yourdomain.com/api/payments.php/webhook`

(Note: Webhook endpoint needs to be implemented based on your specific requirements)
