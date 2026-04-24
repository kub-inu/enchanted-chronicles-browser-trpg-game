<?php
namespace App\Modules\User\Controllers;

use App\Core\Web\View;
use App\Core\Web\ViewMap;
use App\Core\Http\Response;
use App\Core\Http\Request;

use App\Modules\User\DTO\UserRegistrationData;
use App\Modules\User\Services\UserRegistrationService;
use App\Core\Validation\Sanitizer;

class UserController {

    // [GET] /auth/register
    public function UserRegistrationPage(){
        View::make(ViewMap::USER_REGISTER, ['title' => "Registrácia"])->render();
    }

    // [POST] /auth/register
    public function register(Request $request){
        $dto = new UserRegistrationData(
            username: Sanitizer::string($request->input('username')),
            email: Sanitizer::email($request->input('email')),
            password: $request->input('password'),
            passwordCheck: $request->input('password_check'),
            legal: !empty($request->input('legal'))
        );

        $result = UserRegistrationService::register($dto);

        if($result->success){
            Response::redirect('/', 200);
        }else{
            Response::json($result);
        }
    }
}
