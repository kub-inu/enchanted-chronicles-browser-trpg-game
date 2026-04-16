<?php

namespace App\Modules\User\Repositories;

use App\Core\Database\Database;

class PendingUserRepository{

    public static function usernameExists(string $username): bool
    {
        $db = new Database();
        $db->query("SELECT id FROM users_pending WHERE username = :username AND expires_at > NOW() LIMIT 1");
        $db->bind(":username", $username);
        $result = $db->single();

        return $result ? true : false;
    }

    public static function emailExists(string $email): bool
    {
        $db = new Database();
        $db->query("SELECT id FROM users_pending WHERE email = :email AND expires_at > NOW() LIMIT 1");
        $db->bind(":email", $email);
        $result = $db->single();

        return $result ? true : false;
    }

    public static function findByTokenHash(string $token): array
    {
        $db = new Database();
        $db->query("SELECT * FROM users_pending WHERE token = :token AND expires_at > NOW() LIMIT 1");
        $db->bind(":token", $token);
        $result = $db->single();

        return $result ?: [];
    }

    public static function create(array $data): int
    {
        //Vytvorí záznam
        $db = new Database();
        $db->query("INSERT INTO users_pending 
            (email, username, token, payload_json, expires_at) 
            VALUES 
            (:email, :username, :token, :payload_json, :expires_at)"
            );
        $db->bind(":email", $data['email']);
        $db->bind(":username", $data['username']);
        $db->bind(":token", $data['token']);
        $db->bind(":payload_json", $data['payload_json']);
        $db->bind(":expires_at", $data['expires_at']);
        $db->execute();

        return $db->lastInsertId();
    }

    public static function delete(int $id, ?Database $db = null): bool
    {
        $db = $db ?? new Database();
        $db->query("DELETE FROM users_pending WHERE id = :id");
        $db->bind(":id", $id);
        return $db->execute();
    }
}