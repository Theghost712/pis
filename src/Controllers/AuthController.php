<?php

namespace App\Controllers;

use App\Core\Session;
use App\Core\Validation;
use App\Core\Security;
use App\Models\User;
use App\Models\Patient;
use App\Models\Provider;
use App\Models\AuditLog;
use App\Services\NotificationService;

class AuthController
{
    private Session $session;
    private Validation $validation;
    private Security $security;
    private User $userModel;
    private Patient $patientModel;
    private Provider $providerModel;
    private AuditLog $auditLog;
    private NotificationService $notification;

    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->session->start();
        $this->validation = new Validation();
        $this->security = new Security();
        $this->userModel = new User();
        $this->patientModel = new Patient();
        $this->providerModel = new Provider();
        $this->auditLog = new AuditLog();
        $this->notification = new NotificationService();
    }

    public function loginForm(): void
    {
        if ($this->session->isLoggedIn()) {
            header('Location: ' . $this->getDashboardRedirect());
            exit;
        }
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void
    {
        $data = $_POST;
        
        $rules = [
            'username' => 'required',
            'password' => 'required|min:8'
        ];
        
        if (!$this->validation->validate($data, $rules)) {
            $this->session->setFlash('error', $this->validation->getFirstError());
            header('Location: /login');
            exit;
        }

        // Rate limiting check
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if ($this->security->isRateLimited('login_' . $ip)) {
            $this->session->setFlash('error', 'Too many login attempts. Please try again later.');
            header('Location: /login');
            exit;
        }

        $user = $this->userModel->findByUsername($data['username']);
        
        if (!$user || !$user->authenticate($data['password'])) {
            $this->auditLog->log([
                'action' => 'login_failed',
                'resource_type' => 'user',
                'details' => ['username' => $data['username']],
                'status' => 'failure'
            ]);
            $this->session->setFlash('error', 'Invalid username or password.');
            header('Location: /login');
            exit;
        }

        if (!$user->isActive()) {
            $this->session->setFlash('error', 'Your account has been deactivated.');
            header('Location: /login');
            exit;
        }

        if (!$user->isVerified()) {
            $this->session->setFlash('error', 'Please verify your email address.');
            header('Location: /login');
            exit;
        }

        // Check MFA
        if ($user->isMFAEnabled()) {
            $mfaCode = $_POST['mfa_code'] ?? null;
            if (!$mfaCode) {
                $this->session->set('mfa_pending_user_id', $user->getId());
                require_once __DIR__ . '/../Views/auth/mfa.php';
                return;
            }
            
            if (!$user->verifyMFA($mfaCode)) {
                $this->auditLog->log([
                    'user_id' => $user->getId(),
                    'action' => 'mfa_failed',
                    'resource_type' => 'user',
                    'resource_id' => $user->getId(),
                    'status' => 'failure'
                ]);
                $this->session->setFlash('error', 'Invalid MFA code.');
                header('Location: /login');
                exit;
            }
        }

        // Successful login
        $this->session->set('user_id', $user->getId());
        $this->session->set('user_role', $user->getRole());
        $this->session->set('user_name', $user->getFullName());
        
        $user->updateLastLogin();
        
        $this->auditLog->log([
            'user_id' => $user->getId(),
            'action' => 'login_success',
            'resource_type' => 'user',
            'resource_id' => $user->getId(),
            'status' => 'success'
        ]);

        $this->session->remove('mfa_pending_user_id');
        
        header('Location: ' . $user->getDashboard());
        exit;
    }

    public function logout(): void
    {
        if ($this->session->isLoggedIn()) {
            $this->auditLog->log([
                'user_id' => $this->session->getUserId(),
                'action' => 'logout',
                'resource_type' => 'user',
                'resource_id' => $this->session->getUserId(),
                'status' => 'success'
            ]);
        }
        $this->session->destroy();
        header('Location: /login');
        exit;
    }

    public function registerForm(): void
    {
        if ($this->session->isLoggedIn()) {
            header('Location: ' . $this->getDashboardRedirect());
            exit;
        }
        require_once __DIR__ . '/../Views/auth/register.php';
    }

    public function register(): void
    {
        $data = $_POST;
        
        $rules = [
            'username' => 'required|min:3|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'phone' => 'required|min:10|max:20',
            'address' => 'required|min:5'
        ];
        
        if (!$this->validation->validate($data, $rules)) {
            $this->session->setFlash('error', $this->validation->getFirstError());
            $this->session->setFlash('form_data', $data);
            header('Location: /register');
            exit;
        }

        // Create user
        $userId = $this->userModel->create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => 'patient'
        ]);

        if (!$userId) {
            $this->session->setFlash('error', 'Registration failed. Please try again.');
            header('Location: /register');
            exit;
        }

        // Create patient profile
        $patientId = $this->patientModel->create([
            'user_id' => $userId,
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'blood_type' => $data['blood_type'] ?? null,
            'allergies' => $data['allergies'] ?? null
        ]);

        if (!$patientId) {
            $this->userModel->hardDelete($userId);
            $this->session->setFlash('error', 'Registration failed. Please try again.');
            header('Location: /register');
            exit;
        }

        // Send verification email
        $this->notification->sendVerificationEmail(
            $data['email'],
            $data['first_name'] . ' ' . $data['last_name'],
            $userId
        );

        $this->auditLog->log([
            'action' => 'user_registered',
            'resource_type' => 'user',
            'resource_id' => $userId,
            'details' => ['username' => $data['username']],
            'status' => 'success'
        ]);

        $this->session->setFlash('success', 'Registration successful! Please check your email to verify your account.');
        header('Location: /login');
        exit;
    }

    public function verifyEmail(): void
    {
        $token = $_GET['token'] ?? null;
        
        if (!$token) {
            $this->session->setFlash('error', 'Invalid verification token.');
            header('Location: /login');
            exit;
        }

        $payload = $this->security->verifyJWT($token);
        
        if (!$payload || !isset($payload['user_id'])) {
            $this->session->setFlash('error', 'Invalid or expired verification token.');
            header('Location: /login');
            exit;
        }

        $user = $this->userModel->findById($payload['user_id']);
        
        if (!$user) {
            $this->session->setFlash('error', 'User not found.');
            header('Location: /login');
            exit;
        }

        if ($user->isVerified()) {
            $this->session->setFlash('info', 'Your email is already verified.');
            header('Location: /login');
            exit;
        }

        $user->setIsVerified(true);
        $user->update(['is_verified' => true]);

        $this->auditLog->log([
            'user_id' => $user->getId(),
            'action' => 'email_verified',
            'resource_type' => 'user',
            'resource_id' => $user->getId(),
            'status' => 'success'
        ]);

        $this->session->setFlash('success', 'Email verified successfully! You can now login.');
        header('Location: /login');
        exit;
    }

    public function forgotPasswordForm(): void
    {
        require_once __DIR__ . '/../Views/auth/forgot_password.php';
    }

    public function forgotPassword(): void
    {
        $email = $_POST['email'] ?? '';
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->session->setFlash('error', 'Please enter a valid email address.');
            header('Location: /forgot-password');
            exit;
        }

        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            // Don't reveal if email exists or not for security
            $this->session->setFlash('success', 'If an account exists, a password reset link has been sent.');
            header('Location: /login');
            exit;
        }

        // Rate limiting check
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if ($this->security->isRateLimited('password_reset_' . $ip)) {
            $this->session->setFlash('error', 'Too many requests. Please try again later.');
            header('Location: /forgot-password');
            exit;
        }

        // Generate password reset token
        $token = $this->security->generateJWT([
            'user_id' => $user->getId(),
            'type' => 'password_reset',
            'exp' => time() + 3600 // 1 hour
        ]);

        // Send reset email
        $this->notification->sendPasswordResetEmail(
            $user->getEmail(),
            $user->getFullName(),
            $token
        );

        $this->auditLog->log([
            'user_id' => $user->getId(),
            'action' => 'password_reset_requested',
            'resource_type' => 'user',
            'resource_id' => $user->getId(),
            'status' => 'success'
        ]);

        $this->session->setFlash('success', 'Password reset link sent to your email.');
        header('Location: /login');
        exit;
    }

    public function resetPasswordForm(): void
    {
        $token = $_GET['token'] ?? '';
        require_once __DIR__ . '/../Views/auth/reset_password.php';
    }

    public function resetPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        if (!$token) {
            $this->session->setFlash('error', 'Invalid reset token.');
            header('Location: /login');
            exit;
        }

        $payload = $this->security->verifyJWT($token);
        
        if (!$payload || !isset($payload['user_id']) || $payload['type'] !== 'password_reset') {
            $this->session->setFlash('error', 'Invalid or expired reset token.');
            header('Location: /login');
            exit;
        }

        if (strlen($password) < 8) {
            $this->session->setFlash('error', 'Password must be at least 8 characters.');
            header('Location: /reset-password?token=' . urlencode($token));
            exit;
        }

        if ($password !== $passwordConfirmation) {
            $this->session->setFlash('error', 'Passwords do not match.');
            header('Location: /reset-password?token=' . urlencode($token));
            exit;
        }

        $user = $this->userModel->findById($payload['user_id']);
        
        if (!$user) {
            $this->session->setFlash('error', 'User not found.');
            header('Location: /login');
            exit;
        }

        $user->update(['password' => $password]);

        $this->auditLog->log([
            'user_id' => $user->getId(),
            'action' => 'password_reset_success',
            'resource_type' => 'user',
            'resource_id' => $user->getId(),
            'status' => 'success'
        ]);

        $this->session->setFlash('success', 'Password reset successfully! You can now login.');
        header('Location: /login');
        exit;
    }

    public function setupMFAForm(): void
    {
        if (!$this->session->isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        $userId = $this->session->getUserId();
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            header('Location: /login');
            exit;
        }

        // Generate MFA secret if not already enabled
        if (!$user->isMFAEnabled()) {
            $secret = $this->security->generateMfaSecret();
            $user->setMfaSecret($secret);
            $user->update(['mfa_secret' => $secret]);
        }

        $qrCode = $user->getMfaQrCode();
        require_once __DIR__ . '/../Views/auth/setup_mfa.php';
    }

    public function enableMFA(): void
    {
        if (!$this->session->isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        $code = $_POST['code'] ?? '';
        $userId = $this->session->getUserId();
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            header('Location: /login');
            exit;
        }

        if (!$user->getMfaSecret()) {
            $this->session->setFlash('error', 'MFA secret not generated.');
            header('Location: /setup-mfa');
            exit;
        }

        if (!$user->verifyMFA($code)) {
            $this->session->setFlash('error', 'Invalid MFA code. Please try again.');
            header('Location: /setup-mfa');
            exit;
        }

        $user->enableMFA($user->getMfaSecret());

        $this->auditLog->log([
            'user_id' => $user->getId(),
            'action' => 'mfa_enabled',
            'resource_type' => 'user',
            'resource_id' => $user->getId(),
            'status' => 'success'
        ]);

        $this->session->setFlash('success', 'MFA enabled successfully.');
        header('Location: ' . $user->getDashboard());
        exit;
    }

    public function disableMFA(): void
    {
        if (!$this->session->isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        $userId = $this->session->getUserId();
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $user->disableMFA();

        $this->auditLog->log([
            'user_id' => $user->getId(),
            'action' => 'mfa_disabled',
            'resource_type' => 'user',
            'resource_id' => $user->getId(),
            'status' => 'success'
        ]);

        $this->session->setFlash('success', 'MFA disabled successfully.');
        header('Location: ' . $user->getDashboard());
        exit;
    }

    private function getDashboardRedirect(): string
    {
        $role = $this->session->getUserRole();
        $dashboards = [
            'patient' => '/patient/dashboard',
            'provider' => '/provider/dashboard',
            'admin' => '/admin/dashboard',
            'system_admin' => '/admin/dashboard'
        ];
        return $dashboards[$role] ?? '/';
    }
} 