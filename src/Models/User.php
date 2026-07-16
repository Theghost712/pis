<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Security;

class User
{
    protected int $id;
    protected string $username;
    protected string $email;
    protected string $firstName;
    protected string $lastName;
    protected string $role;
    protected bool $isActive;
    protected bool $isVerified;
    protected ?string $mfaSecret;
    protected bool $mfaEnabled;
    protected string $createdAt;
    protected ?string $updatedAt;
    protected ?string $lastLogin;

    private Security $security;

    public function __construct()
    {
        $this->security = new Security();
    }

    public function findById(int $id): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM users WHERE id = ? LIMIT 1",
            [$id]
        );
        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }
        return $this->hydrate($data);
    }

    public function findByUsername(string $username): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM users WHERE username = ? LIMIT 1",
            [$username]
        );
        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }
        return $this->hydrate($data);
    }

    public function findByEmail(string $email): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT * FROM users WHERE email = ? LIMIT 1",
            [$email]
        );
        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }
        return $this->hydrate($data);
    }

    public function findAll(array $filters = [], string $orderBy = 'created_at DESC', int $limit = 100, int $offset = 0): array
    {
        $db = Database::getInstance();
        $where = [];
        $params = [];

        if (!empty($filters['role'])) {
            $where[] = "role = ?";
            $params[] = $filters['role'];
        }
        if (isset($filters['is_active'])) {
            $where[] = "is_active = ?";
            $params[] = (int) $filters['is_active'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql = "SELECT * FROM users";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY {$orderBy}";
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

        if (!empty($filters['role'])) {
            $where[] = "role = ?";
            $params[] = $filters['role'];
        }
        if (isset($filters['is_active'])) {
            $where[] = "is_active = ?";
            $params[] = (int) $filters['is_active'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql = "SELECT COUNT(*) FROM users";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $stmt = $db->prepareAndExecute($sql, $params);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $db = Database::getInstance();
        $hashedPassword = $this->security->hashPassword($data['password']);

        $db->prepareAndExecute(
            "INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_verified) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $data['username'],
                $data['email'],
                $hashedPassword,
                $data['first_name'],
                $data['last_name'],
                $data['role'] ?? 'patient',
                $data['is_verified'] ?? 0,
            ]
        );

        return $db->lastInsertId();
    }

    public function update(array $data): bool
    {
        $db = Database::getInstance();
        $fields = [];
        $params = [];

        $allowedFields = ['first_name', 'last_name', 'email', 'is_active', 'is_verified', 'mfa_secret', 'mfa_enabled', 'last_login'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (isset($data['password'])) {
            $fields[] = "password_hash = ?";
            $params[] = $this->security->hashPassword($data['password']);
        }

        if (empty($fields)) {
            return true;
        }

        $params[] = $this->id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $db->prepareAndExecute($sql, $params);
        return true;
    }

    public function delete(): bool
    {
        $db = Database::getInstance();
        $db->prepareAndExecute(
            "UPDATE users SET is_active = 0 WHERE id = ?",
            [$this->id]
        );
        $this->isActive = false;
        return true;
    }

    public function hardDelete(): bool
    {
        $db = Database::getInstance();
        $db->prepareAndExecute(
            "DELETE FROM users WHERE id = ?",
            [$this->id]
        );
        return true;
    }

    public function authenticate(string $password): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT password_hash FROM users WHERE id = ?",
            [$this->id]
        );
        $data = $stmt->fetch();
        if (!$data) {
            return false;
        }
        return $this->security->verifyPassword($password, $data['password_hash']);
    }

    public function updateLastLogin(): void
    {
        $db = Database::getInstance();
        $db->prepareAndExecute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$this->id]
        );
        $this->lastLogin = date('Y-m-d H:i:s');
    }

    public function enableMFA(string $secret): void
    {
        $this->mfaSecret = $secret;
        $this->mfaEnabled = true;
        $this->update([
            'mfa_secret' => $secret,
            'mfa_enabled' => true,
        ]);
    }

    public function disableMFA(): void
    {
        $this->mfaSecret = null;
        $this->mfaEnabled = false;
        $this->update([
            'mfa_secret' => null,
            'mfa_enabled' => false,
        ]);
    }

    public function verifyMFA(string $code): bool
    {
        if (!$this->mfaSecret) {
            return false;
        }
        return $this->security->verifyMfaCode($this->mfaSecret, $code);
    }

    public function getMfaQrCode(): string
    {
        if (!$this->mfaSecret) {
            return '';
        }
        return $this->security->generateMfaQrCode($this->mfaSecret, $this->email);
    }

    public function getPermissions(): array
    {
        $permissions = [
            'patient' => ['view_own_records', 'manage_consent', 'view_profile', 'update_profile'],
            'provider' => ['view_patient_records', 'add_records', 'share_records', 'view_referrals', 'create_referrals'],
            'admin' => ['manage_users', 'view_audit', 'generate_reports', 'system_config'],
            'system_admin' => ['manage_users', 'view_audit', 'generate_reports', 'system_config', 'manage_admins'],
        ];
        return $permissions[$this->role] ?? [];
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions());
    }

    public function getDashboard(): string
    {
        $dashboards = [
            'patient' => '/patient/dashboard',
            'provider' => '/provider/dashboard',
            'admin' => '/admin/dashboard',
            'system_admin' => '/admin/dashboard',
        ];
        return $dashboards[$this->role] ?? '/';
    }

    protected function hydrate(array $data): self
    {
        $this->id = (int) $data['id'];
        $this->username = $data['username'];
        $this->email = $data['email'];
        $this->firstName = $data['first_name'];
        $this->lastName = $data['last_name'];
        $this->role = $data['role'];
        $this->isActive = (bool) $data['is_active'];
        $this->isVerified = (bool) $data['is_verified'];
        $this->mfaSecret = $data['mfa_secret'] ?? null;
        $this->mfaEnabled = (bool) ($data['mfa_enabled'] ?? false);
        $this->createdAt = $data['created_at'];
        $this->updatedAt = $data['updated_at'] ?? null;
        $this->lastLogin = $data['last_login'] ?? null;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'full_name' => $this->getFullName(),
            'role' => $this->role,
            'is_active' => $this->isActive,
            'is_verified' => $this->isVerified,
            'mfa_enabled' => $this->mfaEnabled,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'last_login' => $this->lastLogin,
        ];
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getFullName(): string { return $this->firstName . ' ' . $this->lastName; }
    public function getRole(): string { return $this->role; }
    public function isActive(): bool { return $this->isActive; }
    public function isVerified(): bool { return $this->isVerified; }
    public function getMfaSecret(): ?string { return $this->mfaSecret; }
    public function isMFAEnabled(): bool { return $this->mfaEnabled; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function getLastLogin(): ?string { return $this->lastLogin; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setUsername(string $username): void { $this->username = $username; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setFirstName(string $firstName): void { $this->firstName = $firstName; }
    public function setLastName(string $lastName): void { $this->lastName = $lastName; }
    public function setRole(string $role): void { $this->role = $role; }
    public function setIsActive(bool $isActive): void { $this->isActive = $isActive; }
    public function setIsVerified(bool $isVerified): void { $this->isVerified = $isVerified; }
    public function setMfaSecret(?string $mfaSecret): void { $this->mfaSecret = $mfaSecret; }
    public function setMfaEnabled(bool $mfaEnabled): void { $this->mfaEnabled = $mfaEnabled; }
}
