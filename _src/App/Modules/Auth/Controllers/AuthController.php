<?php
namespace App\Modules\Auth\Controllers;

// Http
use App\Core\Http\Response;
use App\Core\Http\Request;
use App\Core\Support\Result;

//View
use App\Core\Web\View;
use App\Core\Web\ViewMap;

//Sanitation
use App\Core\Validation\Sanitizer;

// Services
use App\Modules\Auth\Services\LoginService;
use App\Modules\Auth\Services\LogoutService;
use App\Modules\Auth\Services\VerifyEmailService;
use App\Modules\Auth\Services\ResetPasswordService;


class AuthController
{

    // Prihlásenie používateľa: [POST] /auth/login
    public function login(Request $request): void
    {
        $result = LoginService::login([
            'username' => Sanitizer::string($request->input('username')),
            'password' => $request->input('password'),
        ]);

        if(!$result->success){ Response::json($result); }
        Response::redirect('/account', 200);
    }

    // Odhlásenie používateľa: [POST] /auth/logout
    public function logout(Request $request)
    {
        LogoutService::logout();
        Response::redirect('/', 200);
    }


    //Overenie emailovej adresy používateľa: [GET] /auth/verify/{token}
    public function verifyEmailAdressPage(array $params = null)
    {
        if ($params === null || !isset($params['token']) || $params['token'] === '') {
            Response::redirect('/', 404);
        }

        $result = VerifyEmailService::verifyEmail($params['token']);

        $tokenExpired = false;

        if(!$result->success){

            if($result->data['expired'] == true){
                $tokenExpired = true;
            }

            if($result->data['tokenNoExists'] == true){
                Response::redirect('/', 404);
            }
        }

        View::make(ViewMap::AUTH_EMAIL_VERIFY, [
            'title' => 'Aktivácia účtu',
            'expiredView' => $tokenExpired,
            'verifyToken' => $params['token']
        ])->render();
    }

    public function resendEmailVerify(Request $request){
        $token = $request->input('token');
        $result = VerifyEmailService::resend($token);
        Response::json($result);
    }


    // Stránka s obnovou hesla [GET] /reset-password/{token?}
    public function resetPasswordPage(array $params = null){
        $showResetPasswordForm = false;
        $token = '';

        if(isset($params['token'])){
            $result = ResetPasswordService::verifyPasswordResetToken($params['token']);
            if($result->success){
                $showResetPasswordForm = true;
                $token = $result->data['token'];
            }else{
                Response::redirect('/reset-password', 302);
            }
        }

        View::make(ViewMap::AUTH_RESET_PASSWORD, [
            'title' => 'Zabudnuté heslo',
            'showResetPasswordForm' => $showResetPasswordForm,
            'token' => $token,
        ])->render();
    }

    //Odoslanie overovacieho odkazu pre obnovu hesla [post] /reset-password
    public function sendVerifyTokenForResetPassword(Request $request){
        $result = ResetPasswordService::resetPasswordVerifyEmail(Sanitizer::email($request->input('email')));
        Response::json($result);
    }

    //Nastavenie nového hesla [post] /auth/password/reset
    public function resetForgottenPassword(Request $request){
        $result = ResetPasswordService::resetForgottenPassword([
            'password' => $request->input('password'),
            'password_check' => $request->input('password_check'),
            'token' => $request->input('token')
        ]);
        Response::json($result);
    }
}