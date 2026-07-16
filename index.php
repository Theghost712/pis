<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

require BASE_PATH . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

use App\Core\Router;

if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

date_default_timezone_set('UTC');
session_start();

$router = new Router();

require BASE_PATH . '/config/routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
