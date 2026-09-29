<?php

declare(strict_types=1);

namespace App\Security;

final class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    public function token(): string
    {
        // One unpredictable session token is shared by all state-changing forms.
        if (!isset($_SESSION[self::SESSION_KEY])) {
            // random_bytes provides a cryptographically secure value for the token.
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public function isValid(mixed $token): bool
    {
        // hash_equals avoids leaking useful timing differences during comparison.
        return is_string($token)
            && isset($_SESSION[self::SESSION_KEY])
            && hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    public function refresh(): void
    {
        // Rotate the token after authentication state changes or registration succeeds.
        unset($_SESSION[self::SESSION_KEY]);
    }
}
