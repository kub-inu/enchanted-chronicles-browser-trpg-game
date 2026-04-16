<?php
use App\Core\Exceptions\HttpException;

if (!function_exists('abort')) {
    function abort(int $statusCode, string $message = '', array $context = []): never
    {
        throw new HttpException($statusCode, $message, $context);
    }
}


function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        throw new RuntimeException(".env file not found: {$path}");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);

        $_ENV[trim($key)] = trim($value);
    }
}


function config(string $name): array
{
    static $cache = [];

    if (isset($cache[$name])) {
        return $cache[$name];
    }

    $path = ROOT_DIR . '/_src/Config/' . $name . '.php';

    if (!file_exists($path)) {
        throw new RuntimeException("Config file '{$name}.php' not found.");
    }

    $data = require $path;

    if (!is_array($data)) {
        throw new RuntimeException("Config file '{$name}.php' must return an array.");
    }

    $cache[$name] = $data;

    return $data;
}