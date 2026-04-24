<?php

namespace App\Runtime\OnlineUsers;

use App\Core\Database\Database;

class OnlineUsersRepository
{
    public static function touch(array $data): bool
    {
        $db = new Database();
        $db->query("
            INSERT INTO users_online (user_id, session_id, last_seen_at, last_page) VALUES (:user_id, :session_id, :last_seen_at, :last_page)
            ON DUPLICATE KEY UPDATE
                user_id = VALUES(user_id), session_id = VALUES(session_id), last_seen_at = VALUES(last_seen_at), last_page = VALUES(last_page)
        ");

        $db->bind(':user_id', $data['user_id']);
        $db->bind(':session_id', $data['session_id']);
        $db->bind(':last_seen_at', $data['last_seen_at']);
        $db->bind(':last_page', $data['last_page'] ?? null);

        return $db->execute();
    }

    public static function updateCharacterId(int $userId, ?int $characterId): bool
    {
        $db = new Database();
        $db->query("UPDATE users_online SET character_id = :character_id WHERE user_id = :user_id");
        $db->bind(':user_id', $userId);
        $db->bind(':character_id', $characterId);
        return $db->execute();
    }

    public static function deleteByUserId(int $userId): bool
    {
        $db = new Database();

        $db->query("DELETE FROM users_online WHERE user_id = :user_id");
        $db->bind(':user_id', $userId);

        return $db->execute();
    }

    public static function getOnlineUsers(string $threshold): array
    {
        $db = new Database();
        $db->query("
            SELECT
                u.id,
                u.username,
                o.character_id,
                o.last_seen_at,
                o.last_page
            FROM users_online o
            INNER JOIN users u ON u.id = o.user_id
            WHERE o.last_seen_at >= :threshold
            ORDER BY o.last_seen_at DESC
        ");
        $db->bind(':threshold', $threshold);
        return $db->resultSet();
    }

    public static function deleteExpired(string $threshold): bool
    {
        $db = new Database();

        $db->query("DELETE FROM users_online WHERE last_seen_at < :threshold");
        $db->bind(':threshold', $threshold);

        return $db->execute();
    }

    public static function findByUserId(int $userId): array|false
    {
        $db = new Database();
        $db->query("SELECT * FROM users_online WHERE user_id = :user_id LIMIT 1");
        $db->bind(':user_id', $userId);
        return $db->single();
    }
}