<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class AuditService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function log(
        string $action,
        string $resourceType,
        ?int $resourceId = null,
        ?string $description = null,
        ?int $userId = null
    ): void {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, action, resource_type, resource_id, description, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $userId ?? $this->getCurrentUserId(),
            $action,
            $resourceType,
            $resourceId,
            $description,
            $this->getClientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    public function getLogs(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $pdo = $this->db->getConnection();
        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'al.user_id = ?';
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $where[] = 'al.action = ?';
            $params[] = $filters['action'];
        }

        if (!empty($filters['resource_type'])) {
            $where[] = 'al.resource_type = ?';
            $params[] = $filters['resource_type'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $perPage;

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs al {$whereClause}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT al.*, u.username, CONCAT(u.first_name, ' ', u.last_name) as user_name, u.email as user_email
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            {$whereClause}
            ORDER BY al.created_at DESC
            LIMIT ? OFFSET ?
        ");

        $allParams = array_merge($params, [$perPage, $offset]);
        $stmt->execute($allParams);

        return [
            'data' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ];
    }

    private function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    private function getClientIp(): ?string
    {
        $keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];

        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', $_SERVER[$key])[0];
                return trim($ip);
            }
        }

        return null;
    }
}
