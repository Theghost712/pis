<?php

namespace App\Models;

use App\Core\Database;

class Hospital
{
    private int $id;
    private string $name;
    private string $address;
    private string $phone;
    private ?string $email;
    private string $createdAt;
    private ?string $updatedAt;

    public function findById(int $id): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM hospitals WHERE id = ? LIMIT 1",
            [$id]
        );
        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }
        return $this->hydrate($data);
    }

    public function all(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute("SELECT * FROM hospitals ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function findAll(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM hospitals ORDER BY name ASC"
        );
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "INSERT INTO hospitals (name, address, phone, email) VALUES (?, ?, ?, ?)",
            [
                $data['name'],
                $data['address'],
                $data['phone'],
                $data['email'] ?? null
            ]
        );
        return $db->lastInsertId();
    }

    public function update(array $data): bool
    {
        $db = Database::getInstance();
        $fields = [];
        $params = [];

        $allowedFields = ['name', 'address', 'phone', 'email'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return true;
        }

        $params[] = $this->id;
        $sql = "UPDATE hospitals SET " . implode(', ', $fields) . " WHERE id = ?";
        $db->prepareAndExecute($sql, $params);
        return true;
    }

    public function delete(): bool
    {
        $db = Database::getInstance();
        $db->prepareAndExecute(
            "DELETE FROM hospitals WHERE id = ?",
            [$this->id]
        );
        return true;
    }

    public function getProviderCount(): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT COUNT(*) FROM providers WHERE hospital_id = ?",
            [$this->id]
        );
        return (int)$stmt->fetchColumn();
    }

    protected function hydrate(array $data): self
    {
        $this->id = (int)$data['id'];
        $this->name = $data['name'];
        $this->address = $data['address'];
        $this->phone = $data['phone'];
        $this->email = $data['email'];
        $this->createdAt = $data['created_at'];
        $this->updatedAt = $data['updated_at'];
        return $this;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getAddress(): string { return $this->address; }
    public function getPhone(): string { return $this->phone; }
    public function getEmail(): ?string { return $this->email; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
}