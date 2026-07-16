<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Provider
{
    private int $id;
    private int $userId;
    private string $specialization;
    private string $licenseNumber;
    private int $hospitalId;
    private ?int $yearsOfExperience;
    private ?float $consultationFee;
    private string $createdAt;
    private ?string $updatedAt;

    private User $user;
    private Hospital $hospital;

    public function __construct()
    {
        $this->user = new User();
        $this->hospital = new Hospital();
    }

    public function findById(int $id): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM providers WHERE id = ? LIMIT 1",
            [$id]
        );
        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }
        return $this->hydrate($data);
    }

    public function findByUserId(int $userId): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM providers WHERE user_id = ? LIMIT 1",
            [$userId]
        );
        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }
        return $this->hydrate($data);
    }

    public function findAll(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $db = Database::getInstance();
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR p.specialization LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        if (!empty($filters['specialization'])) {
            $where[] = "p.specialization = ?";
            $params[] = $filters['specialization'];
        }
        if (!empty($filters['hospital_id'])) {
            $where[] = "p.hospital_id = ?";
            $params[] = $filters['hospital_id'];
        }

        $sql = "SELECT p.*, u.username, u.email, u.first_name, u.last_name, u.is_active, h.name as hospital_name
                FROM providers p
                JOIN users u ON p.user_id = u.id
                JOIN hospitals h ON p.hospital_id = h.id";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY u.last_name ASC, u.first_name ASC";
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepareAndExecute($sql, $params);
        return $stmt->fetchAll();
    }

    public function count(array $filters = []): int
    {
        $db = Database::getInstance();
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR p.specialization LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        if (!empty($filters['specialization'])) {
            $where[] = "p.specialization = ?";
            $params[] = $filters['specialization'];
        }
        if (!empty($filters['hospital_id'])) {
            $where[] = "p.hospital_id = ?";
            $params[] = $filters['hospital_id'];
        }

        $sql = "SELECT COUNT(*) FROM providers p JOIN users u ON p.user_id = u.id";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $stmt = $db->prepareAndExecute($sql, $params);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "INSERT INTO providers (user_id, specialization, license_number, hospital_id, 
             years_of_experience, consultation_fee) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'],
                $data['specialization'],
                $data['license_number'],
                $data['hospital_id'],
                $data['years_of_experience'] ?? null,
                $data['consultation_fee'] ?? null,
            ]
        );
        return $db->lastInsertId();
    }

    public function update(array $data): bool
    {
        $db = Database::getInstance();
        $fields = [];
        $params = [];

        $allowedFields = ['specialization', 'license_number', 'hospital_id', 'years_of_experience', 'consultation_fee'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return true;
        }

        $params[] = $this->id;
        $sql = "UPDATE providers SET " . implode(', ', $fields) . " WHERE id = ?";
        $db->prepareAndExecute($sql, $params);
        return true;
    }

    public function delete(): bool
    {
        $db = Database::getInstance();
        $db->prepareAndExecute(
            "DELETE FROM providers WHERE id = ?",
            [$this->id]
        );
        return true;
    }

    public function getPatients(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT DISTINCT p.*, u.first_name, u.last_name, u.email, u.username 
             FROM patients p
             JOIN users u ON p.user_id = u.id
             JOIN consent c ON c.patient_id = p.id
             WHERE c.provider_id = ? AND c.status = 'active' AND c.expires_at > NOW()
             ORDER BY u.last_name ASC, u.first_name ASC",
            [$this->id]
        );
        return $stmt->fetchAll();
    }

    public function getPatientsWithConsent(): array
    {
        return $this->getPatients();
    }

    public function getMedicalRecordsForPatient(int $patientId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM medical_records 
             WHERE patient_id = ? AND provider_id = ? AND is_active = 1 
             ORDER BY record_date DESC",
            [$patientId, $this->id]
        );
        return $stmt->fetchAll();
    }

    public function addMedicalRecord(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "INSERT INTO medical_records (patient_id, provider_id, record_type, record_date, 
             content, diagnosis, prescription, lab_results, notes) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['patient_id'],
                $this->id,
                $data['record_type'],
                $data['record_date'],
                json_encode($data['content'] ?? []),
                $data['diagnosis'] ?? null,
                $data['prescription'] ?? null,
                json_encode($data['lab_results'] ?? []),
                $data['notes'] ?? null,
            ]
        );
        return $db->lastInsertId();
    }

    public function createReferral(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "INSERT INTO referrals (from_provider_id, to_provider_id, patient_id, reason, notes) 
             VALUES (?, ?, ?, ?, ?)",
            [
                $this->id,
                $data['to_provider_id'],
                $data['patient_id'],
                $data['reason'],
                $data['notes'] ?? null,
            ]
        );
        return $db->lastInsertId();
    }

    public function getReferrals(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT r.*, 
             u1.first_name as from_first, u1.last_name as from_last,
             u2.first_name as to_first, u2.last_name as to_last,
             pt.first_name as patient_first, pt.last_name as patient_last
             FROM referrals r
             JOIN providers p1 ON r.from_provider_id = p1.id
             JOIN users u1 ON p1.user_id = u1.id
             JOIN providers p2 ON r.to_provider_id = p2.id
             JOIN users u2 ON p2.user_id = u2.id
             JOIN patients pa ON r.patient_id = pa.id
             JOIN users pt ON pa.user_id = pt.id
             WHERE r.from_provider_id = ? OR r.to_provider_id = ?
             ORDER BY r.created_at DESC",
            [$this->id, $this->id]
        );
        return $stmt->fetchAll();
    }

    public function getUser(): ?User
    {
        return $this->user->findById($this->userId);
    }

    public function getHospital(): ?Hospital
    {
        return $this->hospital->findById($this->hospitalId);
    }

    public function getFullName(): string
    {
        $user = $this->getUser();
        return $user ? $user->getFullName() : 'Unknown';
    }

    protected function hydrate(array $data): self
    {
        $this->id = (int) $data['id'];
        $this->userId = (int) $data['user_id'];
        $this->specialization = $data['specialization'];
        $this->licenseNumber = $data['license_number'];
        $this->hospitalId = (int) $data['hospital_id'];
        $this->yearsOfExperience = isset($data['years_of_experience']) ? (int) $data['years_of_experience'] : null;
        $this->consultationFee = isset($data['consultation_fee']) ? (float) $data['consultation_fee'] : null;
        $this->createdAt = $data['created_at'];
        $this->updatedAt = $data['updated_at'] ?? null;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'specialization' => $this->specialization,
            'license_number' => $this->licenseNumber,
            'hospital_id' => $this->hospitalId,
            'years_of_experience' => $this->yearsOfExperience,
            'consultation_fee' => $this->consultationFee,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getSpecialization(): string { return $this->specialization; }
    public function getLicenseNumber(): string { return $this->licenseNumber; }
    public function getHospitalId(): int { return $this->hospitalId; }
    public function getYearsOfExperience(): ?int { return $this->yearsOfExperience; }
    public function getConsultationFee(): ?float { return $this->consultationFee; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setUserId(int $userId): void { $this->userId = $userId; }
    public function setSpecialization(string $specialization): void { $this->specialization = $specialization; }
    public function setLicenseNumber(string $licenseNumber): void { $this->licenseNumber = $licenseNumber; }
    public function setHospitalId(int $hospitalId): void { $this->hospitalId = $hospitalId; }
    public function setYearsOfExperience(?int $years): void { $this->yearsOfExperience = $years; }
    public function setConsultationFee(?float $fee): void { $this->consultationFee = $fee; }
}
