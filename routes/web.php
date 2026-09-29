<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use App\Http\Router;
use App\Repositories\UserRepository;
use App\Security\Csrf;
use App\Validation\LoginValidator;
use App\Validation\RegistrationValidator;
use App\View;

/** @var Router $router */
/** @var View $view */
/** @var PDO $database */

// Controllers share the same repository and CSRF service for this request.
$users = new UserRepository($database);
$csrf = new Csrf();

// Controllers connect HTTP requests with validation, storage, and rendered pages.
$registration = new RegistrationController(
    $view,
    new RegistrationValidator(),
    $users,
    $csrf,
);

$auth = new AuthController(
    $view,
    new LoginValidator(),
    $users,
    $csrf,
);

// GET routes for displaying pages obviously and POST routes for actions that change state.
$router->get('/', static function (): void {
    header('Location: /register', true, 302);
});

$router->get('/register', static function () use ($registration): void {
    $registration->show();
});

$router->post('/register', static function () use ($registration): void {
    $registration->store();
});

$router->get('/register/success', static function () use ($registration): void {
    $registration->success();
});

$router->get('/login', static function () use ($auth): void {
    $auth->show();
});

$router->post('/login', static function () use ($auth): void {
    $auth->store();
});

$router->get('/account', static function () use ($auth): void {
    $auth->account();
});

$router->post('/logout', static function () use ($auth): void {
    $auth->logout();
});
