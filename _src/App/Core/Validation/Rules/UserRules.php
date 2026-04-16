<?php

namespace App\Core\Validation\Rules;

final class UserRules
{
    public static function registration(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'password_check' => ['required', 'string', 'same:password'],
            'legal' => ['required']
        ];
    }
}