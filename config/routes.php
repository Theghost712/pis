<?php

declare(strict_types=1);

/** @var App\Core\Router $router */

// ============ WEB ROUTES ============

// Auth
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@forgotPasswordForm');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password', 'AuthController@resetPasswordForm');
$router->post('/reset-password', 'AuthController@resetPassword');
$router->get('/setup-mfa', 'AuthController@setupMFAForm');
$router->post('/setup-mfa/enable', 'AuthController@enableMFA');
$router->post('/setup-mfa/disable', 'AuthController@disableMFA');

// Dashboard
$router->get('/dashboard', 'DashboardController@patientDashboard');
$router->get('/patient/dashboard', 'DashboardController@patientDashboard');
$router->get('/provider/dashboard', 'DashboardController@providerDashboard');
$router->get('/admin/dashboard', 'DashboardController@adminDashboard');

// Patient routes
$router->get('/patient/profile', 'PatientController@profile');
$router->post('/patient/profile', 'PatientController@updateProfile');
$router->get('/patient/records', 'PatientController@records');
$router->get('/consent', 'ConsentController@index');
$router->post('/consent/create', 'ConsentController@create');
$router->post('/consent/revoke', 'ConsentController@revoke');
$router->post('/consent/renew', 'ConsentController@renew');

// Provider routes
$router->get('/provider/patients', 'ProviderController@patients');
$router->get('/provider/patient/{id}', 'ProviderController@patientView');
$router->get('/provider/records/add', 'RecordController@addForm');
$router->post('/provider/records/add', 'RecordController@webStore');
$router->get('/provider/referrals', 'ProviderController@referrals');

// Admin routes
$router->get('/admin/users', 'AdminController@users');
$router->post('/admin/users', 'AdminController@users');
$router->get('/admin/audit', 'AdminController@audit');
$router->get('/admin/reports', 'AdminController@reports');

// ============ API ROUTES ============

// Auth API
$router->post('/api/auth/login', 'AuthController@apiLogin');
$router->post('/api/auth/register', 'AuthController@apiRegister');
$router->post('/api/auth/logout', 'AuthController@apiLogout');
$router->get('/api/auth/me', 'AuthController@me');

// Patient API
$router->get('/api/patients', 'PatientController@index');
$router->get('/api/patients/{id}', 'PatientController@show');
$router->post('/api/patients', 'PatientController@store');
$router->put('/api/patients/{id}', 'PatientController@update');
$router->delete('/api/patients/{id}', 'PatientController@destroy');

// Provider API
$router->get('/api/providers', 'ProviderController@index');
$router->get('/api/providers/{id}', 'ProviderController@show');
$router->post('/api/providers', 'ProviderController@store');
$router->put('/api/providers/{id}', 'ProviderController@update');
$router->delete('/api/providers/{id}', 'ProviderController@destroy');

// Records API
$router->get('/api/records', 'MedicalRecordController@index');
$router->get('/api/records/{id}', 'MedicalRecordController@show');
$router->post('/api/records', 'MedicalRecordController@store');
$router->put('/api/records/{id}', 'MedicalRecordController@update');
$router->delete('/api/records/{id}', 'MedicalRecordController@destroy');

// Consent API
$router->get('/api/consent', 'ConsentController@index');
$router->get('/api/consent/{id}', 'ConsentController@show');
$router->post('/api/consent', 'ConsentController@store');
$router->put('/api/consent/{id}', 'ConsentController@update');
$router->delete('/api/consent/{id}', 'ConsentController@destroy');
