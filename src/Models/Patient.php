<?php

declare(strict_types=1);

namespace PIS\Models;

class Patient extends Model
{
    protected string $table = 'patients';

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (user_id, medical_record_number, date_of_birth, blood_type, phone, address, emergency_contact, insurance_info, created_at)
            VALUES (:user_id, :medical_record_number, :date_of_birth, :blood_type, :phone, :address, :emergency_contact, :insurance_info, NOW())
        ");

        $stmt->execute([
            'user_id' => $data['user_id'],
            'medical_record_number' => $data['medical_record_number'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'blood_type' => $data['blood_type'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'insurance_info' => $data['insurance_info'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
