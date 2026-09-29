<?php

declare(strict_types=1);

namespace App\Validation;

final class RegistrationValidator
{
    /** @var array<string, string> */
    private const COUNTRIES = [
        'cyprus' => '357',
        'greece' => '30',
        'united_kingdom' => '44',
    ];

    /**
     * @param array<string, mixed> $input
     * @return array{data: array<string, string>, errors: array<string, string>}
     */
    public function validate(array $input): array
    {
        // Normalize every expected field once so validation and persistence use the same values.
        $data = [
            'first_name' => $this->text($input['first_name'] ?? ''),
            'last_name' => $this->text($input['last_name'] ?? ''),
            'country' => $this->text($input['country'] ?? ''),
            'country_code' => $this->text($input['country_code'] ?? ''),
            'phone' => $this->text($input['phone'] ?? ''),
            'email' => mb_strtolower($this->text($input['email'] ?? '')),
            // Passwords are not trimmed because spaces may be intentional.
            'password' => is_string($input['password'] ?? null) ? $input['password'] : '',
            'terms' => ($input['terms'] ?? null) === '1' ? '1' : '',
        ];

        $errors = [];

        $this->validateName($data['first_name'], 'first_name', 'First name', $errors);
        $this->validateName($data['last_name'], 'last_name', 'Last name', $errors);

        // Do not trust the select element: its submitted value can still be changed manually.
        if (!isset(self::COUNTRIES[$data['country']])) {
            $errors['country'] = 'Please select a valid country.';
        }

        // The plus sign is presentation only; we store digits and verify them against the country.
        if ($data['country_code'] === '' || !ctype_digit($data['country_code'])) {
            $errors['country_code'] = 'Country code must contain numbers only.';
        } elseif (isset(self::COUNTRIES[$data['country']])
            && $data['country_code'] !== self::COUNTRIES[$data['country']]) {
            $errors['country_code'] = 'Country code does not match the selected country.';
        }

        if ($data['phone'] === '' || !ctype_digit($data['phone'])) {
            $errors['phone'] = 'Phone number must contain numbers only.';
        } elseif (strlen($data['phone']) < 6 || strlen($data['phone']) > 15) {
            $errors['phone'] = 'Phone number must be between 6 and 15 digits.';
        }

        if ($data['email'] === '' || filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Please enter a valid email address, for example name@example.com.';
        } elseif (strlen($data['email']) > 254) {
            $errors['email'] = 'Email address must not exceed 254 characters.';
        }

        // Return the first unmet password rule so the message stays short and specific.
        if (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Z]/', $data['password'])) {
            $errors['password'] = 'Password must include at least one capital letter.';
        } elseif (!preg_match('/[0-9]/', $data['password'])) {
            $errors['password'] = 'Password must include at least one number.';
        } elseif (!preg_match('/[^A-Za-z0-9]/', $data['password'])) {
            $errors['password'] = 'Password must include at least one symbol.';
        }

        if ($data['terms'] !== '1') {
            $errors['terms'] = 'You must accept the Privacy Policy and Terms and Conditions.';
        }

        return ['data' => $data, 'errors' => $errors];
    }

    /** @param array<string, string> $errors */
    private function validateName(string $value, string $field, string $label, array &$errors): void
    {
        $length = mb_strlen($value);

        if ($length <= 3) {
            $errors[$field] = "{$label} must contain more than 3 characters.";
        } elseif ($length > 100) {
            $errors[$field] = "{$label} must not exceed 100 characters.";
        }
    }

    private function text(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }
}
