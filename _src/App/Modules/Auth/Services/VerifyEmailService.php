<?php
namespace App\Modules\Auth\Services;

use App\Core\Auth\Auth;
use App\Core\Support\Result;

// User modul
use App\Modules\User\Services\UserRegistrationService;
use App\Modules\User\Repositories\PendingUserRepository;
use App\Modules\User\DTO\AccountCreateData;

class VerifyEmailService
{
    public static function verifyEmail(string $token): Result
    {
        $hashToken = hash('sha256', $token);
        $account = PendingUserRepository::findByTokenHash($hashToken);

        // if(empty($account) || !isset($account['payload_json']) || $account['payload_json'] === '') {
        //     return Result::error('Token neexistuje alebo expiroval.', null, [ 'tokenNoExists' => true, 'expired' => false ]);
        // }

        if(empty($account)) {
            return Result::error('Token neexistuje alebo expiroval.', null, [ 'tokenNoExists' => true, 'expired' => false ]);
        }

        if(strtotime($account['expires_at']) < time()){
            return Result::error('Token expiroval.', null, [ 'tokenNoExists' => false, 'expired' => true ]);
        }

        // $payload = json_decode($account['payload_json'], true);
        // if (!is_array($payload)) {
        //     return Result::validation([
        //         'payload' => 'Dáta registrácie sú poškodené.'
        //     ]);
        // }

        // if ( !isset($payload['password'], $payload['registration_date'], $payload['legal'])) {
        //     return Result::validation([
        //         'payload' => 'Dáta registrácie sú neúplné.'
        //     ]);
        // }

        return UserRegistrationService::finishRegistration( 
            new AccountCreateData(
                username: $account['username'],
                email: $account['email'],
                password: $account['password'],
                registrationDate: $account['created_at'],
                legal: 1,
                pendingId: $account['id']
            )
        );
    }

    public static function resend(string $token): Result
    {

        $account = PendingUserRepository::findByTokenHash(hash('sha256', $token));

        if(empty($account)){
            return Result::error('Token nebolo možné obnoviť kvôli odstráneniu čakajúcej registrácie z dôvodu čistenia systému.');
        }

        $newToken = bin2hex(random_bytes(32));
        $newTokenHash = hash('sha256', $newToken);

        PendingUserRepository::updateTokenExpiration($account['id'], $newTokenHash, date('Y-m-d H:i:s', strtotime('+24 hours')));
        UserRegistrationService::sendVerificationEmail($account['email'], $account['username'], $newToken);

        return Result::success('Na email bol odoslaný nový aktivačný odkaz.');
    }
}