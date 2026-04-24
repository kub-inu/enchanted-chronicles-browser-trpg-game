<?php

namespace App\Modules\Auth\Repositories;

use App\Core\Database\Database;

class AuthenticationRepository{

    public static function storeToken(array $data): void
    {
        $db = new Database();
        $db->query("INSERT INTO tokens (token, domain, account_id, expires_at) VALUES (:t, :d, :id, :expire)");
        $db->bind(":t", $data['token']);
        $db->bind(':d', $data['domain']);
        $db->bind(":id", $data['account_id']);
        $db->bind(":expire", $data['expires_at']);
        $db->execute();
    }

    public static function findToken($token, $domain): array
    {
        $db = new Database();

        $db->query("SELECT * FROM tokens WHERE token = :t AND domain = :d");
        $db->bind(":t", $token);
        $db->bind(":d", $domain);

        return $db->single() ?: [];
    }

    public static function removeToken($token, $domain): void
    {
        $db = new Database();
        
        $db->query("DELETE FROM tokens WHERE token = :t AND domain = :d");
        $db->bind(":t", $token);
        $db->bind(":d", $domain);
        $db->execute();

    }

    public static function setNewPassword($id, $psw): void 
    {
        $db = new Database();
        $db->query("UPDATE users SET password = :psw WHERE id = :id");
        $db->bind(":id", $id);
        $db->bind(":psw", $psw);
        $db->execute();       
    }
}