<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use App\Security\Csrf;
use App\Validation\RegistrationValidator;
use App\View;
use PDOException;

final class RegistrationController
{
    public function __construct(
        private readonly View $view,
        private readonly RegistrationValidator $validator,
        private readonly UserRepository $users,
        private readonly Csrf $csrf,
    ) {
    }

    public function show(): void
    {
        echo $this->view->render('register', [
            'pageTitle' => 'Register',
            'csrfToken' => $this->csrf->token(),
            'errors' => $this->pull('registration_errors', []),
            'old' => $this->pull('registration_old', []),
        ]);
    }

    public function store(): void
    {
        // Reject expired or forged form submissions before processing their contents.
        if (!$this->csrf->isValid($_POST['_token'] ?? null)) {
            http_response_code(419);
            echo $this->view->render('error', [
                'pageTitle' => 'Session expired',
                'message' => 'Your form session expired. Please return to the form and try again.',
            ]);
            return;
        }

        // Browser checks improve the experience, but this server validation is authoritative.
        $result = $this->validator->validate($_POST);
        $data = $result['data'];
        $errors = $result['errors'];

        // Give a friendly duplicate message here; the insert catch below also covers race conditions.
        if (!isset($errors['email']) && $this->users->emailExists($data['email'])) {
            $errors['email'] = 'An account with this email address already exists.';
        }

        if ($errors !== []) {
            $this->returnWithErrors($data, $errors);
        }

        // Keep only the hash; the plain password is never written to the database or session.
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);

        try {
            $this->users->create($data);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000'
                || str_contains($exception->getMessage(), 'UNIQUE constraint failed')) {
                $this->returnWithErrors($data, [
                    'email' => 'An account with this email address already exists.',
                ]);
            }

            throw $exception;
        }

        unset($_SESSION['registration_old'], $_SESSION['registration_errors']);
        // This one-time session flag prevents direct access to the success page.
        $_SESSION['registration_success'] = true;
        $this->csrf->refresh();
        $this->redirect('/register/success');
    }

    public function success(): void
    {
        if ($this->pull('registration_success', false) !== true) {
            $this->redirect('/register');
        }

        echo $this->view->render('success', [
            'pageTitle' => 'Registration complete',
        ]);
    }

    /**
     * @param array<string, string> $data
     * @param array<string, string> $errors
     */
    private function returnWithErrors(array $data, array $errors): never
    {
        // Redirect after POST so refreshing the form cannot submit it a second time.
        unset($data['password']);
        $_SESSION['registration_errors'] = $errors;
        $_SESSION['registration_old'] = $data;
        $this->redirect('/register');
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
