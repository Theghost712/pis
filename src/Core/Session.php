<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    private static ?Session $instance = null;
    private array $config;
    private bool $started = false;

    private function __construct()
    {
        $this->config = require __DIR__ . '/../../config/config.php';
    }

    public static function getInstance(): Session
    {
        if (self::$instance === null) {
            self::$instance = new Session();
        }
        return self::$instance;
    }

    public function start(): void
    {
        if ($this->started) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            $config = $this->config['session'];

            session_set_cookie_params([
                'lifetime' => $config['lifetime'],
                'path' => '/',
                'domain' => '',
                'secure' => $config['secure'],
                'httponly' => $config['httponly'],
                'samesite' => $config['samesite'],
            ]);

            session_start();

            if (!isset($_SESSION['_created_at'])) {
                $_SESSION['_created_at'] = time();
            } elseif (time() - $_SESSION['_created_at'] > 300) {
                $this->regenerateId();
                $_SESSION['_created_at'] = time();
            }

            $this->started = true;
        }
    }

    public static function set(string $key, $value): void
    {
        $instance = self::getInstance();
        $instance->start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        $instance = self::getInstance();
        $instance->start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        $instance = self::getInstance();
        $instance->start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        $instance = self::getInstance();
        $instance->start();
        unset($_SESSION[$key]);
    }

    public static function clear(): void
    {
        $instance = self::getInstance();
        $instance->start();
        $_SESSION = [];
    }

    public static function destroy(): void
    {
        $instance = self::getInstance();
        $instance->start();
        $_SESSION = [];
        session_destroy();
        session_write_close();
        $instance->started = false;
    }

    public static function regenerateId(): void
    {
        $instance = self::getInstance();
        $instance->start();
        session_regenerate_id(true);
    }

    public static function isLoggedIn(): bool
    {
        $instance = self::getInstance();
        $instance->start();
        return $instance->has('user_id') && $instance->has('user_role');
    }

    public static function getUserId(): ?int
    {
        return self::get('user_id');
    }

    public static function getUserRole(): ?string
    {
        return self::get('user_role');
    }

    public static function getUserName(): ?string
    {
        return self::get('user_name');
    }

    public static function getUserEmail(): ?string
    {
        return self::get('user_email');
    }

    public static function setFlash(string $key, $value): void
    {
        $instance = self::getInstance();
        $instance->start();
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, $default = null)
    {
        $instance = self::getInstance();
        $instance->start();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        $instance = self::getInstance();
        $instance->start();
        return isset($_SESSION['_flash'][$key]);
    }
}
