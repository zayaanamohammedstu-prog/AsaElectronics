<?php
class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($email, $password, $firstName, $lastName, $phone = null, $role = 'customer') {
        $query = "INSERT INTO " . $this->table . " 
                  (email, password_hash, first_name, last_name, phone, role) 
                  VALUES (:email, :password_hash, :first_name, :last_name, :phone, :role)";

        $stmt = $this->conn->prepare($query);

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findById($id) {
        $query = "SELECT id, email, first_name, last_name, phone, role, created_at, updated_at 
                  FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT id, email, first_name, last_name, phone, role, created_at, updated_at 
                  FROM " . $this->table . " 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['first_name'])) {
            $fields[] = "first_name = :first_name";
            $params[':first_name'] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $fields[] = "last_name = :last_name";
            $params[':last_name'] = $data['last_name'];
        }
        if (isset($data['phone'])) {
            $fields[] = "phone = :phone";
            $params[':phone'] = $data['phone'];
        }
        if (isset($data['password'])) {
            $fields[] = "password_hash = :password_hash";
            $params[':password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
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
}
