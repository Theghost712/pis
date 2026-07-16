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

    public function set(string $key, $value): void
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        $this->start();
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        $this->start();
        unset($_SESSION[$key]);
    }

    public function clear(): void
    {
        $this->start();
        $_SESSION = [];
    }

    public function destroy(): void
    {
        $this->start();
        $_SESSION = [];
        session_destroy();
        session_write_close();
        $this->started = false;
    }

    public function regenerateId(): void
    {
        $this->start();
        session_regenerate_id(true);
    }

    public function isLoggedIn(): bool
    {
        $this->start();
        return $this->has('user_id') && $this->has('user_role');
    }

    public function getUserId(): ?int
    {
        return $this->get('user_id');
    }

    public function getUserRole(): ?string
    {
        return $this->get('user_role');
    }

    public function getUserName(): ?string
    {
        return $this->get('user_name');
    }

    public function setFlash(string $key, $value): void
    {
        $this->start();
        $_SESSION['_flash'][$key] = $value;
    }

    public function getFlash(string $key, $default = null)
    {
        $this->start();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public function hasFlash(string $key): bool
    {
        $this->start();
        return isset($_SESSION['_flash'][$key]);
    }
}
