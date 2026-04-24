<?php

namespace App\Modules\Auth\Services;

use App\Core\Auth\Auth;
use App\Core\Validation\Validator;
use App\Core\Validation\Rules\AuthRules;

use App\Core\Http\Response;
use App\Core\Support\Result;

use App\Modules\User\Repositories\UserRepository;
use App\Modules\User\Repositories\PendingUserRepository;
use App\Modules\Character\Repositories\CharacterRepository;

use App\Core\Security\RateLimit\RateLimiter;

class LoginService
{
    public static function login($data): Result
    {
        //Validation layer
        $validator = new Validator([
            "username" => $data['username'],
            "password" => $data['password']
        ], AuthRules::login());
        if ($validator->fails()) {
            return Result::validation($validator->errors(), $data);
        }

        //$rate = new RateLimiter('login', $data['username']);
        //$rate->checkOrFail();
    
        $user = UserRepository::loginData($data['username']);

        if(!empty($user) && password_verify($data['password'], $user['password'])){
            //$rate->clear();

            Auth::login((int) $user['id']);
            return Result::success();

        }else{
            //$rate->hit();
            return Result::error('Neplatné prihlasovacie údaje.', null, $data);
        }
    }
}