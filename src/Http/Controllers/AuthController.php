<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use App\Security\Csrf;
use App\Validation\LoginValidator;
use App\View;

final class AuthController
{
    // Verify against a real hash even when the email is unknown to reduce timing differences.
    private const DUMMY_PASSWORD_HASH = '$2y$12$sFLZhs9qeJyN6lEv52IcCuiiVjbZ97wrR7zPIX8JCl02YD/Lh3lHS';

    public function __construct(
        private readonly View $view,
        private readonly LoginValidator $validator,
        private readonly UserRepository $users,
        private readonly Csrf $csrf,
    ) {
    }

    public function show(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/account');
        }

        echo $this->view->render('login', [
            'pageTitle' => 'Login',
            'csrfToken' => $this->csrf->token(),
            'errors' => $this->pull('login_errors', []),
            'old' => $this->pull('login_old', []),
        ]);
    }

    public function store(): void
    {
        if (!$this->csrf->isValid($_POST['_token'] ?? null)) {
            http_response_code(419);
            echo $this->view->render('error', [
                'pageTitle' => 'Session expired',
                'message' => 'Your form session expired. Please return to the login page and try again.',
                'returnUrl' => '/login',
                'returnLabel' => 'Return to login',
            ]);
            return;
        }

        $result = $this->validator->validate($_POST);
        $data = $result['data'];

        if ($result['errors'] !== []) {
            $this->returnWithErrors($data['email'], $result['errors']);
        }

        $user = $this->users->findByEmail($data['email']);
        $passwordHash = $user['password_hash'] ?? self::DUMMY_PASSWORD_HASH;
        $passwordMatches = password_verify($data['password'], $passwordHash);

        // One message for both failures prevents the login form from revealing registered emails.
        if ($user === null || !$passwordMatches) {
            $this->returnWithErrors($data['email'], [
                'credentials' => 'The email address or password is incorrect.',
            ]);
        }

        // Upgrade stored hashes quietly when PHP's password defaults change.
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $this->users->updatePasswordHash(
                (int) $user['id'],
                password_hash($data['password'], PASSWORD_DEFAULT),
            );
        }

        // A fresh session ID prevents the pre-login ID from being reused after authentication.
        session_regenerate_id(true);
        $_SESSION['auth_user_id'] = (int) $user['id'];
        $_SESSION['auth_user_name'] = $user['first_name'];
        $this->csrf->refresh();

        $this->redirect('/account');
    }

    public function account(): void
    {
        // Account details are only rendered when the session contains a logged-in user.
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        echo $this->view->render('account', [
            'pageTitle' => 'Your account',
            'firstName' => (string) $_SESSION['auth_user_name'],
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function logout(): void
    {
        if (!$this->csrf->isValid($_POST['_token'] ?? null)) {
            http_response_code(419);
            echo $this->view->render('error', [
                'pageTitle' => 'Session expired',
                'message' => 'Your session expired. Please return to the login page.',
                'returnUrl' => '/login',
                'returnLabel' => 'Return to login',
            ]);
            return;
        }

        // Drop authentication data and rotate the ID so the old session cannot be reused.
        unset($_SESSION['auth_user_id'], $_SESSION['auth_user_name']);
        session_regenerate_id(true);
        $this->csrf->refresh();

        $this->redirect('/login');
    }

    /** @param array<string, string> $errors */
    private function returnWithErrors(string $email, array $errors): never
    {
        $_SESSION['login_errors'] = $errors;
        $_SESSION['login_old'] = ['email' => $email];
        $this->redirect('/login');
    }

    private function isAuthenticated(): bool
    {
        return isset($_SESSION['auth_user_id'], $_SESSION['auth_user_name'])
            && is_int($_SESSION['auth_user_id'])
            && is_string($_SESSION['auth_user_name']);
    }

    private function redirect(string $path): never
    {
        header('Location: ' . $path, true, 303);
        exit;
    }

    private function pull(string $key, mixed $default): mixed
    {
        $value = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);

        return $value;
    }
}
