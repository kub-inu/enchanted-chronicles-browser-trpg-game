<?php
namespace App\Core\Security;

final class Escaper
{
    public static function html(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function attr(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function url(?string $value): string
    {
        return filter_var((string)$value, FILTER_SANITIZE_URL) ?: '';
    }

    public static function jsString(?string $value): string
    {
        return json_encode((string)$value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '""';
    }
}