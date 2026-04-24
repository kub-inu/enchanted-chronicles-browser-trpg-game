<?php

namespace App\Core\Logging;

final class Logger
{
    private const DEFAULT_CHANNEL = 'app';

    /* ===================== PUBLIC API ===================== */

    public static function info(string $message, array $context = [], string $channel = self::DEFAULT_CHANNEL): void
    {
        self::write('info', $message, $context, $channel);
    }

    public static function warning(string $message, array $context = [], string $channel = self::DEFAULT_CHANNEL): void
    {
        self::write('warning', $message, $context, $channel);
    }

    public static function error(string $message, array $context = [], string $channel = self::DEFAULT_CHANNEL): void
    {
        self::write('error', $message, $context, $channel);
    }

    public static function debug(string $message, array $context = [], string $channel = self::DEFAULT_CHANNEL): void
    {
        self::write('debug', $message, $context, $channel);
    }

    public static function exception(\Throwable $e, array $context = [], string $channel = self::DEFAULT_CHANNEL): void
    {
        $context['exception'] = [
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];

        self::write('error', $e->getMessage(), $context, $channel);
    }

    /* ===================== CORE ===================== */

    private static function write(string $level, string $message, array $context, string $channel): void
    {
        $record = self::buildRecord($level, $message, $context, $channel);

        $dateDir = date('Y-m-d');
        $baseDir = ROOT_DIR . '/storage/logs';
        $dir = $baseDir . '/' . $dateDir;

        self::ensureDirectoryExists($dir);

        // text log (per channel)
        $textFile = $dir . '/' . $record['channel'] . '.log';
        $textLine = self::formatTextLine($record);

        if (file_put_contents($textFile, $textLine, FILE_APPEND | LOCK_EX) === false) {
            throw new \RuntimeException('Logger failed to write to text log: ' . $textFile);
        }

        // json log (global)
        $jsonFile = $dir . '/logs.jsonl';
        $jsonLine = self::formatJsonLine($record);

        if (file_put_contents($jsonFile, $jsonLine, FILE_APPEND | LOCK_EX) === false) {
            throw new \RuntimeException('Logger failed to write to JSON log: ' . $jsonFile);
        }
    }

    private static function buildRecord(string $level, string $message, array $context, string $channel): array
    {
        $meta = self::buildMeta();

        // override meta (napr. CLI, worker)
        if (isset($context['_meta']) && is_array($context['_meta'])) {
            $meta = array_merge($meta, $context['_meta']);
            unset($context['_meta']);
        }

        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'level'     => strtoupper($level),
            'channel'   => self::sanitizeChannel($channel),
            'message'   => $message,
            'context'   => $context,
            'meta'      => $meta,
        ];
    }

    /* ===================== META ===================== */

    private static function buildMeta(): array
    {
        return [
            'request_id' => self::resolveRequestId(),
            'user_id'    => $_SESSION['user_id'] ?? null,
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? null,
            'method'     => $_SERVER['REQUEST_METHOD'] ?? null,
            'uri'        => $_SERVER['REQUEST_URI'] ?? null,
        ];
    }

    private static function resolveRequestId(): ?string
    {
        if (!empty($_SERVER['HTTP_X_REQUEST_ID'])) {
            return (string) $_SERVER['HTTP_X_REQUEST_ID'];
        }

        if (!empty($GLOBALS['app_request_id'])) {
            return (string) $GLOBALS['app_request_id'];
        }

        return null;
    }

    /* ===================== FORMATTERS ===================== */

    private static function formatTextLine(array $r): string
    {
        $meta = sprintf(
            'rid=%s uid=%s ip=%s method=%s uri=%s',
            self::stringify($r['meta']['request_id'] ?? null),
            self::stringify($r['meta']['user_id'] ?? null),
            self::stringify($r['meta']['ip'] ?? null),
            self::stringify($r['meta']['method'] ?? null),
            self::stringify($r['meta']['uri'] ?? null),
        );

        $context = !empty($r['context'])
            ? ' | ' . json_encode($r['context'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '';

        return sprintf(
            "[%s] [%s] [%s] %s | %s%s%s",
            $r['timestamp'],
            $r['level'],
            $r['channel'],
            $r['message'],
            $meta,
            $context,
            PHP_EOL
        );
    }

    private static function formatJsonLine(array $record): string
    {
        return json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }

    /* ===================== HELPERS ===================== */

    private static function ensureDirectoryExists(string $dir): void
    {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException('Logger failed to create directory: ' . $dir);
            }
        }
    }

    private static function sanitizeChannel(string $channel): string
    {
        $channel = strtolower(trim($channel));
        $channel = preg_replace('/[^a-z0-9\-_]/', '-', $channel) ?? self::DEFAULT_CHANNEL;
        $channel = trim($channel, '-_');

        return $channel !== '' ? $channel : self::DEFAULT_CHANNEL;
    }

    private static function stringify(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '-';
    }


    //$logs = Logger::getLogs(channel: 'auth', level: 'error', limit: 50);
    public static function getLogs(?string $date = null, ?string $channel = null, ?string $level = null, ?string $requestId = null, int $limit = 100): array 
    {
        $date = $date ?? date('Y-m-d');
        $file = ROOT_DIR . '/storage/logs/' . $date . '/logs.jsonl';

        if (!file_exists($file)) {
            return [];
        }

        $lines = self::tail($file, $limit);

        $logs = [];

        foreach ($lines as $line) {
            $record = json_decode($line, true);

            if (!is_array($record)) {
                continue;
            }
            
            if ($channel !== null && ($record['channel'] ?? null) !== $channel) {
                continue;
            }

            if ($level !== null && ($record['level'] ?? null) !== strtoupper($level)) {
                continue;
            }

            if ($requestId !== null && ($record['meta']['request_id'] ?? null) !== $requestId) {
                continue;
            }

            $logs[] = $record;
        }

        return $logs;
    }


    private static function tail(string $file, int $lines = 100): array
    {
        $f = fopen($file, "rb");
        if ($f === false) {
            return [];
        }

        $buffer = '';
        $chunkSize = 4096;
        $pos = -1;
        $lineCount = 0;

        fseek($f, 0, SEEK_END);
        $fileSize = ftell($f);

        while ($lineCount < $lines && abs($pos) < $fileSize) {
            $seek = max($fileSize + $pos - $chunkSize, 0);
            $readSize = min($chunkSize, $fileSize + $pos);

            fseek($f, $seek);
            $chunk = fread($f, $readSize);

            $buffer = $chunk . $buffer;
            $lineCount = substr_count($buffer, "\n");

            $pos -= $chunkSize;
        }

        fclose($f);

        $linesArray = explode("\n", trim($buffer));

        return array_slice($linesArray, -$lines);
    }
}