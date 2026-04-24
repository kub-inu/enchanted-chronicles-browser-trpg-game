<?php
namespace App\Core\Security;

final class SecurityHeaders
{
    public static function send(?CspPolicy $cspPolicy = null): void
    {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        $csp = $cspPolicy?->build() ?? (new CspPolicy())->build();
        header('Content-Security-Policy: ' . $csp);
    }
}