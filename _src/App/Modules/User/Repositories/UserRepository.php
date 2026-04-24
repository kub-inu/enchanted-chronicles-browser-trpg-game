<?php

namespace App\Modules\User\Repositories;

use App\Core\Database\Database;

class UserRepository{

    public static function usernameExists(string $username): bool
    {
        $db = new Database();
        $db->query("SELECT id FROM users WHERE username = :username LIMIT 1");
        $db->bind(":username", $username);
        $result = $db->single();

        return $result ? true : false;
    }

    public static function emailExists(string $email): bool
    {
        $db = new Database();
        $db->query("SELECT id FROM users WHERE email = :email LIMIT 1");
        $db->bind(":email", $email);
        $result = $db->single();

        return $result ? true : false;
    }

    public static function loginData(string $username): array
    {
        $db = new Database();
        $db->query("SELECT id, username, password FROM users WHERE username = :username");
        $db->bind(":username", $username);
        $result = $db->single();

        return $result ?: [];        
    }

    public static function findByUsername(string $username): array
    {
        $db = new Database();
        $db->query("SELECT id, username FROM users WHERE username = :username");
        $db->bind(":username", $username);
        $result = $db->single();

        return $result ?: [];
    }

    public static function findById(int $id): array
    {
        $db = new Database();
        $db->query("SELECT * FROM users WHERE id = :id");
        $db->bind(":id", $id);
        $result = $db->single();

        return $result ?: [];
    }

    public static function findByEmail(string $email):array
    {
        $db = new Database();
        $db->query("SELECT * FROM users WHERE email = :email LIMIT 1");
        $db->bind(":email", $email);
        $result = $db->single();

        return $result ?: [];        
    }


    /**
     * CRUD
     */

    public static function create(array $data, ?Database $db = null): int
    {
        $db = $db ?? new Database();
        $db->query("INSERT INTO users
            (username, email, password, registration_date) 
            VALUES 
            (:username, :email, :password, :registration_date)"
            );
        $db->bind(":username", $data['username']);
        $db->bind(":email", $data['email']);
        $db->bind(":password", $data['password']);
        $db->bind(":registration_date", $data['registration_date']);
        $db->execute();

        return $db->lastInsertId();
    }



    /**
     * Authorization
     */

    public static function getRoleByUserId(int $userId): ?array
    {
        $db = new Database();
        $db->query(" SELECT r.id, r.name FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE u.id = :user_id LIMIT 1 ");
        $db->bind(':user_id', $userId);
        $result = $db->single();

        if (!$result) { return null; }

        return [
            'id' => $result['id'],
            'name' => $result['name']
        ];
    }

    public static function getResolvedPermissionsByUserId(int $userId): array
    {
        $db = new Database();

        // 1. permissions z roly
        $db->query(" SELECT p.name FROM users u INNER JOIN role_permissions rp ON rp.role_id = u.role_id INNER JOIN permissions p ON p.id = rp.permission_id WHERE u.id = :user_id ");
        $db->bind(':user_id', $userId);
        $rolePermissions = $db->resultSet() ?: [];

        $resolved = [];

        foreach ($rolePermissions as $row) {
            $resolved[$row['name']] = true;
        }

        // 2. user overrides
        $db->query("SELECT p.name, up.effect FROM user_permissions up INNER JOIN permissions p ON p.id = up.permission_id WHERE up.user_id = :user_id");
        $db->bind(':user_id', $userId);
        $overrides = $db->resultSet() ?: [];

        foreach ($overrides as $row) {
            if ($row['effect'] === 'deny') {
                unset($resolved[$row['name']]);
                continue;
            }

            if ($row['effect'] === 'allow') {
                $resolved[$row['name']] = true;
            }
        }

        return array_keys($resolved);
    }

    // public static function findByUsername($username){
    //     $class = new self();

    //     $class->db->query("SELECT * FROM users WHERE username = :u");
    //     $class->db->bind(":u", $username);
    //     $result = $class->db->single();

    //     return $result ?: null;
    // }

    // public function findById($id){
    //     $class = new self();

    //     $class->db->query("SELECT * FROM users WHERE id = :id");
    //     $class->db->bind(":id", $id);
    //     $result = $class->db->single();

    //     return $result ?: null;
    // }

    // public static function findByEmail($email){
    //     $class = new self();

    //     $class->db->query("SELECT * FROM users WHERE email = :e");
    //     $class->db->bind(":e", $email);
    //     $result = $class->db->single();

    //     return $result ?: null;
    // }

    // public static function createNewUser($data, $hash){
    //     $class = new self();

    //     $class->db->query("INSERT INTO users (username, password_hash, email) VALUES (:u, :psw, :e)");
    //     $class->db->bind(":u", $data['username']);
    //     $class->db->bind(":psw", $hash);
    //     $class->db->bind(":e", $data['email']);
    //     $class->db->execute();

    //     $user_id = $class->db->lastInsertId();

    //     $class->db->query("INSERT INTO user_profiles (user_id, status) VALUES (:id, :status)");
    //     $class->db->bind(":id", $user_id);
    //     $class->db->bind(":status", "Active");
    //     $class->db->execute();

    //     $class->db->query("INSERT INTO user_settings (user_id) VALUES (:id)");
    //     $class->db->bind(":id", $user_id);
    //     $class->db->execute();

    //     $class->db->query("INSERT INTO user_stats (user_id) VALUES (:id)");
    //     $class->db->bind(":id", $user_id);
    //     $class->db->execute();

    //     return $user_id;
    // }
}