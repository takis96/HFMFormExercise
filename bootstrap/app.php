<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Http\Router;
use App\View;

$rootPath = dirname(__DIR__);
$autoloadPath = $rootPath . '/vendor/autoload.php';

if (!is_file($autoloadPath)) {
    throw new RuntimeException('Dependencies are missing. Run "composer install" first.');
}

require $autoloadPath;

// Configure the cookie before session_start so every form uses the same safe defaults.
ini_set('session.use_strict_mode', '1');
session_name('hfm_session');
session_set_cookie_params([
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Build the few shared services the application needs for each request.
$config = require $rootPath . '/config/app.php';
$database = Connection::make($config['database_path']);
$router = new Router();
$view = new View($rootPath . '/resources/views');

// The routes file receives these shared objects from this bootstrap scope.
require $rootPath . '/routes/web.php';

return [
    'config' => $config,
    'database' => $database,
    'router' => $router,
    'view' => $view,
];

