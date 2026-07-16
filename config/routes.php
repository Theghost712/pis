<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PatientController;
use App\Controllers\ProviderController;
use App\Controllers\RecordController;
use App\Controllers\ConsentController;
use App\Controllers\AdminController;
use App\Controllers\MedicalRecordController;

/** @var App\Core\Router $router */

// ============ WEB ROUTES ============

// Auth
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'forgotPasswordForm']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->get('/reset-password', [AuthController::class, 'resetPasswordForm']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);
$router->get('/mfa', [AuthController::class, 'mfaForm']);
$router->post('/mfa', [AuthController::class, 'mfaVerify']);

// Dashboard
$router->get('/dashboard', [DashboardController::class, 'index']);

// Patient routes
$router->get('/patient/profile', [PatientController::class, 'profile']);
$router->post('/patient/profile', [PatientController::class, 'updateProfile']);
$router->get('/patient/records', [PatientController::class, 'records']);
$router->get('/patient/consent', [ConsentController::class, 'patientConsent']);
$router->post('/patient/consent', [ConsentController::class, 'store']);
$router->post('/patient/consent/{id}/revoke', [ConsentController::class, 'webRevoke']);

// Provider routes
$router->get('/provider/patients', [ProviderController::class, 'patients']);
$router->get('/provider/patient/{id}', [ProviderController::class, 'patientView']);
$router->get('/provider/records/add', [RecordController::class, 'addForm']);
$router->post('/provider/records/add', [RecordController::class, 'store']);
$router->get('/provider/referrals', [ProviderController::class, 'referrals']);

// Admin routes
$router->get('/admin/users', [AdminController::class, 'users']);
$router->post('/admin/users', [AdminController::class, 'createUser']);
$router->get('/admin/audit', [AdminController::class, 'audit']);
$router->get('/admin/reports', [AdminController::class, 'reports']);

// ============ API ROUTES ============

// Auth API
$router->post('/api/auth/login', [AuthController::class, 'apiLogin']);
$router->post('/api/auth/register', [AuthController::class, 'apiRegister']);
$router->post('/api/auth/logout', [AuthController::class, 'apiLogout']);
$router->get('/api/auth/me', [AuthController::class, 'me']);

// Patient API
$router->get('/api/patients', [PatientController::class, 'index']);
$router->get('/api/patients/{id}', [PatientController::class, 'show']);
$router->post('/api/patients', [PatientController::class, 'store']);
$router->put('/api/patients/{id}', [PatientController::class, 'update']);
$router->delete('/api/patients/{id}', [PatientController::class, 'destroy']);

// Provider API
$router->get('/api/providers', [ProviderController::class, 'index']);
$router->get('/api/providers/{id}', [ProviderController::class, 'show']);
$router->post('/api/providers', [ProviderController::class, 'store']);
$router->put('/api/providers/{id}', [ProviderController::class, 'update']);
$router->delete('/api/providers/{id}', [ProviderController::class, 'destroy']);

// Records API
$router->get('/api/records', [MedicalRecordController::class, 'index']);
$router->get('/api/records/{id}', [MedicalRecordController::class, 'show']);
$router->post('/api/records', [MedicalRecordController::class, 'store']);
$router->put('/api/records/{id}', [MedicalRecordController::class, 'update']);
$router->delete('/api/records/{id}', [MedicalRecordController::class, 'destroy']);

// Consent API
$router->get('/api/consent', [ConsentController::class, 'index']);
$router->get('/api/consent/{id}', [ConsentController::class, 'show']);
$router->post('/api/consent', [ConsentController::class, 'store']);
$router->put('/api/consent/{id}', [ConsentController::class, 'update']);
$router->delete('/api/consent/{id}', [ConsentController::class, 'destroy']);
