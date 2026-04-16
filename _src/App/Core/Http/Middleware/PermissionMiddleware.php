<?php

namespace App\Core\Http\Middleware;

use App\Core\Http\Request;
use App\Core\Auth\Auth;

class PermissionMiddleware extends Middleware
{
    public function handle(Request $request, array $params, callable $next, mixed $mwParam = null)
    {
        if ($mwParam === null || $mwParam === '') {
            abort(500, 'Permission middleware parameter is missing.');
        }

        $permissions = is_array($mwParam)
            ? $mwParam
            : explode(',', $mwParam);

        if (!Auth::check()) {
            abort(401, 'Authentication required.');
        }

        foreach ($permissions as $permission) {
            if (Auth::can(trim($permission))) {
                return $next();
            }
        }

        abort(403, 'You do not have permission to access this resource.');
    }
}