<?php

namespace App\Core\Auth;

use App\Core\Auth\Auth;
use App\Core\Validation\Validator;
use App\Core\Validation\Rules\AuthRules;

use App\Core\Http\Response;

use App\Modules\User\Repositories\UserRepository;
use App\Modules\User\Repositories\PendingUserRepository;
use App\Modules\Character\Repositories\CharacterRepository;

use App\Core\Security\RateLimit\RateLimiter;

class AuthService{


    // public static function createPendingRegistration(array $data): never
    // {
    //     $validator = new Validator($data, AuthRules::registrationPending());

    //     if(!empty($data['username']) && (UserRepository::usernameExists($data['username']) || PendingUserRepository::usernameExists($data['username']))){
    //         $validator->addError('username', 'Existuje.');
    //     }

    //     if(!empty($data['username']) && (UserRepository::emailExists($data['email']) || PendingUserRepository::emailExists($data['email']))){
    //         $validator->addError('email', 'Existuje.');
    //     }
        
    //     if ($validator->fails()) {
    //         Response::validation($validator->errors(), [
    //             'username' => $data['username'],
    //             'email' => $data['email']
    //         ]);
    //     }

    //     $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
    //     $token = bin2hex(random_bytes(32));

    //     try {
    //         $pendingId = PendingUserRepository::create([
    //             'email' => $data['email'],
    //             'username' => $data['username'],
    //             'token' => $token,
    //             'payload_json' => json_encode([
    //                 'legal' => true,
    //                 'registration_date' => date('Y-m-d H:i:s'),
    //                 'password' => $password_hash,
    //             ], JSON_UNESCAPED_UNICODE),
    //             'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
    //         ]);

    //         if (!self::sendVerificationEmail($data['email'], $data['username'], $token)) {
    //             Response::error('Registrácia bola uložená, ale email sa nepodarilo odoslať. Kontaktuj technickú podporu projektu.');
    //         }

    //         Response::success($pendingId, 'Registrácia bola vytvorená. Skontroluj email pre dokončenie registrácie.');

    //     }catch(\Throwable $e){
    //         Response::error('Pri vytváraní registrácie nastala chyba.');
    //     }
    // }

    // public static function sendVerificationEmail(string $email, string $username, string $token): bool 
    // {
    //     $app = config('app');
    //     $link = $app['url'] . 'auth/register/verify/' . urlencode($token);

    //         $subject = 'Aktivácia registrácie';
    //         $message = '
    //             <html>
    //             <head>
    //                 <meta charset="UTF-8">
    //             </head>
    //             <body>
    //                 <p>Ahoj,</p>
    //                 <p>klikni na odkaz nižšie pre dokončenie registrácie:</p>
    //                 <p><a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">Dokončiť registráciu - '.htmlspecialchars($link, ENT_QUOTES, 'UTF-8').'</a></p>
    //                 <p>Ak si sa neregistroval, tento email ignoruj.</p>
    //             </body>
    //             </html>
    //         ';

    //         $headers = [
    //             'MIME-Version: 1.0',
    //             'Content-type: text/html; charset=UTF-8',
    //             'From: Enchanted Chronicles <no-reply@fantasy-rpg.cz>'
    //         ];

    //         return mail($email, $subject, $message, implode("\r\n", $headers));
    // }

    // public static function createNewUser(array $data): array
    // {
    //     //Platnosť tokenu
    //     $pendingUser = PendingUserRepository::findByTokenHash($data['token']);
    //     if(empty($pendingUser) || !isset($pendingUser['payload_json']) || $pendingUser['payload_json'] === '') {
    //         return ['success' => false, 'errors' => [ 'token' => 'Token neexistuje alebo expiroval.' ] ];
    //     }

    //     //Validácia používateľského vstupu
    //     $errors = array_merge(
    //         RegistrationValidator::validate([
    //             "password" => $data['password'],
    //             "password_check" => $data['password_check']
    //         ], true),
    //         CharacterValidator::validate([
    //             "character_name" => $data['character_name'],
    //             "character_surname" => $data['character_surname'],
    //             "character_gender" => $data['character_gender']
    //         ])
    //     );
    //     if (!empty($errors)) {
    //         return [ 'success' => false, 'errors' => $errors ];
    //     }


    //     //Vytvorenie používateľa
    //     $payload_json = json_decode($pendingUser['payload_json'], true);
    //     if (!is_array($payload_json)) {
    //         return [ 'success' => false, 'errors' => [ 'payload' => 'Dáta registrácie sú poškodené.' ] ];
    //     }

    //     $db = new \Core\Database();

    //     try {
    //         $db->beginTransaction();

    //         $userId = UserRepository::create([
    //             "username" => $pendingUser['username'],
    //             "email" => $pendingUser['email'],
    //             "password" => password_hash($data['password'], PASSWORD_DEFAULT),
    //             "registration_date" => $payload_json['registration_date']
    //         ], $db);

    //         //Vytvorenie postavy
    //         $characterId = CharacterRepository::create([
    //             "user_id" => $userId,
    //             "first_name" => ucfirst(trim($data['character_name'])),
    //             "last_name" => ucfirst(trim($data['character_surname'])),
    //             "gender" => $data['character_gender']
    //         ], $db);

    //         //Zmazanie pending usera
    //         PendingUserRepository::delete($pendingUser['id'], $db);

    //         $db->endTransaction();

    //         return [
    //             'success' => true,
    //             'message' => 'Tvoja registrácia bola dokončená. Môžeš sa prihlásiť.',
    //             'user_id' => $userId,
    //             'character_id' => $characterId
    //         ];
    //     } catch (\Throwable $e){
    //         if ($db->inTransaction()) {
    //             $db->cancelTransaction();
    //         }            
    //         return [
    //             'success' => false,
    //             'errors' => [
    //                 'system' => 'Pri dokončovaní registrácie nastala chyba.'
    //             ]
    //         ];
    //     }
    // }



    public static function login(array $data): array
    {
        //Validation layer
        $validator = new Validator($data, AuthRules::login());
        if ($validator->fails()) {
            Response::validation($validator->errors());
        }

        $rate = new RateLimiter('login', $data['username']);
        $rate->checkOrFail();
    
        $user = UserRepository::loginData($data['username']);

        if(!empty($user) && password_verify($data['password'], $user['password'])){
            $rate->clear();
            Auth::login((int) $user['id']);
            return ['success' => true, 'message' => 'Ok.'];

            Response::success();
        }else{
            $rate->hit();
            return ['success' => false, 'message' => 'Neplatné prihlasovacie údaje.'];
        }
    }


    public static function logout():void
    {
        //Tu sa ešte bude premazávať db
        Auth::logout();
    }


    // public static function touchSession(string $token){
        
    //     if (empty($token)) return;
    //     $c = new self();

    //     // najprv overenie, či token existuje
    //     $c->db->query("SELECT user_id, last_seen FROM user_presence WHERE token = :t LIMIT 1");
    //     $c->db->bind(':t', $token);
    //     $exists = $c->db->single();

    //     if (!$exists){
    //         session_destroy();
    //         return;
    //     };

    //     $lastSeen = strtotime($exists['last_seen']);
    //     $expiry = $lastSeen + SESSION_EXPIRATION;

    //     if ($expiry < time()) {
    //         $c->db->query("DELETE FROM user_presence WHERE token = :t");
    //         $c->db->bind(':t', $token);
    //         $c->db->execute();

    //         session_destroy();
    //         return;
    //     }

    //     $c->db->query("UPDATE user_presence SET last_seen = CURRENT_TIMESTAMP WHERE token = :t");
    //     $c->db->bind(':t', $token);
    //     $c->db->execute();  
    // }

    // public static function cleanupPresence(){
    //     $c = new self();
    //     $expiry = date('Y-m-d H:i:s', time() - SESSION_EXPIRATION);
    //     $c->db->query("DELETE FROM user_presence WHERE last_seen < :expiry");
    //     $c->db->bind(":expiry", $expiry);
    //     $c->db->execute();
    // }
}