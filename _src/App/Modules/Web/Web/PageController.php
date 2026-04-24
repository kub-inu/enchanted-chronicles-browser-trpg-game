<?php
namespace App\Modules\Web\Web;

use App\Core\Web\View;
use App\Core\Http\Request;

class PageController {

    //Zobrazí domovskú stránku aplikácie
    public function index(Request $request){

        if($request->input('expired')){
            $expiredLogout = 'Bol si odhlásený z nečinnosti.';
        }else{
            $expiredLogout = '';
        }

        View::make('home', [
            'title' => "Home", 
            "expired" => $expiredLogout
            ])->render();
    }	

    public function UserRegistrationPage(){
        View::make('pages/auth/user_registration', ['title' => "Registrácia"])->render();
    }

    public function lobby(){
        echo 'lobby';
    }
}