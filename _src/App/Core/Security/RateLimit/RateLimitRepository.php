<?php
namespace App\Core\Security\RateLimit;

use App\Core\Database\Database;

final class RateLimitRepository
{
    public function get(string $key): array|false
    {
        $db = new Database();
        $db->query('
            SELECT rate_key, attempts, expires_at
            FROM rate_limits
            WHERE rate_key = :rate_key
            LIMIT 1
        ');
        $db->bind(':rate_key', $key);

        return $db->single();
    }

    public function save(string $key, int $attempts, int $expiresAt): void
    {
        $db = new Database();
        $db->query('
            INSERT INTO rate_limits (rate_key, attempts, expires_at)
            VALUES (:rate_key, :attempts, :expires_at)
            ON DUPLICATE KEY UPDATE
                attempts = VALUES(attempts),
                expires_at = VALUES(expires_at)
        ');
        $db->bind(':rate_key', $key);
        $db->bind(':attempts', $attempts);
        $db->bind(':expires_at', $expiresAt);
        $db->execute();
    }

    public function clear(string $key): void
    {
        $db = new Database();
        $db->query('DELETE FROM rate_limits WHERE rate_key = :rate_key');
        $db->bind(':rate_key', $key);
        $db->execute();
    }
}