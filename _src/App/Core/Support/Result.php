<?php

namespace App\Core\Support;

class Result
{
    public function __construct(
        public bool $success,
        public ?string $message = null,
        public mixed $data = null,
        public ?array $errors = null,
        public int $statusCode = 200
    ) {}

    // Result::success("Ok", $data);
    public static function success(?string $message = 'OK', mixed $data = null, int $statusCode = 200): self {
        return new self(true, $message, $data, null, $statusCode);
    }

    // Result::error('Chyba.', $errors, $data);
    public static function error(?string $message = 'Error', ?array $errors = null, mixed $data = null, int $statusCode = 400 ): self 
    {
        return new self(false, $message, $data, $errors, $statusCode);
    }

    // Result::validation($errors, $data);
    public static function validation(array $errors, mixed $data = null, ?string $message = 'Validation error', int $statusCode = 422): self 
    {
        return new self(false, $message, $data, $errors, $statusCode);
    }
}