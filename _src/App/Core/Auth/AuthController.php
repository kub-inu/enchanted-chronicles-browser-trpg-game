<?php
namespace App\Core\Auth;

use App\Core\Web\View;
use App\Core\Http\Response;
use App\Core\Http\Request;

use App\Core\Auth\AuthService;

use App\Modules\User\Services\VerifyEmailAdressService;
use App\Modules\User\Repositories\PendingUserRepository;

use App\Core\Validation\Sanitizer;

class AuthController {

    /**
     * Zobrazenie stránky s registračným formulárom
     */
    public function UserRegistrationPage(){
        View::make('pages/auth/user_registration', ['title' => "Registrácia"])->render();
    }

    public function registerVerifyPage(array $params = null){

        if(!$params['token']){
            Response::redirect('/');
        }else{

        }

        $pending_data = PendingUserRepository::findByTokenHash(hash('sha256', $params['token']));
        if($pending_data){
            View::make('pages/auth/completed_registration', [
                'title' => "Dokončenie registrácie", 
                'data' => $pending_data 
            ])->render();
        }else{
            Response::redirect('/');
        }
    }





    // public function createNewUser(Request $request, array $params){
    //     $data = AuthService::createNewUser([
    //         'password' => $request->input('password'),
    //         'password_check' => $request->input('password_check'),

    //         'character_name' => $request->input('character_name'),
    //         'character_surname' => $request->input("character_surname"),
    //         'character_gender' => $request->input("character_gender"),

    //         'token' => $params['token']
    //     ]);
    //     Response::redirect('/');
    // }

    /**
     * Spracovanie API po odoslaní registračného formulára
     */
    // public function register(Request $request){ 
    //     AuthService::createPendingRegistration([
    //         'username' => Sanitizer::string($request->input('username')),
    //         'email' => Sanitizer::email($request->input('email')),
    //         'password' => $request->input('password'),
    //         'password_check' => $request->input('password_check'),
    //         'legal' => $request->input('legal')
    //     ]);
    // }


    /**
     * Spracovanie a sanita API po odoslaní prihlasovacieho formulára
     */
    public function login(Request $request){
        $result = AuthService::login([
            'username' => Sanitizer::string($request->input('username')),
            'password' => $request->input('password'),
        ]);
        //$response->redirect('/');
        //Response::redirect('/');
    }

    public function logout()
    {
        AuthService::logout();
        Response::redirect('/');
    }
}