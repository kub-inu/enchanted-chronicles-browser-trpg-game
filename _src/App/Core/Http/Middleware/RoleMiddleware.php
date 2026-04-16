<?php

namespace App\Core\Http\Middleware;

use App\Core\Http\Request;
use App\Core\Auth\Auth;

class RoleMiddleware extends Middleware
{
    public function handle(Request $request, array $params, callable $next, mixed $mwParam = null)
    {
        if ($mwParam === null || $mwParam === '') {
            abort(500, 'Role middleware parameter is missing.');
        }

        $roles = is_array($mwParam)
            ? $mwParam
            : explode(',', $mwParam);

        if (!Auth::check()) {
            abort(401, 'Authentication required.');
        }

        if (Auth::hasRole('superadmin')) {
            return $next();
        }

        foreach ($roles as $role) {
            if (Auth::hasRole(trim($role))) {
                return $next();
            }
        }

        abort(403, 'You do not have permission to access this resource.');
    }
}