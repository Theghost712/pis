<?php

declare(strict_types=1);

namespace App\Models;

class Consent extends Model
{
    protected string $table = 'consents';

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (patient_id, provider_id, consent_type, status, description, granted_at, revoked_at, expires_at, created_at)
            VALUES (:patient_id, :provider_id, :consent_type, :status, :description, :granted_at, :revoked_at, :expires_at, NOW())
        ");

        $stmt->execute([
            'patient_id' => $data['patient_id'],
            'provider_id' => $data['provider_id'] ?? null,
            'consent_type' => $data['consent_type'],
            'status' => $data['status'] ?? 'pending',
            'description' => $data['description'] ?? null,
            'granted_at' => $data['granted_at'] ?? null,
            'revoked_at' => $data['revoked_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByPatientId(int $patientId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, pr.name as provider_name
            FROM {$this->table} c
            LEFT JOIN providers p ON c.provider_id = p.id
            LEFT JOIN users pr ON p.user_id = pr.id
            WHERE c.patient_id = :patient_id
            ORDER BY c.created_at DESC
        ");
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    public function findByProviderId(int $providerId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, pt.name as patient_name
            FROM {$this->table} c
            LEFT JOIN patients pa ON c.patient_id = pa.id
            LEFT JOIN users pt ON pa.user_id = pt.id
            WHERE c.provider_id = :provider_id
            ORDER BY c.created_at DESC
        ");
        $stmt->execute(['provider_id' => $providerId]);
        return $stmt->fetchAll();
    }

    public function findByStatus(string $status): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = :status ORDER BY created_at DESC");
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status, ?string $timestampColumn = null): bool
    {
        $data = ['status' => $status, 'id' => $id];
        $sql = "UPDATE {$this->table} SET status = :status";

        if ($timestampColumn !== null) {
            $sql .= ", {$timestampColumn} = NOW()";
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
}
