<?php

declare(strict_types=1);

namespace PIS\Services;

use PIS\Core\Database;

class AuditService
{
    private \PDO $db;

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
        $stmt = $this->db->prepare("
            INSERT INTO audit_logs (user_id, action, resource_type, resource_id, description, ip_address, user_agent, created_at)
            VALUES (:user_id, :action, :resource_type, :resource_id, :description, :ip_address, :user_agent, NOW())
        ");

        $stmt->execute([
            'user_id' => $userId ?? $this->getCurrentUserId(),
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'description' => $description,
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    public function getLogs(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = :user_id';
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $where[] = 'action = :action';
            $params['action'] = $filters['action'];
        }

        if (!empty($filters['resource_type'])) {
            $where[] = 'resource_type = :resource_type';
            $params['resource_type'] = $filters['resource_type'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM audit_logs {$whereClause}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT al.*, u.name as user_name, u.email as user_email
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            {$whereClause}
            ORDER BY al.created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

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
