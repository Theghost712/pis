<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Patient
{
    private int $id;
    private int $userId;
    private string $dateOfBirth;
    private string $gender;
    private string $phone;
    private string $address;
    private ?string $emergencyContactName;
    private ?string $emergencyContactPhone;
    private ?string $bloodType;
    private ?string $allergies;
    private string $createdAt;
    private ?string $updatedAt;

    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function findById(int $id): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM patients WHERE id = ? LIMIT 1",
            [$id]
        );
        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }
        return $this->hydrate($data);
    }

    public function find(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM patients WHERE id = ? LIMIT 1",
            [$id]
        );
        return $stmt->fetch() ?: null;
    }

    public function findByUserId(?int $userId): ?self
    {
        if ($userId === null) return null;
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM patients WHERE user_id = ? LIMIT 1",
            [$userId]
        );
        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }
        return $this->hydrate($data);
    }

    public function all(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute("SELECT p.*, u.username, u.email, u.first_name, u.last_name FROM patients p JOIN users u ON p.user_id = u.id ORDER BY u.last_name ASC");
        return $stmt->fetchAll();
    }

    public function findAll(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $db = Database::getInstance();
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR p.phone LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        if (!empty($filters['gender'])) {
            $where[] = "p.gender = ?";
            $params[] = $filters['gender'];
        }

        $sql = "SELECT p.*, u.username, u.email, u.first_name, u.last_name, u.is_active 
                FROM patients p
                JOIN users u ON p.user_id = u.id";
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
            $where[] = "(u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR p.phone LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        if (!empty($filters['gender'])) {
            $where[] = "p.gender = ?";
            $params[] = $filters['gender'];
        }

        $sql = "SELECT COUNT(*) FROM patients p JOIN users u ON p.user_id = u.id";
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
            "INSERT INTO patients (user_id, date_of_birth, gender, phone, address, 
             emergency_contact_name, emergency_contact_phone, blood_type, allergies) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'],
                $data['date_of_birth'],
                $data['gender'],
                $data['phone'],
                $data['address'],
                $data['emergency_contact_name'] ?? null,
                $data['emergency_contact_phone'] ?? null,
                $data['blood_type'] ?? null,
                $data['allergies'] ?? null,
            ]
        );
        return $db->lastInsertId();
    }

    public function update(array $data, ?int $id = null): bool
    {
        $db = Database::getInstance();
        $fields = [];
        $params = [];

        $allowedFields = ['date_of_birth', 'gender', 'phone', 'address', 'emergency_contact_name', 
                         'emergency_contact_phone', 'blood_type', 'allergies'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return true;
        }

        $patientId = $id ?? $this->id;
        $params[] = $patientId;
        $sql = "UPDATE patients SET " . implode(', ', $fields) . " WHERE id = ?";
        $db->prepareAndExecute($sql, $params);
        return true;
    }

    public function delete(?int $id = null): bool
    {
        $db = Database::getInstance();
        $patientId = $id ?? $this->id;
        $db->prepareAndExecute(
            "DELETE FROM patients WHERE id = ?",
            [$patientId]
        );
        return true;
    }

    public function getAge(): int
    {
        $today = new \DateTime();
        $dob = new \DateTime($this->dateOfBirth);
        $diff = $today->diff($dob);
        return $diff->y;
    }

    public function getMedicalRecords(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT mr.*, u.first_name, u.last_name 
             FROM medical_records mr
             JOIN providers p ON mr.provider_id = p.id
             JOIN users u ON p.user_id = u.id
             WHERE mr.patient_id = ? AND mr.is_active = 1 
             ORDER BY mr.record_date DESC",
            [$this->id]
        );
        return $stmt->fetchAll();
    }

    public function getActiveConsents(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT c.*, u.first_name, u.last_name, p.specialization 
             FROM consent c
             JOIN providers p ON c.provider_id = p.id
             JOIN users u ON p.user_id = u.id
             WHERE c.patient_id = ? AND c.status = 'active' AND c.expires_at > NOW()
             ORDER BY c.expires_at ASC",
            [$this->id]
        );
        return $stmt->fetchAll();
    }

    public function hasConsent(int $providerId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT COUNT(*) FROM consent 
             WHERE patient_id = ? AND provider_id = ? AND status = 'active' AND expires_at > NOW()",
            [$this->id, $providerId]
        );
        return $stmt->fetchColumn() > 0;
    }

    public function grantConsent(int $providerId, string $scope, string $expiresAt): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "INSERT INTO consent (patient_id, provider_id, scope, expires_at) 
             VALUES (?, ?, ?, ?)",
            [$this->id, $providerId, $scope, $expiresAt]
        );
        return $db->lastInsertId();
    }

    public function revokeConsent(int $consentId): bool
    {
        $db = Database::getInstance();
        $db->prepareAndExecute(
            "UPDATE consent SET status = 'revoked' WHERE id = ? AND patient_id = ?",
            [$consentId, $this->id]
        );
        return true;
    }

    public function getUser(): ?User
    {
        return $this->user->findById($this->userId);
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
        $this->dateOfBirth = $data['date_of_birth'];
        $this->gender = $data['gender'];
        $this->phone = $data['phone'];
        $this->address = $data['address'];
        $this->emergencyContactName = $data['emergency_contact_name'] ?? null;
        $this->emergencyContactPhone = $data['emergency_contact_phone'] ?? null;
        $this->bloodType = $data['blood_type'] ?? null;
        $this->allergies = $data['allergies'] ?? null;
        $this->createdAt = $data['created_at'];
        $this->updatedAt = $data['updated_at'] ?? null;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'date_of_birth' => $this->dateOfBirth,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'address' => $this->address,
            'emergency_contact_name' => $this->emergencyContactName,
            'emergency_contact_phone' => $this->emergencyContactPhone,
            'blood_type' => $this->bloodType,
            'allergies' => $this->allergies,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getDateOfBirth(): string { return $this->dateOfBirth; }
    public function getGender(): string { return $this->gender; }
    public function getPhone(): string { return $this->phone; }
    public function getAddress(): string { return $this->address; }
    public function getEmergencyContactName(): ?string { return $this->emergencyContactName; }
    public function getEmergencyContactPhone(): ?string { return $this->emergencyContactPhone; }
    public function getBloodType(): ?string { return $this->bloodType; }
    public function getAllergies(): ?string { return $this->allergies; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setUserId(int $userId): void { $this->userId = $userId; }
    public function setDateOfBirth(string $dateOfBirth): void { $this->dateOfBirth = $dateOfBirth; }
    public function setGender(string $gender): void { $this->gender = $gender; }
    public function setPhone(string $phone): void { $this->phone = $phone; }
    public function setAddress(string $address): void { $this->address = $address; }
    public function setEmergencyContactName(?string $name): void { $this->emergencyContactName = $name; }
    public function setEmergencyContactPhone(?string $phone): void { $this->emergencyContactPhone = $phone; }
    public function setBloodType(?string $bloodType): void { $this->bloodType = $bloodType; }
    public function setAllergies(?string $allergies): void { $this->allergies = $allergies; }
}
