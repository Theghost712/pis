<?php

declare(strict_types=1);

namespace PIS\Services;

use PIS\Models\User;

class AuthService
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function generateToken(array $user): string
    {
        $config = require BASE_PATH . '/config/config.php';
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'exp' => time() + 3600,
        ];

        return $this->encodeJwt($payload, $config['encryption_key']);
    }

    public function validateToken(string $token): ?array
    {
        $config = require BASE_PATH . '/config/config.php';
        $payload = $this->decodeJwt($token, $config['encryption_key']);

        if (!$payload || $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    public function getCurrentUser(): ?array
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return null;
        }

        $payload = $this->validateToken($matches[1]);

        if (!$payload) {
            return null;
        }

        return $this->userModel->find($payload['user_id']);
    }

    private function encodeJwt(array $payload, string $secret): string
    {
        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = $this->base64UrlEncode(json_encode($payload));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', "$header.$payload", $secret, true));

        return "$header.$payload.$signature";
    }

    private function decodeJwt(string $token, string $secret): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;
        $expectedSignature = $this->base64UrlEncode(hash_hmac('sha256', "$header.$payload", $secret, true));

        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        return json_decode($this->base64UrlDecode($payload), true);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
