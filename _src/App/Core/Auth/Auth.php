<?php

namespace App\Core\Auth;

use App\Core\Authorization\Authorization;
use App\Modules\User\Repositories\UserRepository;
use App\Core\Session\Session;


class Auth 
{
    protected static ?array $cachedUser = null;

    public static function check(): bool
    {
        //return isset($_SESSION['user_id']);
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        //return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        return Session::get('user_id', null);
    }


    //Príprava do budúcna
    public static function character_id(): ?int 
    {
        //return isset($_SESSION['character_id']) ? (int) $_SESSION['character_id'] : null;
        return Session::get('character_id', null);
    }

    /**
     * Vrátiť informácie o userovy
     */

    public static function user(): ?array
    {
        if (self::$cachedUser !== null) { return self::$cachedUser; }
        $userId = self::id();

        if ($userId === null) { return null; }

        $user = UserRepository::findById($userId);
        self::$cachedUser = !empty($user) ? $user : null;

        return self::$cachedUser;
    }

    /**
     * Výpis dát do HTML
     */

    public static function userForView(): ?array
    {
        $user = self::user();
        if ($user === null) { return null; }

        return self::e_array([
            "username" => $user['username'] ?? ""
        ]);
    }

    public static function e_array(array $data): array
    {
        $escaped = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $escaped[$key] = self::e_array($value);
            } elseif (is_string($value)) {
                $escaped[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            } else {
                $escaped[$key] = $value;
            }
        }

        return $escaped;
    }


    /**
     * LOGIN / LOGOUT - práca so sessions
     */

    public static function login(int $user_id): void 
    {
        Session::regenerate();
        self::$cachedUser = null;
        Authorization::flush();

        Session::set('user_id', $user_id);
    }

    public static function logout():void
    {
        self::$cachedUser = null;
        Authorization::flush();

        Session::destroy();
    }



    /**
     * Autorizačné metódy
     */

    //Vráti array roly, ktorú user drží
    public static function role(): ?array
    {
        $userId = self::id();

        if (!$userId) {
            return null;
        }

        return Authorization::getRoleForUser($userId);
    }  

    //Vráti všetky permissions ktoré sú viazané na usera vďaka role alebo overwrite modu
    public static function permissions(): array
    {
        $userId = self::id();

        if (!$userId) {
            return [];
        }

        return Authorization::getPermissionsForUser($userId);
    }

    //Porovnáva či user vlastní také oprávnenie na vykonanie akcie
    public static function can(string $permission): bool
    {
        return Authorization::canCurrent($permission);
    }

    public static function hasRole(string $role): bool
    {
        return Authorization::hasCurrentRole($role);
    }
}