<?php

declare(strict_types=1);

namespace App\Models;

class Consent extends Model
{
    protected string $table = 'consent';

    public function create(array $data): int
    {
        $this->db->prepareAndExecute("
            INSERT INTO {$this->table} (patient_id, provider_id, consent_type, status, description, granted_at, revoked_at, expires_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ", [
            $data['patient_id'],
            $data['provider_id'] ?? null,
            $data['consent_type'],
            $data['status'] ?? 'pending',
            $data['description'] ?? null,
            $data['granted_at'] ?? null,
            $data['revoked_at'] ?? null,
            $data['expires_at'] ?? null,
        ]);

        return $this->db->lastInsertId();
    }

    public function findByPatientId(int $patientId): array
    {
        $stmt = $this->db->prepareAndExecute("
            SELECT c.*, u.first_name, u.last_name, p.specialization
            FROM {$this->table} c
            LEFT JOIN providers p ON c.provider_id = p.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE c.patient_id = ?
            ORDER BY c.created_at DESC
        ", [$patientId]);
        return $stmt->fetchAll();
    }

    public function findByProviderId(int $providerId): array
    {
        $stmt = $this->db->prepareAndExecute("
            SELECT c.*, u.first_name, u.last_name, pa.date_of_birth, pa.gender
            FROM {$this->table} c
            LEFT JOIN patients pa ON c.patient_id = pa.id
            LEFT JOIN users u ON pa.user_id = u.id
            WHERE c.provider_id = ?
            ORDER BY c.created_at DESC
        ", [$providerId]);
        return $stmt->fetchAll();
    }

    public function findByStatus(string $status): array
    {
        $stmt = $this->db->prepareAndExecute(
            "SELECT * FROM {$this->table} WHERE status = ? ORDER BY created_at DESC",
            [$status]
        );
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status, ?string $timestampColumn = null): bool
    {
        if ($timestampColumn !== null) {
            $sql = "UPDATE {$this->table} SET status = ?, {$timestampColumn} = NOW() WHERE id = ?";
            $stmt = $this->db->prepareAndExecute($sql, [$status, $id]);
        } else {
            $sql = "UPDATE {$this->table} SET status = ? WHERE id = ?";
            $stmt = $this->db->prepareAndExecute($sql, [$status, $id]);
        }
        return $stmt->rowCount() > 0;
    }
}
