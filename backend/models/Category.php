<?php
class Category {
    private $conn;
    private $table = 'categories';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($name, $description, $slug) {
        $query = "INSERT INTO " . $this->table . " 
                  (name, description, slug) 
                  VALUES (:name, :description, :slug)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':slug', $slug);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function getAll() {
        $query = "SELECT c.*, COUNT(p.id) as product_count 
                  FROM " . $this->table . " c 
                  LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
                  GROUP BY c.id 
                  ORDER BY c.name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getBySlug($slug) {
        $query = "SELECT * FROM " . $this->table . " WHERE slug = :slug LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function update($id, $name, $description, $slug) {
        $query = "UPDATE " . $this->table . " 
                  SET name = :name, description = :description, slug = :slug 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
