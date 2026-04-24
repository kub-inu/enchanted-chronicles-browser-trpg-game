<?php
namespace App\Modules\User\DTO;

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