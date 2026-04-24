<?php

namespace App\Runtime\OnlineUsers;

use App\Runtime\OnlineUsers\OnlineUsersRepository;

class OnlineUsersService
{
    private const SESSION_TOUCH_KEY = '_online_users_last_touch';
    private const DEFAULT_TOUCH_INTERVAL = 30;
    private const DEFAULT_ONLINE_TIMEOUT = 90;
    private const DEFAULT_CLEANUP_MAX_AGE = 86400;

    public static function touch(
        int $userId,
        string $sessionId,
        ?string $lastPage = null
    ): void 
    {
        $now = date('Y-m-d H:i:s');

        $result = OnlineUsersRepository::touch([
            'user_id'      => $userId,
            'session_id'   => $sessionId,
            'last_seen_at' => $now,
            'last_page'    => $lastPage,
        ]);
    }

    public static function touchIfNeeded(int $userId, string $sessionId, ?string $lastPage = null, int $intervalSeconds = self::DEFAULT_TOUCH_INTERVAL): void 
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::touch($userId, $sessionId, $lastPage);
            return;
        }

        $now = time();
        $lastTouch = $_SESSION[self::SESSION_TOUCH_KEY] ?? 0;

        if (($now - (int) $lastTouch) < $intervalSeconds) {
            return;
        }

        self::touch($userId, $sessionId, $lastPage);
        $_SESSION[self::SESSION_TOUCH_KEY] = $now;
    }

    public static function setCharacterId(int $userId, ?int $characterId): void
    {
        OnlineUsersRepository::updateCharacterId($userId, $characterId);
    }

    public static function remove(int $userId): void
    {
        OnlineUsersRepository::deleteByUserId($userId);
        if (session_status() === PHP_SESSION_ACTIVE) {
            unset($_SESSION[self::SESSION_TOUCH_KEY]);
        }
    }

    public static function getOnlineUsers(int $timeoutSeconds = self::DEFAULT_ONLINE_TIMEOUT): array 
    {
        $threshold = date('Y-m-d H:i:s', time() - $timeoutSeconds);
        return OnlineUsersRepository::getOnlineUsers($threshold);
    }

    public static function cleanExpired(int $maxAgeSeconds = self::DEFAULT_CLEANUP_MAX_AGE): void 
    {
        $threshold = date('Y-m-d H:i:s', time() - $maxAgeSeconds);
        OnlineUsersRepository::deleteExpired($threshold);
    }
}