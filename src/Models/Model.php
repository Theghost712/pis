<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class Model
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function getPdo(): PDO
    {
        return $this->db->getConnection();
    }

    public function all(): array
    {
        $stmt = $this->db->prepareAndExecute("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepareAndExecute("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $stmt = $this->db->prepareAndExecute("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})", array_values($data));
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $params = array_merge(array_values($data), [$id]);

        $stmt = $this->db->prepareAndExecute("UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?", $params);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepareAndExecute("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function where(array $conditions): array
    {
        $where = implode(' AND ', array_map(fn($col) => "{$col} = ?", array_keys($conditions)));
        $stmt = $this->db->prepareAndExecute("SELECT * FROM {$this->table} WHERE {$where}", array_values($conditions));
        return $stmt->fetchAll();
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->db->prepareAndExecute("SELECT COUNT(*) FROM {$this->table}");
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepareAndExecute("SELECT * FROM {$this->table} LIMIT ? OFFSET ?", [$perPage, $offset]);

        return [
            'data' => $stmt->fetchAll(),
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ];
    }
}
