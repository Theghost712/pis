<?php

declare(strict_types=1);

namespace App\Models;

class MedicalRecord extends Model
{
    protected string $table = 'medical_records';

    public function create(array $data): int
    {
        $this->db->prepareAndExecute("
            INSERT INTO {$this->table} (patient_id, provider_id, record_type, title, description, diagnosis, notes, record_date, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ", [
            $data['patient_id'],
            $data['provider_id'] ?? null,
            $data['record_type'],
            $data['title'],
            $data['description'] ?? null,
            $data['diagnosis'] ?? null,
            $data['notes'] ?? null,
            $data['record_date'],
        ]);

        return $this->db->lastInsertId();
    }

    public function findByPatientId(int $patientId): array
    {
        $stmt = $this->db->prepareAndExecute("
            SELECT mr.*, u.first_name, u.last_name
            FROM {$this->table} mr
            LEFT JOIN providers pr ON mr.provider_id = pr.id
            LEFT JOIN users u ON pr.user_id = u.id
            WHERE mr.patient_id = ?
            ORDER BY mr.record_date DESC
        ", [$patientId]);
        return $stmt->fetchAll();
    }

    public function findByProviderId(int $providerId): array
    {
        $stmt = $this->db->prepareAndExecute("
            SELECT mr.*, u.first_name, u.last_name
            FROM {$this->table} mr
            LEFT JOIN patients pa ON mr.patient_id = pa.id
            LEFT JOIN users u ON pa.user_id = u.id
            WHERE mr.provider_id = ?
            ORDER BY mr.record_date DESC
        ", [$providerId]);
        return $stmt->fetchAll();
    }
}
