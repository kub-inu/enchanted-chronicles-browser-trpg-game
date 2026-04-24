<?php

namespace App\Modules\Auth\Services;

use App\Core\Auth\Auth;
use App\Core\Support\Result;

use App\Core\Validation\Validator;
use App\Core\Validation\Rules\AuthRules;

use App\Modules\User\Repositories\UserRepository;
use App\Modules\Auth\Repositories\AuthenticationRepository;

use App\Core\Logging\Logger;
use App\Core\Security\Escaper;

class ResetPasswordService
{
    public static function resetPasswordVerifyEmail(string $email): Result
    {
        $validator = new Validator(['email' => $email], AuthRules::resetPassword());
        if($validator->fails()){
            return Result::validation($validator->errors(), [
                'email' => $email
            ]);
        }

        //Overenie existencie emailu
        $account = UserRepository::findByEmail($email);

        // Bezpečnostne neutrálna odpoveď
        if(empty($account)){
            return Result::success('Ak účet existuje, overovací email bol odoslaný.');
        }

        //Vytvorenie tokenu
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));


        try {
            //Uloženie tokenu
            AuthenticationRepository::storeToken([
                'token' => $tokenHash,
                'domain' => 'password-reset',
                'account_id' => $account['id'],
                'expires_at' => $expires_at
            ]);

            //Odoslanie emailu s tokenom
            $app = config('app');
            $link = rtrim($app['url'], '/') . '/reset-password/' . urlencode($token);

            $subject = 'Obnovenie zabudnutého hesla';
            $message = '
                <html>
                <head>
                    <meta charset="UTF-8">
                </head>
                <body>
                    <p>Ahoj '.Escaper::html($account['username']).',</p>
                    <p>Zaznamenali sme, že sa pokúšaš obnoviť svoje zabudnuté heslo. Pre pokračovanie klikni na odkaz.</p>
                    <p>Platnosť odkazu je do: '. Escaper::html($expires_at) .'</p>
                    <p><a href="' . Escaper::attr($link) . '">Obnoviť heslo.</a></p>
                    <p>Ak si túto žiadosť neodoslal, tento email ignoruj.</p>
                </body>
                </html>
            ';

            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: Enchanted Chronicles <no-reply@fantasy-rpg.cz>'
            ];

            $send = mail($account['email'], $subject, $message, implode("\r\n", $headers));

            if(!$send){
                Logger::error('Error - Overovací email pre obnovu hesla sa neodoslal', [
                    'email' => $email
                ], 'auth');
                return Result::error('Nastala nečakaná chyba. Kontaktuj technickú podporu.');
            }

        }catch(\Throwable $e){
            Logger::error('Error - Chyba pri resetovaní hesla', [
                'message' => $e->getMessage(),
                'email' => $email
            ], 'auth');
            return Result::error('Nastala nečakaná chyba. Kontaktuj technickú podporu.');            
        }

        return Result::success('Ak účet existuje, overovací email bol odoslaný.');
    }

    public static function verifyPasswordResetToken(string $token): Result
    {
        $tokenHash = hash('sha256', $token);
        $tokenRecord = AuthenticationRepository::findToken($tokenHash, 'password-reset');

        if(empty($tokenRecord)){
            return Result::error('Takýto token neexistuje.');
        }

        if(strtotime($tokenRecord['expires_at']) <= time()){
            AuthenticationRepository::removeToken($tokenHash, 'password-reset');
            return Result::error('Takýto token neexistuje.');
        }

        return Result::success('Overenie bolo úspešné.', [
            'token' => $token, 
            'account_id' => $tokenRecord['account_id'], 
            ]);
    }

    public static function resetForgottenPassword(array $data): Result
    {
        $token = self::verifyPasswordResetToken($data['token']);
        if(!$token->success){
            return $token; //Result::error()
        }

        $validator = new Validator([
            'password' => $data['password'],
            'password_check' => $data['password_check']
            ], AuthRules::password());
        
        if($validator->fails()){
            return Result::validation($validator->errors(), []);
        }
    
        $accountId = $token->data['account_id'];
        $newPasswordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        AuthenticationRepository::setNewPassword($accountId, $newPasswordHash);
        AuthenticationRepository::removeToken(hash('sha256', $data['token']), 'password-reset');

        return Result::success('Nové heslo bolo nastavené.');
    }
}