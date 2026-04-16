<?php
namespace App\Core\Web;

use App\Core\Web\View;

class PageController {

    //Zobrazí domovskú stránku aplikácie
    public function index(array $params){
        View::make('home', ['title' => "Home"])->render();
    }	

    public function UserRegistrationPage(){
        View::make('pages/auth/user_registration', ['title' => "Registrácia"])->render();
    }
}