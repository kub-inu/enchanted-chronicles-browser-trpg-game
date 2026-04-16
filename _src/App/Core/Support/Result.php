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

    public static function success(mixed $data = null, ?string $message = 'OK', int $statusCode = 200): self {
        return new self(
            success: true,
            message: $message,
            data: $data,
            errors: null,
            statusCode: $statusCode
        );
    }

    public static function failure(?string $message = 'Error', ?array $errors = null, mixed $data = null, int $statusCode = 400 ): self 
    {
        return new self(
            success: false,
            message: $message,
            data: $data,
            errors: $errors,
            statusCode: $statusCode
        );
    }

    public static function validation(array $errors, mixed $data = null, ?string $message = 'Validation error', int $statusCode = 422): self 
    {
        return new self(false, $message, $data, $errors, $statusCode);
    }
}