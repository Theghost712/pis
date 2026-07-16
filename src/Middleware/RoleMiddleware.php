<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

class RoleMiddleware
{
    public function handle(string ...$allowedRoles): void
    {
        $userRole = Session::get('user_role');

        if (!$userRole) {
            if ($this->isApiRequest()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Access denied']);
                exit;
            }

            header('Location: /dashboard');
            exit;
        }

        if (!in_array($userRole, $allowedRoles, true)) {
            if ($this->isApiRequest()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Insufficient permissions']);
                exit;
            }

            Session::flash('error', 'You do not have permission to access this page.');
            header('Location: /dashboard');
            exit;
        }
    }

    public function admin(): void
    {
        $this->handle('admin');
    }

    public function provider(): void
    {
        $this->handle('provider', 'admin');
    }

    public function patient(): void
    {
        $this->handle('patient', 'admin');
    }

    public function staff(): void
    {
        $this->handle('staff', 'admin');
    }

    public function providerOrAdmin(): void
    {
        $this->handle('provider', 'admin');
    }

    public function patientOrAdmin(): void
    {
        $this->handle('patient', 'admin');
    }

    private function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return str_starts_with($uri, '/api/');
    }
}
