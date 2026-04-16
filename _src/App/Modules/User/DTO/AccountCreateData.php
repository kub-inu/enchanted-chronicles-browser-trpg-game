<?php
namespace App\Modules\Auth\DTO;

class AccountCreateData
{
    public function __construct(
        public string $username,
        public string $email,
        public string $password,
        public string $registrationDate,
        public bool $legal,
        public int $pendingId
    ) {}
}