<?php
class Order {
    private $conn;
    private $table = 'orders';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($userId, $addressId, $totalAmount, $items) {
        try {
            $this->conn->beginTransaction();

            // Create order
            $query = "INSERT INTO " . $this->table . " 
                      (user_id, address_id, total_amount, status, payment_status) 
                      VALUES (:user_id, :address_id, :total_amount, 'pending', 'pending')";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':address_id', $addressId);
            $stmt->bindParam(':total_amount', $totalAmount);
            $stmt->execute();

            $orderId = $this->conn->lastInsertId();

            // Create order items
            $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                          VALUES (:order_id, :product_id, :quantity, :price)";
            $itemStmt = $this->conn->prepare($itemQuery);

            foreach ($items as $item) {
                $itemStmt->bindParam(':order_id', $orderId);
                $itemStmt->bindParam(':product_id', $item['product_id']);
                $itemStmt->bindParam(':quantity', $item['quantity']);
                $itemStmt->bindParam(':price', $item['price']);
                $itemStmt->execute();

                // Update product stock
                $stockQuery = "UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :product_id";
                $stockStmt = $this->conn->prepare($stockQuery);
                $stockStmt->bindParam(':quantity', $item['quantity']);
                $stockStmt->bindParam(':product_id', $item['product_id']);
                $stockStmt->execute();
            }

            $this->conn->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Order creation error: " . $e->getMessage());
            return false;
        }
    }

    public function getById($id) {
        $query = "SELECT o.*, u.email, u.first_name, u.last_name,
                  a.address_line1, a.address_line2, a.city, a.state, a.country, a.postal_code
                  FROM " . $this->table . " o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  LEFT JOIN addresses a ON o.address_id = a.id 
                  WHERE o.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $order = $stmt->fetch();

        if ($order) {
            $order['items'] = $this->getOrderItems($id);
        }

        return $order;
    }

    public function getOrderItems($orderId) {
        $query = "SELECT oi.*, p.name, p.image_url, p.sku 
                  FROM order_items oi 
                  LEFT JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getByUserId($userId, $limit = 50, $offset = 0) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getAll($filters = []) {
        $query = "SELECT o.*, u.email, u.first_name, u.last_name 
                  FROM " . $this->table . " o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  WHERE 1=1";

        $params = [];

        if (isset($filters['status'])) {
            $query .= " AND o.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (isset($filters['payment_status'])) {
            $query .= " AND o.payment_status = :payment_status";
            $params[':payment_status'] = $filters['payment_status'];
        }

        $query .= " ORDER BY o.created_at DESC";

        if (isset($filters['limit'])) {
            $query .= " LIMIT :limit";
            if (isset($filters['offset'])) {
                $query .= " OFFSET :offset";
            }
        }

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if (isset($filters['limit'])) {
            $stmt->bindValue(':limit', $filters['limit'], PDO::PARAM_INT);
            if (isset($filters['offset'])) {
                $stmt->bindValue(':offset', $filters['offset'], PDO::PARAM_INT);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updatePaymentStatus($id, $paymentStatus, $paymentReference = null) {
        $query = "UPDATE " . $this->table . " 
                  SET payment_status = :payment_status, payment_reference = :payment_reference 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':payment_status', $paymentStatus);
        $stmt->bindParam(':payment_reference', $paymentReference);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function getSalesAnalytics($startDate = null, $endDate = null) {
        $query = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as order_count,
                    SUM(total_amount) as total_sales,
                    AVG(total_amount) as avg_order_value
                  FROM " . $this->table . " 
                  WHERE payment_status = 'completed'";

        $params = [];

        if ($startDate) {
            $query .= " AND created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if ($endDate) {
            $query .= " AND created_at <= :end_date";
            $params[':end_date'] = $endDate;
        }

        $query .= " GROUP BY DATE(created_at) ORDER BY date DESC";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCount($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE 1=1";
        $params = [];

        if (isset($filters['status'])) {
            $query .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }

        if (isset($filters['payment_status'])) {
            $query .= " AND payment_status = :payment_status";
            $params[':payment_status'] = $filters['payment_status'];
        }

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }
}
