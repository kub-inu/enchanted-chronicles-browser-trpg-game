<?php

namespace App\Core\Logging;

class Logger
{
    private static function write(string $level, string $message, array $context = [], string $channel = 'app'): void
    {
        $dateTime = date('Y-m-d H:i:s');
        $dateFile = date('Y-m-d');

        $line = sprintf(
            "[%s] %s: %s%s%s",
            $dateTime,
            strtoupper($level),
            $message,
            !empty($context)
                ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : '',
            PHP_EOL
        );

        $logDir = ROOT_DIR . '/storage/logs';

        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0755, true) && !is_dir($logDir)) {
                throw new \RuntimeException('Logger failed to create log directory: ' . $logDir);
            }
        }

        $safeChannel = self::sanitizeChannel($channel);
        $file = $logDir . '/' . $safeChannel . '-' . $dateFile . '.log';

        $result = file_put_contents($file, $line, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            throw new \RuntimeException('Logger failed to write to file: ' . $file);
        }
    }

    public static function info(string $message, array $context = [], string $channel = 'app'): void
    {
        self::write('info', $message, $context, $channel);
    }

    public static function warning(string $message, array $context = [], string $channel = 'app'): void
    {
        self::write('warning', $message, $context, $channel);
    }

    public static function error(string $message, array $context = [], string $channel = 'app'): void
    {
        self::write('error', $message, $context, $channel);
    }

    private static function sanitizeChannel(string $channel): string
    {
        $channel = strtolower(trim($channel));
        $channel = preg_replace('/[^a-z0-9_-]/', '', $channel);

        return $channel !== '' ? $channel : 'app';
    }
}