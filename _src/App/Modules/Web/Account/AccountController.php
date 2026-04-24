<?php
namespace App\Modules\Web\Account;

// Http
use App\Core\Http\Response;
use App\Core\Http\Request;
use App\Core\Support\Result;

//View
use App\Core\Web\View;
use App\Core\Web\ViewMap;


class AccountController
{
    public function account(){
        View::make(ViewMap::ACCAUNT_INDEX, ['title' => 'Môj účet'])->render();
    }
}