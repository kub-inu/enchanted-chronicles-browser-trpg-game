<?php
namespace App\Modules\User\Services;

use App\Core\Http\Response;
use App\Core\Logging\Logger;
use App\Core\Security\Escaper;

use App\Core\Validation\Validator;
use App\Core\Validation\Rules\UserRules;

use App\Modules\User\DTO\UserRegistrationData;

use App\Core\Database\Database;
use App\Modules\User\Repositories\UserRepository;
use App\Modules\User\Repositories\PendingUserRepository;

use App\Modules\User\DTO\AccountCreateData;

//use App\Core\Security\RateLimit\RateLimiter;


class UserRegistrationService{

    /**
     * ======================
     * Vytvorenie dočasného účtu
     * ======================
     */

    public static function register(UserRegistrationData $dto): void
    {
        self::registerValidation($dto);

        $passwordHash = password_hash($dto->password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        try {
            $pendingId = PendingUserRepository::create([
                'email' => $dto->email,
                'username' => $dto->username,
                'token' => $tokenHash,
                'payload_json' => json_encode([
                    'legal' => $dto->legal,
                    'registration_date' => date('Y-m-d H:i:s'),
                    'password' => $passwordHash,
                ], JSON_UNESCAPED_UNICODE),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
            ]);


            try{
                self::sendVerificationEmail($dto->email, $dto->username, $token);
            }catch(\Throwable $e){
                Logger::error('Error - Overovací email sa neodoslal', ["message" => $e->getMessage()], 'auth');
                Response::error('Registrácia bola uložená, ale email sa nepodarilo odoslať. Kontaktuj technickú podporu projektu.');
            }

            Response::success(
                message: 'Registrácia bola vytvorená. Skontroluj email pre dokončenie registrácie.',
                data: $pendingId
            );

        }catch(\Throwable $e){
            Logger::error('Error - Registrácia používateľa', ["message" => $e->getMessage()], 'auth');
            Response::error('Pri vytváraní registrácie nastala chyba.');
        }
    }

    public static function registerValidation(UserRegistrationData $dto): void
    {
        $validator = new Validator([
            'username' => $dto->username,
            'email' => $dto->email,
            'password' => $dto->password,
            'password_check' => $dto->passwordCheck,
            'legal' => $dto->legal
        ], UserRules::registration());

        if( $dto->username && ( UserRepository::usernameExists($dto->username) || PendingUserRepository::usernameExists($dto->username) ) ){
            $validator->addError('username', 'Zadané používateľské meno už existuje.');
        }

        if( $dto->email && ( UserRepository::emailExists($dto->email) || PendingUserRepository::emailExists($dto->email) ) ){
            $validator->addError('email', 'Zadaný email už existuje.');
        }

        if ($validator->fails()) {
            Response::validation($validator->errors(), [
                'username' => $dto->username,
                'email' => $dto->email
            ]);
        }        
    }


    public static function sendVerificationEmail(string $email, string $username, string $token): bool 
    {
        $app = config('app');
        $link = rtrim($app['url'], '/') . '/auth/verify/' . urlencode($token);

            $subject = 'Aktivácia registrácie';
            $message = '
                <html>
                <head>
                    <meta charset="UTF-8">
                </head>
                <body>
                    <p>Ahoj, '.Escaper::html($username).'</p>
                    <p>klikni na odkaz nižšie pre dokončenie registrácie:</p>
                    <p><a href="' . Escaper::attr($link) . '">Dokončiť registráciu - '.Escaper::html($link).'</a></p>
                    <p>Ak si sa neregistroval, tento email ignoruj.</p>
                </body>
                </html>
            ';

            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: Enchanted Chronicles <no-reply@fantasy-rpg.cz>'
            ];

            return mail($email, $subject, $message, implode("\r\n", $headers));
    }


    /**
     * ======================
     * DOKONČENIE REGISTRÁCIE
     * ======================
     */

    public static function finishRegistration(AccountCreateData $dto)
    {
        $db = new Database();
        try {
            $db->beginTransaction();

            $uid = UserRepository::create([
                "username" => $dto->username,
                "email" => $dto->email,
                "password" => $dto->password,
                "registration_date" => $dto->registrationDate
            ], $db);

            PendingUserRepository::delete($dto->pendingId, $db);

            $db->endTransaction();

            return [
                'success' => true,
                'message' => 'Tvoja registrácia bola dokončená. Môžeš sa prihlásiť.',
                'user_id' => $uid
            ];

        } catch (\Throwable $e){

            if ($db->inTransaction()) {
                $db->cancelTransaction();
            }      

            return [
                'success' => false,
                'errors' => [
                    'system' => 'Pri dokončovaní registrácie nastala chyba.'
                ]
            ];

        }
    }
}