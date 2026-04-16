<?php

namespace App\Core\Security;

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    public static function validate(?string $token): bool
    {
        if (!isset($_SESSION['_csrf_token']) || $token === null) {
            return false;
        }

        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    public static function validateOrFail(?string $token): void
    {
        if (!self::validate($token)) {
            abort(419, 'Neplatný CSRF token.');
        }
    }

    public static function regenerate(): string
    {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['_csrf_token'];
    }
}