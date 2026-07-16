<?php

declare(strict_types=1);

use PIS\Controllers\AuthController;
use PIS\Controllers\PatientController;
use PIS\Controllers\ProviderController;
use PIS\Controllers\MedicalRecordController;

/** @var PIS\Core\Router $router */

// Auth routes
$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/auth/logout', [AuthController::class, 'logout']);
$router->get('/api/auth/me', [AuthController::class, 'me']);

// Patient routes
$router->get('/api/patients', [PatientController::class, 'index']);
$router->get('/api/patients/{id}', [PatientController::class, 'show']);
$router->post('/api/patients', [PatientController::class, 'store']);
$router->put('/api/patients/{id}', [PatientController::class, 'update']);
$router->delete('/api/patients/{id}', [PatientController::class, 'destroy']);

// Provider routes
$router->get('/api/providers', [ProviderController::class, 'index']);
$router->get('/api/providers/{id}', [ProviderController::class, 'show']);
$router->post('/api/providers', [ProviderController::class, 'store']);
$router->put('/api/providers/{id}', [ProviderController::class, 'update']);
$router->delete('/api/providers/{id}', [ProviderController::class, 'destroy']);

// Medical Records routes
$router->get('/api/records', [MedicalRecordController::class, 'index']);
$router->get('/api/records/{id}', [MedicalRecordController::class, 'show']);
$router->post('/api/records', [MedicalRecordController::class, 'store']);
$router->put('/api/records/{id}', [MedicalRecordController::class, 'update']);
$router->delete('/api/records/{id}', [MedicalRecordController::class, 'destroy']);
