<?php

namespace App\Modules\Auth\Services;

use App\Core\Auth\Auth;
use App\Core\Support\Result;
use App\Runtime\OnlineUsers\OnlineUsersService;
use App\Modules\Auth\Services\AuthSessionService;

class LogoutService
{
    public static function logout()
    {
        OnlineUsersService::remove(Auth::id());
        AuthSessionService::clearActivity();
        Auth::logout();
    }
}