<?php
namespace App\Core\Http\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;

use App\Core\Auth\Auth;

class AuthMiddleware extends Middleware
{
    public function handle(Request $request, array $params, callable $next, mixed $mwParam = null)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required.');
        }
        $next();
    }
}