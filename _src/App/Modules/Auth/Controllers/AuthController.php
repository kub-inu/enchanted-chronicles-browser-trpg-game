<?php
namespace App\Modules\Auth\Controllers;

use App\Core\Web\View;
use App\Core\Web\ViewMap;
use App\Core\Http\Response;
use App\Core\Http\Request;

use App\Core\Validation\Sanitizer;

use App\Modules\Auth\Services\VerifyEmailService;

class AuthController
{
    //login


    /**
     * =========================
     * Prihlásenie používateľa
     * =========================
     *  [POST] /auth/login
     */
    public function login(Request $request)
    {
        $result = AuthService::login([
            'username' => Sanitizer::string($request->input('username')),
            'password' => $request->input('password'),
        ]);
        Response::redirect('/');
    }


    /**
     * =========================
     * Odhlásenie používateľa
     * =========================
     *  [POST] /auth/logout
     */
    public function logout()
    {
        AuthService::logout();
        Response::redirect('/');
    }


    /**
     * =========================
     * Overenie emailovej adresy
     * =========================
     *  [GET] /auth/verify/{token}
     */
    public function verifyEmailAdressPage(array $params = null)
    {
        if ($params === null || !isset($params['token']) || $params['token'] === '') {
            Response::redirect('/', 404);
        }

        $result = VerifyEmailService::verifyEmail($params['token']);
        print_r($result);
    }
}