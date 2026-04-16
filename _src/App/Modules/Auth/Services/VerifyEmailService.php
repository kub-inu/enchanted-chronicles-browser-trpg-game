<?php
namespace App\Modules\Auth\Services;

use App\Core\Auth\Auth;
use App\Core\Http\Response;

use App\Core\Security\RateLimit\RateLimiter;
use App\Modules\User\Repositories\PendingUserRepository;

use App\Modules\User\Services\UserRegistrationService;

use App\Modules\User\DTO\AccountCreateData;

class VerifyEmailService
{
    public static function verifyEmail(string $token)
    {
        $hashToken = hash('sha256', $token);
        $account = PendingUserRepository::findByTokenHash($hashToken);

        if(empty($account) || !isset($account['payload_json']) || $account['payload_json'] === '') {
            return ['success' => false, 'errors' => [ 'token' => 'Token neexistuje alebo expiroval.' ] ];
        }

        $payload_json = json_decode($account['payload_json'], true);
        if (!is_array($payload_json)) {
            return [ 'success' => false, 'errors' => [ 'payload' => 'Dáta registrácie sú poškodené.' ] ];
        }

        $dto = new AccountCreateData(
            username: $account['username'],
            email: $account['email'],
            password: $payload_json['password'],
            registrationDate: $payload_json['registration_date'],
            legal: $payload_json['legal'],
            pendingId: $account['id']
        );
        return UserRegistrationService::finishRegistration($dto);
    }
}