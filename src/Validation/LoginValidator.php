<?php

declare(strict_types=1);

namespace App\Validation;

final class LoginValidator
{
    /**
     * @param array<string, mixed> $input
     * @return array{data: array{email: string, password: string}, errors: array<string, string>}
     */
    public function validate(array $input): array
    {
        // Normalize email for lookup, but keep the password exactly as the user entered it.
        $email = is_string($input['email'] ?? null)
            ? mb_strtolower(trim($input['email']))
            : '';
        $password = is_string($input['password'] ?? null)
            ? $input['password']
            : '';
        // Collect all field errors so the user can correct them in one attempt.
        $errors = [];

        if ($email === '') {
            $errors['email'] = 'Please enter your email address.';
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Please enter a valid email address, for example name@example.com.';
        } elseif (strlen($email) > 254) {
            $errors['email'] = 'Email address must not exceed 254 characters.';
        }

        if ($password === '') {
            $errors['password'] = 'Please enter your password.';
        }

        return [
            'data' => ['email' => $email, 'password' => $password],
            'errors' => $errors,
        ];
    }
}
