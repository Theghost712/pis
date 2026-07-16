<?php

namespace App\Models;

use App\Core\Database;

class Notification
{
    private int $id;
    private int $userId;
    private string $type;
    private string $subject;
    private string $message;
    private bool $isRead;
    private string $sentAt;
    private ?string $readAt;

    public function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "INSERT INTO notifications (user_id, type, subject, message) VALUES (?, ?, ?, ?)",
            [
                $data['user_id'],
                $data['type'],
                $data['subject'],
                $data['message']
            ]
        );
        return $db->lastInsertId();
    }

    public function findByUserId(int $userId, int $limit = 50): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY sent_at DESC LIMIT ?",
            [$userId, $limit]
        );
        return $stmt->fetchAll();
    }

    public function getUnreadCount(int $userId): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
        return (int)$stmt->fetchColumn();
    }

    public function markAsRead(int $id): bool
    {
        $db = Database::getInstance();
        $db->prepareAndExecute(
            "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?",
            [$id]
        );
        return true;
    }

    public function markAllAsRead(int $userId): bool
    {
        $db = Database::getInstance();
        $db->prepareAndExecute(
            "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
        return true;
    }

    public function deleteOld(int $days = 30): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "DELETE FROM notifications WHERE sent_at < DATE_SUB(NOW(), INTERVAL ? DAY) AND is_read = 1",
            [$days]
        );
        return $stmt->rowCount();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getType(): string { return $this->type; }
    public function getSubject(): string { return $this->subject; }
    public function getMessage(): string { return $this->message; }
    public function isRead(): bool { return $this->isRead; }
    public function getSentAt(): string { return $this->sentAt; }
    public function getReadAt(): ?string { return $this->readAt; }
}