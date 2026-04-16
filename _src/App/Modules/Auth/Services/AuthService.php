<?php

namespace App\Modules\Auth\Services;

use App\Core\Auth\Auth;
use App\Core\Validation\Validator;
use App\Core\Validation\Rules\AuthRules;

use App\Core\Http\Response;

use App\Modules\User\Repositories\UserRepository;
use App\Modules\User\Repositories\PendingUserRepository;
use App\Modules\Character\Repositories\CharacterRepository;

use App\Core\Security\RateLimit\RateLimiter;

class AuthService
{
    //...
}