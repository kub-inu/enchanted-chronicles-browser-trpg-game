<?php

namespace App\Modules\Auth\Services;

class AuthSessionService
{
    private const SESSION_ACTIVITY_KEY = 'last_activity_at';
    private const DEFAULT_TIMEOUT_SECONDS = 300; // 30 min - 1800

    public static function isExpired(int $timeoutSeconds = self::DEFAULT_TIMEOUT_SECONDS): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return true;
        }

        $lastActivity = $_SESSION[self::SESSION_ACTIVITY_KEY] ?? null;

        if ($lastActivity === null) {
            return false;
        }

        return (time() - (int) $lastActivity) > $timeoutSeconds;
    }

    public static function refreshActivity(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION[self::SESSION_ACTIVITY_KEY] = time();
    }

    public static function clearActivity(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        unset($_SESSION[self::SESSION_ACTIVITY_KEY]);
    }

    public static function getLastActivity(): ?int
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }

        return isset($_SESSION[self::SESSION_ACTIVITY_KEY])
            ? (int) $_SESSION[self::SESSION_ACTIVITY_KEY]
            : null;
    }
}