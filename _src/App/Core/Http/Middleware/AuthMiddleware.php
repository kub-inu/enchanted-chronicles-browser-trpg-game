<?php
namespace App\Core\Http\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;

use App\Core\Auth\Auth;
use App\Runtime\OnlineUsers\OnlineUsersService;

use App\Modules\Auth\Services\AuthSessionService;
use App\Modules\Auth\Services\LogoutService;

class AuthMiddleware extends Middleware
{
    public function handle(Request $request, array $params, callable $next, mixed $mwParam = null)
    {
        if (!Auth::check()) {
            Response::redirect('/', 401);
        }

        if (AuthSessionService::isExpired()) {
            LogoutService::logout();
            Response::redirect('/?expired=1', 401);
            //Response::redirect('/login?expired=1', 401);
        }

        AuthSessionService::refreshActivity();
        OnlineUsersService::touchIfNeeded(Auth::id(), session_id(), $request->path());
        $next();
    }
}