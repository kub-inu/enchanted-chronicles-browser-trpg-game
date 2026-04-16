<?php
namespace App\Modules\User\DTO;

class UserRegistrationData
{
    public function __construct(
        public string $username,
        public string $email,
        public string $password,
        public string $passwordCheck,
        public bool $legal
    ) {}
}