<?php

declare(strict_types=1);

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

return [
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'Patient Information Sharing System',
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'timezone' => 'UTC',
    ],
    'session' => [
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 1800),
        'secure' => filter_var($_ENV['SESSION_SECURE'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'httponly' => filter_var($_ENV['SESSION_HTTPONLY'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'samesite' => $_ENV['SESSION_SAMESITE'] ?? 'Strict',
    ],
    'security' => [
        'salt_rounds' => (int)($_ENV['SALT_ROUNDS'] ?? 12),
        'encryption_key' => $_ENV['ENCRYPTION_KEY'] ?? '',
        'rate_limit' => [
            'requests' => (int)($_ENV['RATE_LIMIT_REQUESTS'] ?? 100),
            'time' => (int)($_ENV['RATE_LIMIT_TIME'] ?? 60),
        ],
    ],
    'mail' => [
        'host' => $_ENV['MAIL_HOST'] ?? '',
        'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'from' => $_ENV['MAIL_FROM'] ?? '',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Patient Information Sharing System',
    ],
    'mfa' => [
        'issuer' => $_ENV['MFA_ISSUER'] ?? 'PatientInfoSharing',
        'algorithm' => $_ENV['MFA_ALGORITHM'] ?? 'SHA1',
        'digits' => (int)($_ENV['MFA_DIGITS'] ?? 6),
        'period' => (int)($_ENV['MFA_PERIOD'] ?? 30),
    ],
];
