<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

require BASE_PATH . '/vendor/autoload.php';

use App\Core\Router;
use App\Core\Security;
use App\Core\Session;

session_start();

Security::enforceHttps();

$router = new Router();

require BASE_PATH . '/config/routes.php';

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
