<?php

namespace App\Core\Authorization;

use App\Modules\User\Repositories\UserRepository;
use App\Core\Auth\Auth;

class Authorization
{
    protected static array $roleCache = [];
    protected static array $permissionsCache = [];

    /**
     * Overí, či zadaný user má konkrétne oprávnenie.
     */
    public static function can(array $user, string $permission): bool
    {
        if (empty($user['id'])) {
            return false;
        }

        $permissions = self::getPermissionsForUser((int) $user['id']);

        return in_array($permission, $permissions, true);
    }

    /**
     * Overí, či aktuálne prihlásený user má konkrétne oprávnenie.
     */
    public static function canCurrent(string $permission): bool
    {
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        return self::can($user, $permission);
    }

    /**
     * Overí, či zadaný user má konkrétnu rolu.
     */
    public static function hasRole(array $user, string $role): bool
    {
        if (empty($user['id'])) {
            return false;
        }

        $currentRole = self::getRoleForUser((int) $user['id']);

        if ($currentRole === null) {
            return false;
        }

        return ($currentRole['name'] ?? null) === $role;
    }

    /**
     * Overí, či aktuálne prihlásený user má konkrétnu rolu.
     */
    public static function hasCurrentRole(string $role): bool
    {
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        return self::hasRole($user, $role);
    }

    /**
     * Overí ownership podľa owner kľúča v resource.
     * Defaultne očakáva user_id.
     */
    public static function owns(array $user, array $resource, string $ownerKey = 'user_id'): bool
    {
        if (empty($user['id'])) {
            return false;
        }

        if (!array_key_exists($ownerKey, $resource)) {
            return false;
        }

        return (int) $resource[$ownerKey] === (int) $user['id'];
    }

    /**
     * Overí ownership pre aktuálne prihláseného usera.
     */
    public static function ownsCurrent(array $resource, string $ownerKey = 'user_id'): bool
    {
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        return self::owns($user, $resource, $ownerKey);
    }

    /**
     * Vyhodí 403, ak user nemá oprávnenie.
     */
    public static function authorize(array $user, string $permission): void
    {
        if (!self::can($user, $permission)) {
            abort(403, 'You do not have permission to access this resource.');
        }
    }

    /**
     * Vyhodí 403, ak user nevlastní resource.
     */
    public static function authorizeOwnership(array $user, array $resource, string $ownerKey = 'user_id'): void
    {
        if (!self::owns($user, $resource, $ownerKey)) {
            abort(403, 'You do not own this resource.');
        }
    }

    /**
     * Vyhodí 401, ak nie je prihlásený user.
     */
    public static function authorizeAuth(): void
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required.');
        }
    }

    /**
     * Vyhodí 401/403 pre aktuálneho usera a permission check.
     */
    public static function authorizeCurrent(string $permission): void
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401, 'Authentication required.');
        }

        self::authorize($user, $permission);
    }

    /**
     * Vyhodí 401/403 pre aktuálneho usera a ownership check.
     */
    public static function authorizeCurrentOwnership(array $resource, string $ownerKey = 'user_id'): void
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401, 'Authentication required.');
        }

        self::authorizeOwnership($user, $resource, $ownerKey);
    }

    /**
     * Vyhodí 401/403 pre aktuálneho usera a role check.
     */
    public static function authorizeCurrentRole(string $role): void
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401, 'Authentication required.');
        }

        if (!self::hasRole($user, $role)) {
            abort(403, 'You do not have permission to access this resource.');
        }
    }

    /**
     * Vráti rolu usera z cache alebo repository.
     */
    public static function getRoleForUser(int $userId): ?array
    {
        if (array_key_exists($userId, self::$roleCache)) {
            return self::$roleCache[$userId];
        }

        self::$roleCache[$userId] = UserRepository::getRoleByUserId($userId);

        return self::$roleCache[$userId];
    }

    /**
     * Vráti permissions usera z cache alebo repository.
     */
    public static function getPermissionsForUser(int $userId): array
    {
        if (array_key_exists($userId, self::$permissionsCache)) {
            return self::$permissionsCache[$userId];
        }

        self::$permissionsCache[$userId] = UserRepository::getResolvedPermissionsByUserId($userId);

        return self::$permissionsCache[$userId];
    }

    /**
     * Zmaže cache pre konkrétneho usera.
     */
    public static function forgetUser(int $userId): void
    {
        unset(self::$roleCache[$userId], self::$permissionsCache[$userId]);
    }

    /**
     * Zmaže celú authorization cache.
     */
    public static function flush(): void
    {
        self::$roleCache = [];
        self::$permissionsCache = [];
    }
}