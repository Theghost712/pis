<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;
use App\Services\AuthService;

class AuthMiddleware
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function handle(): void
    {
        if (!Session::has('user_id')) {
            if ($this->isApiRequest()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Authentication required']);
                exit;
            }

            Session::flash('error', 'Please log in to continue.');
            header('Location: /login');
            exit;
        }
    }

    public function guest(): void
    {
        if (Session::has('user_id')) {
            header('Location: /dashboard');
            exit;
        }
    }

    public function api(): void
    {
        $token = $this->extractBearerToken();

        if (!$token) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Token required']);
            exit;
        }

        $payload = $this->authService->validateToken($token);

        if (!$payload) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid or expired token']);
            exit;
        }

        $_SESSION['user_id'] = $payload['user_id'];
        $_SESSION['user_role'] = $payload['role'];
    }

    private function extractBearerToken(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return str_starts_with($uri, '/api/');
    }
}
