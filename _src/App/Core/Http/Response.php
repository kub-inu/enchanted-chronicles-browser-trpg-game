<?php
namespace App\Core\Http;

class Response
{
    public static function redirect(string $url, int $status = 302): void 
    {
        http_response_code($status);
        header('Location: ' . $url);
        exit;
    }

    public static function json(bool $success, string $message = '', mixed $data = null, ?array $errors = null, int $statusCode = 200): never 
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    public static function success(string $message = 'OK.', mixed $data = null): never 
    {
        self::json(true, $message, $data, null, 200);
    }

    public static function error(string $message = 'Error', mixed $data = null, ?array $errors = null, int $statusCode = 400): never
    {
        self::json(false, $message, $data, $errors, $statusCode);
    }

    public static function validation(array $errors, mixed $oldInput = null): never 
    {
        self::json(false, 'Validation failed', [
            'old_input_data' => $oldInput
        ], $errors, 422);
    }
}