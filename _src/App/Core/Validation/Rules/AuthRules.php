<?php

namespace App\Core\Validation\Rules;

final class AuthRules
{
    public static function login(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ];
    }

    public static function registrationPending(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'password_check' => ['required', 'string', 'same:password'],
            'legal' => ['required']
        ];
    }

    public static function registerComplete(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'password_check' => ['required', 'string', 'same:password'],
            'character_name' => ['required', 'string', 'min:2', 'max:50'],
            'character_surname' => ['required', 'string', 'min:2', 'max:50'],
            'character_gender' => ['required', 'in:male,female'],
        ];
    }
}