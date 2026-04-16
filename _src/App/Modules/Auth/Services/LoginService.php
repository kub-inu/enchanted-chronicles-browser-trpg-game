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

class LoginService
{
    public function login()
    {
        //Validation layer
        $validator = new Validator($data, AuthRules::login());
        if ($validator->fails()) {
            Response::validation($validator->errors());
        }

        $rate = new RateLimiter('login', $data['username']);
        $rate->checkOrFail();
    
        $user = UserRepository::loginData($data['username']);

        if(!empty($user) && password_verify($data['password'], $user['password'])){
            $rate->clear();
            Auth::login((int) $user['id']);
            return ['success' => true, 'message' => 'Ok.'];

            Response::success();
        }else{
            $rate->hit();
            return ['success' => false, 'message' => 'Neplatné prihlasovacie údaje.'];
        }
    }
}