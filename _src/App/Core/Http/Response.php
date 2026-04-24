<?php
namespace App\Core\Http;

use App\Core\Support\Result;

class Response
{

    public static function html(string $html, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;      
    }

    public static function redirect(string $url, int $status = 302): void 
    {
        header('Location: ' . $url, true, $status);
        exit;
    }

    //Redirect po úspešnom odoslaní formu napr. login 
    public static function redirectSeeOther(string $url): never
    {
        self::redirect($url, 303);
    }


    public static function json(Result $result): void
    {
        self::json_send($result->success, $result->message, $result->data, $result->errors, $result->statusCode);
    }


    //premenovať po refaktore na sendJson
    public static function json_send(bool $success, string $message = '', mixed $data = null, ?array $errors = null, int $statusCode = 200): never 
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        exit;
    }


    /**
     * Určené na zmazanie po dokončení kontroly
     */
    public static function success(string $message = 'OK.', mixed $data = null): never 
    {
        self::json_send(true, $message, $data, null, 200);
    }

    public static function error(string $message = 'Error', mixed $data = null, ?array $errors = null, int $statusCode = 400): never
    {
        self::json_send(false, $message, $data, $errors, $statusCode);
    }

    public static function validation(array $errors, mixed $oldInput = null): never 
    {
        self::json_send(false, 'Validation failed', [
            'old_input_data' => $oldInput
        ], $errors, 422);
    }
}