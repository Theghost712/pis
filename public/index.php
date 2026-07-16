<?php
// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Set error reporting based on environment
if ($_ENV['APP_ENV'] === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Set timezone
date_default_timezone_set('UTC');

// Start session
session_start();

// Load router
require_once __DIR__ . '/../src/Core/Router.php';

$router = new \App\Core\Router();

// Auth routes
$router->addRoute('GET', '/', 'AuthController@loginForm');
$router->addRoute('GET', '/login', 'AuthController@loginForm');
$router->addRoute('POST', '/login', 'AuthController@login');
$router->addRoute('GET', '/register', 'AuthController@registerForm');
$router->addRoute('POST', '/register', 'AuthController@register');
$router->addRoute('GET', '/logout', 'AuthController@logout');
$router->addRoute('GET', '/verify-email', 'AuthController@verifyEmail');
$router->addRoute('GET', '/forgot-password', 'AuthController@forgotPasswordForm');
$router->addRoute('POST', '/forgot-password', 'AuthController@forgotPassword');
$router->addRoute('GET', '/reset-password', 'AuthController@resetPasswordForm');
$router->addRoute('POST', '/reset-password', 'AuthController@resetPassword');
$router->addRoute('GET', '/setup-mfa', 'AuthController@setupMFAForm');
$router->addRoute('POST', '/setup-mfa/enable', 'AuthController@enableMFA');
$router->addRoute('POST', '/setup-mfa/disable', 'AuthController@disableMFA');

// Patient routes
$router->addRoute('GET', '/patient/dashboard', 'DashboardController@patientDashboard');
$router->addRoute('GET', '/patient/profile', 'PatientController@profile');
$router->addRoute('POST', '/patient/profile', 'PatientController@updateProfile');
$router->addRoute('GET', '/patient/records', 'PatientController@records');
$router->addRoute('GET', '/consent', 'ConsentController@index');
$router->addRoute('POST', '/consent/create', 'ConsentController@create');
$router->addRoute('POST', '/consent/revoke', 'ConsentController@revoke');
$router->addRoute('POST', '/consent/renew', 'ConsentController@renew');

// Provider routes
$router->addRoute('GET', '/provider/dashboard', 'DashboardController@providerDashboard');
$router->addRoute('GET', '/provider/patients', 'ProviderController@patients');
$router->addRoute('GET', '/provider/patient/{id}', 'ProviderController@viewPatient');
$router->addRoute('POST', '/provider/patient/{id}/records', 'ProviderController@addRecord');
$router->addRoute('GET', '/provider/records', 'ProviderController@records');
$router->addRoute('GET', '/provider/referrals', 'ProviderController@referrals');
$router->addRoute('POST', '/provider/referrals/create', 'ProviderController@createReferral');

// Admin routes
$router->addRoute('GET', '/admin/dashboard', 'DashboardController@adminDashboard');
$router->addRoute('GET', '/admin/users', 'AdminController@users');
$router->addRoute('GET', '/admin/user/{id}', 'AdminController@viewUser');
$router->addRoute('POST', '/admin/user/{id}', 'AdminController@updateUser');
$router->addRoute('GET', '/admin/audit', 'AdminController@audit');
$router->addRoute('GET', '/admin/reports', 'AdminController@reports');
$router->addRoute('GET', '/admin/settings', 'AdminController@settings');
$router->addRoute('POST', '/admin/settings', 'AdminController@updateSettings');

// Dispatch request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);