<?php

namespace App\Core\Exceptions;

use Exception;

class HttpException extends Exception
{
    protected array $context;

    public function __construct(int $statusCode, string $message = '', array $context = [])
    {
        parent::__construct($message, $statusCode);
        $this->context = $context;
    }

    public function getStatusCode(): int
    {
        return $this->getCode() ?: 500;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}