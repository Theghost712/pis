<?php

declare(strict_types=1);

namespace App\Core;

class Security
{
    private array $config;
    private Session $session;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/config.php';
        $this->session = Session::getInstance();
    }

    public function hashPassword(string $password): string
    {
        $saltRounds = $this->config['security']['salt_rounds'];
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $saltRounds]);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function needsRehash(string $hash): bool
    {
        $saltRounds = $this->config['security']['salt_rounds'];
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $saltRounds]);
    }

    public function generateCSRFToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $token);
        return $token;
    }

    public function verifyCSRFToken(string $token): bool
    {
        $stored = $this->session->get('csrf_token');
        return $stored && hash_equals($stored, $token);
    }

    public function generateMfaSecret(): string
    {
        return base32_encode(random_bytes(16));
    }

    public function generateMfaQrCode(string $secret, string $email): string
    {
        $issuer = $this->config['mfa']['issuer'];
        $label = $issuer . ':' . $email;
        $totp = new \PHPGangsta_GoogleAuthenticator();
        return $totp->getQRCodeGoogleUrl($label, $secret, $issuer);
    }

    public function verifyMfaCode(string $secret, string $code): bool
    {
        if (empty($secret) || empty($code)) {
            return false;
        }

        $ga = new \PHPGangsta_GoogleAuthenticator();
        $period = $this->config['mfa']['period'] ?? 30;
        $digits = $this->config['mfa']['digits'] ?? 6;

        for ($i = -2; $i <= 2; $i++) {
            $time = floor(time() / $period) + $i;
            $expected = $ga->getCode($secret, $time, $digits);
            if ($code === $expected) {
                return true;
            }
        }
        return false;
    }

    public function sanitizeInput(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    public function sanitizeArray(array $data): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = $this->sanitizeInput($value);
            }
        });
        return $data;
    }

    public function encrypt(string $data): string
    {
        $key = $this->config['security']['encryption_key'];
        if (empty($key)) {
            throw new \RuntimeException('Encryption key not configured');
        }

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $encryptedData): string
    {
        $key = $this->config['security']['encryption_key'];
        if (empty($key)) {
            throw new \RuntimeException('Encryption key not configured');
        }

        $data = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    public function generateJWT(array $payload): string
    {
        $key = $this->config['security']['encryption_key'];
        if (empty($key)) {
            throw new \RuntimeException('JWT signing key not configured');
        }
        return \Firebase\JWT\JWT::encode($payload, $key, 'HS256');
    }

    public function verifyJWT(string $token): ?array
    {
        try {
            $key = $this->config['security']['encryption_key'];
            if (empty($key)) {
                throw new \RuntimeException('JWT verification key not configured');
            }
            return (array) \Firebase\JWT\JWT::decode($token, $key, ['HS256']);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function isRateLimited(string $identifier): bool
    {
        $config = $this->config['security']['rate_limit'];
        $key = "rate_limit_{$identifier}";
        $timestampKey = $key . '_timestamp';

        $count = (int) $this->session->get($key, 0);
        $timestamp = (int) $this->session->get($timestampKey, time());

        if (time() - $timestamp > $config['time']) {
            $this->session->set($key, 1);
            $this->session->set($timestampKey, time());
            return false;
        }

        if ($count >= $config['requests']) {
            return true;
        }

        $this->session->set($key, $count + 1);
        return false;
    }

    public function generateRandomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    public function generateSecureToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function validatePassword(string $password): bool
    {
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        return preg_match($pattern, $password) === 1;
    }

    public function getPasswordRequirements(): string
    {
        return 'Minimum 8 characters, at least one uppercase, one lowercase, one number, and one special character.';
    }
}

if (!function_exists('base32_encode')) {
    function base32_encode($data)
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $bytes = unpack('C*', $data);
        $byteCount = count($bytes);
        $shift = 0;
        $buffer = 0;

        for ($i = 0; $i < $byteCount; $i++) {
            $buffer = ($buffer << 8) | $bytes[$i + 1];
            $shift += 8;
            while ($shift >= 5) {
                $output .= $alphabet[($buffer >> ($shift - 5)) & 31];
                $shift -= 5;
            }
        }

        if ($shift > 0) {
            $output .= $alphabet[($buffer << (5 - $shift)) & 31];
        }

        while (strlen($output) % 8 !== 0) {
            $output .= '=';
        }

        return $output;
    }
}
