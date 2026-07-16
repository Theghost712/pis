<?php

declare(strict_types=1);

namespace PIS\Models;

class MedicalRecord extends Model
{
    protected string $table = 'medical_records';

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (patient_id, provider_id, record_type, title, description, diagnosis, notes, record_date, created_at)
            VALUES (:patient_id, :provider_id, :record_type, :title, :description, :diagnosis, :notes, :record_date, NOW())
        ");

        $stmt->execute([
            'patient_id' => $data['patient_id'],
            'provider_id' => $data['provider_id'] ?? null,
            'record_type' => $data['record_type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'diagnosis' => $data['diagnosis'] ?? null,
            'notes' => $data['notes'] ?? null,
            'record_date' => $data['record_date'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByPatientId(int $patientId): array
    {
        $stmt = $this->db->prepare("
            SELECT mr.*, p.name as provider_name
            FROM {$this->table} mr
            LEFT JOIN providers pr ON mr.provider_id = pr.id
            LEFT JOIN users p ON pr.user_id = p.id
            WHERE mr.patient_id = :patient_id
            ORDER BY mr.record_date DESC
        ");
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    public function findByProviderId(int $providerId): array
    {
        $stmt = $this->db->prepare("
            SELECT mr.*, pt.name as patient_name
            FROM {$this->table} mr
            LEFT JOIN patients pa ON mr.patient_id = pa.id
            LEFT JOIN users pt ON pa.user_id = pt.id
            WHERE mr.provider_id = :provider_id
            ORDER BY mr.record_date DESC
        ");
        $stmt->execute(['provider_id' => $providerId]);
        return $stmt->fetchAll();
    }
}
