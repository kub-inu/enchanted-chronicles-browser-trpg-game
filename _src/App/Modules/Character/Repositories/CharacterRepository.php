<?php

namespace App\Modules\Character\Repositories;

use App\Core\Database\Database;

class CharacterRepository{

    public static function create(array $data, ?Database $db = null): int
    {
        $db = $db ?? new Database();
        $db->query("INSERT INTO characters
            (user_id, first_name, last_name, gender) 
            VALUES 
            (:user_id, :first_name, :last_name, :gender)"
            );
        $db->bind(":user_id", $data['user_id']);
        $db->bind(":first_name", $data['first_name']);
        $db->bind(":last_name", $data['last_name']);
        $db->bind(":gender", intval($data['gender']));
        $db->execute();

        return $db->lastInsertId();
    }
}