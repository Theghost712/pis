<?php

namespace App\Models;

use App\Core\Database;

class AuditLog
{
    private int $id;
    private ?int $userId;
    private string $action;
    private string $resourceType;
    private ?int $resourceId;
    private string $ipAddress;
    private ?string $userAgent;
    private array $details;
    private string $status;
    private string $createdAt;

    public function log(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "INSERT INTO audit_logs (user_id, action, resource_type, resource_id, 
             ip_address, user_agent, details, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'] ?? null,
                $data['action'],
                $data['resource_type'],
                $data['resource_id'] ?? null,
                $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null,
                json_encode($data['details'] ?? []),
                $data['status'] ?? 'success'
            ]
        );
        return $db->lastInsertId();
    }

    public function getByUser(int $userId, int $limit = 100): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
        return $stmt->fetchAll();
    }

    public function getByAction(string $action, int $limit = 100): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM audit_logs WHERE action = ? ORDER BY created_at DESC LIMIT ?",
            [$action, $limit]
        );
        return $stmt->fetchAll();
    }

    public function getByResource(string $resourceType, int $resourceId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM audit_logs WHERE resource_type = ? AND resource_id = ? 
             ORDER BY created_at DESC",
            [$resourceType, $resourceId]
        );
        return $stmt->fetchAll();
    }

    public function getRecent(int $limit = 100): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT a.*, u.username, u.first_name, u.last_name 
             FROM audit_logs a
             LEFT JOIN users u ON a.user_id = u.id
             ORDER BY a.created_at DESC LIMIT ?",
            [$limit]
        );
        return $stmt->fetchAll();
    }

    public function getStats(): array
    {
        $db = Database::getInstance();
        
        $stmt = $db->prepareAndExecute(
            "SELECT action, COUNT(*) as count FROM audit_logs GROUP BY action ORDER BY count DESC"
        );
        $actions = $stmt->fetchAll();
        
        $stmt = $db->prepareAndExecute(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM audit_logs 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY DATE(created_at) 
             ORDER BY date"
        );
        $daily = $stmt->fetchAll();
        
        $stmt = $db->prepareAndExecute(
            "SELECT COUNT(*) as total, 
             SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
             SUM(CASE WHEN status = 'failure' THEN 1 ELSE 0 END) as failure
             FROM audit_logs"
        );
        $summary = $stmt->fetch();
        
        return [
            'actions' => $actions,
            'daily' => $daily,
            'summary' => $summary
        ];
    }

    public function clearOld(int $days = 90): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$days]
        );
        return $stmt->rowCount();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getUserId(): ?int { return $this->userId; }
    public function getAction(): string { return $this->action; }
    public function getResourceType(): string { return $this->resourceType; }
    public function getResourceId(): ?int { return $this->resourceId; }
    public function getIpAddress(): string { return $this->ipAddress; }
    public function getUserAgent(): ?string { return $this->userAgent; }
    public function getDetails(): array { return $this->details; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): string { return $this->createdAt; }
}