<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

use PIS\Core\Router;
use PIS\Core\Session;

session_start();

$router = new Router();

require BASE_PATH . '/config/routes.php';

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
