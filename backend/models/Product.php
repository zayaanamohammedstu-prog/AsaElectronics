<?php
class Product {
    private $conn;
    private $table = 'products';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (category_id, name, description, price, stock_quantity, image_url, sku, is_active) 
                  VALUES (:category_id, :name, :description, :price, :stock_quantity, :image_url, :sku, :is_active)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':stock_quantity', $data['stock_quantity']);
        $stmt->bindParam(':image_url', $data['image_url']);
        $stmt->bindParam(':sku', $data['sku']);
        $stmt->bindParam(':is_active', $data['is_active']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function getAll($filters = []) {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                  FROM " . $this->table . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE 1=1";

        $params = [];

        if (isset($filters['category_id'])) {
            $query .= " AND p.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        if (isset($filters['is_active'])) {
            $query .= " AND p.is_active = :is_active";
            $params[':is_active'] = $filters['is_active'];
        }

        if (isset($filters['search'])) {
            $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $query .= " ORDER BY p.created_at DESC";

        if (isset($filters['limit'])) {
            $query .= " LIMIT :limit";
            if (isset($filters['offset'])) {
                $query .= " OFFSET :offset";
            }
        }

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
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

    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                  FROM " . $this->table . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        $allowedFields = ['category_id', 'name', 'description', 'price', 'stock_quantity', 'image_url', 'sku', 'is_active'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute($params);
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateStock($id, $quantity) {
        $query = "UPDATE " . $this->table . " SET stock_quantity = stock_quantity + :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':quantity', $quantity);
        return $stmt->execute();
    }

    public function getCount($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE 1=1";
        $params = [];

        if (isset($filters['category_id'])) {
            $query .= " AND category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        if (isset($filters['is_active'])) {
            $query .= " AND is_active = :is_active";
            $params[':is_active'] = $filters['is_active'];
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
