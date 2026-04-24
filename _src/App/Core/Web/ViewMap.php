<?php

namespace App\Core\Web;

final class ViewMap
{
    // HOME
    public const HOME = 'pages/home/index';

    // AUTH
    public const AUTH_LOGIN = 'pages/auth/login';
    public const AUTH_VERIFY = 'pages/auth/verify';

    // USER
    public const USER_REGISTER = 'pages/auth/user_registration';
    public const USER_PROFILE = 'pages/user/profile';


    public const AUTH_RESET_PASSWORD = 'pages/auth/reset_password';
    public const AUTH_EMAIL_VERIFY = 'pages/auth/email_verify';

    public const ACCAUNT_INDEX = 'pages/user/account/account';
}