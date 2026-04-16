<?php
namespace App\Core\Validation;

final class Sanitizer
{
    public static function string(?string $value): string
    {
        return trim((string)$value);
    }

    public static function email(?string $value): string
    {
        return mb_strtolower(trim((string)$value));
    }

    public static function int(mixed $value, int $default = 0): int
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : $default;
    }

    public static function normalizeWhitespace(?string $value): string
    {
        $value = trim((string)$value);
        return preg_replace('/\s+/u', ' ', $value) ?? '';
    }

    public static function stripControlChars(?string $value): string
    {
        return preg_replace('/[\x00-\x1F\x7F]/u', '', (string)$value) ?? '';
    }

    public static function plainText(?string $value): string
    {
        $value = self::string($value);
        $value = self::stripControlChars($value);
        return self::normalizeWhitespace($value);
    }
}