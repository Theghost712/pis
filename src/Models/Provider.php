<?php

declare(strict_types=1);

namespace PIS\Models;

class Provider extends Model
{
    protected string $table = 'providers';

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (user_id, license_number, specialization, hospital, department, phone, created_at)
            VALUES (:user_id, :license_number, :specialization, :hospital, :department, :phone, NOW())
        ");

        $stmt->execute([
            'user_id' => $data['user_id'],
            'license_number' => $data['license_number'],
            'specialization' => $data['specialization'],
            'hospital' => $data['hospital'] ?? null,
            'department' => $data['department'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findBySpecialization(string $specialization): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE specialization = :specialization");
        $stmt->execute(['specialization' => $specialization]);
        return $stmt->fetchAll();
    }
}
