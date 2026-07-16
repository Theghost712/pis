<?php

declare(strict_types=1);

namespace PIS\Controllers;

use PIS\Models\User;
use PIS\Services\AuthService;

class AuthController extends Controller
{
    private User $userModel;
    private AuthService $authService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->authService = new AuthService();
    }

    public function login(): void
    {
        $input = $this->getInput();
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->authService->verifyPassword($password, $user['password'])) {
            $this->json(['error' => 'Invalid credentials'], 401);
            return;
        }

        $token = $this->authService->generateToken($user);

        $this->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role'],
            ],
        ]);
    }

    public function register(): void
    {
        $input = $this->getInput();

        if ($this->userModel->findByEmail($input['email'] ?? '')) {
            $this->json(['error' => 'Email already exists'], 409);
            return;
        }

        $data = [
            'name' => $input['name'] ?? '',
            'email' => $input['email'] ?? '',
            'password' => $this->authService->hashPassword($input['password'] ?? ''),
            'role' => $input['role'] ?? 'patient',
        ];

        $userId = $this->userModel->create($data);

        $this->json(['message' => 'User registered', 'id' => $userId], 201);
    }

    public function logout(): void
    {
        \PIS\Core\Session::destroy();
        $this->json(['message' => 'Logged out']);
    }

    public function me(): void
    {
        $user = $this->authService->getCurrentUser();

        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $this->json(['user' => $user]);
    }
}
