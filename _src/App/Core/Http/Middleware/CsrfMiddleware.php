<?php

namespace App\Core\Http\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;

use App\Core\Security\Csrf;

class CsrfMiddleware extends Middleware
{
    public function handle(Request $request, array $params, callable $next, mixed $mwParam = null)
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            Csrf::validateOrFail($request->input('_csrf'));
        }

        return $next();
    }
}