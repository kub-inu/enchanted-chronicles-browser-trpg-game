<?php

namespace App\Core\Exceptions;


use Throwable;
use App\Core\Logging\Logger;
use App\Core\Auth\Auth;

use App\Core\Exceptions\HttpException;

class ErrorHandler
{
    /**
     * Zaregistruje globálne handlery pre exceptions a PHP errors.
     * Volá sa raz pri bootstrape aplikácie, typicky v public/index.php.
     */

    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Hlavný vstupný bod pre všetky neodchytené exceptions.
     * Ak ide o HttpException, spracuje ju ako riadenú HTTP chybu.
     * Inak ju považuje za internú chybu aplikácie (500).
     */ 

    public static function handleException(Throwable $e): never
    {
        if ($e instanceof HttpException) {
            self::handleHttpException($e);
            exit;
        }

        self::handleThrowable($e);
    }

    /**
     * Spracuje riadenú HTTP chybu, napr. 403 / 404 / 419.
     * Nastaví HTTP status code, zaloguje chybu
     * a vráti JSON alebo HTML error stránku podľa typu requestu.
     */
    public static function handleHttpException(HttpException $e): never
    {
        $statusCode = $e->getStatusCode();
        $message = $e->getMessage();

        http_response_code($statusCode);

        self::safeLogHttpException($e);

        $publicMessage = self::publicMessage($statusCode, $message);

        if (self::expectsJson()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => $statusCode,
                    'message' => $publicMessage,
                ],
            ]);
            exit;
        }

        self::renderErrorPage($statusCode, $publicMessage);
    }

    /**
     * Spracuje všetky neočakávané chyby ako interný server error (500).
     * Zaloguje detail chyby, ale používateľovi ukáže len bezpečnú správu.
     */ 
    public static function handleThrowable(Throwable $e): never
    {
        error_log('HANDLE THROWABLE HIT: ' . $e->getMessage());
        file_put_contents(
    ROOT_DIR . '/storage/logs/debug.log',
    json_encode([
        'time' => date('Y-m-d H:i:s'),

        'type' => get_class($e),
        'message' => $e->getMessage(),

        'file' => $e->getFile(),
        'line' => $e->getLine(),

        'trace' => $e->getTraceAsString(),

        'request' => [
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ],

        'user' => [
            'id' => Auth::id(),
            'role' => Auth::role(),
        ],

        'session' => $_SESSION ?? [],

    ], JSON_PRETTY_PRINT) . PHP_EOL . str_repeat('-', 80) . PHP_EOL,
    FILE_APPEND
);

        http_response_code(500);

        self::safeLogThrowable($e);

        if (self::expectsJson()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => 'Internal server error',
                ],
            ]);
            exit;
        }

        self::renderErrorPage(500, 'Internal server error');
    }


    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];

        if (!in_array($error['type'], $fatalTypes, true)) {
            return;
        }

        self::handleThrowable(
            new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            )
        );
    }

    /**
     * Prevedie PHP error/warning/notice na ErrorException,
     * aby sa všetko spracovávalo jednotne cez exception flow.
     *
     * Ak konkrétny severity level nie je aktívny v error_reporting(),
     * handler ho ignoruje a vráti false.
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }   

    /**
     * Zistí, či klient očakáva JSON odpoveď.
     * Používa Accept header alebo X-Requested-With pre AJAX.
     */

    private static function expectsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

        return str_contains($accept, 'application/json')
            || strtolower($requestedWith) === 'xmlhttprequest';
    }

    /**
     * Vráti bezpečnú verejnú správu podľa HTTP status kódu.
     * Fallback správu použije len tam, kde to dáva zmysel.
     */
    private static function publicMessage(int $statusCode, string $fallback = ''): string
    {
        return match ($statusCode) {
            401 => 'Authentication required',
            403 => 'You do not have permission to access this resource',
            404 => 'Requested resource was not found',
            405 => 'Method not allowed',
            419 => 'Page expired or invalid security token',
            500 => 'Internal server error',
            default => $fallback ?: 'Request failed',
        };
    }

    /**
     * Pokúsi sa načítať HTML error view podľa status code.
     * Ak view neexistuje, zobrazí jednoduchý fallback HTML output.
     */
    private static function renderErrorPage(int $statusCode, string $message): never
    {
        $viewPath = ROOT_DIR . '/views/errors/' . $statusCode . '.php';

        if (file_exists($viewPath)) {
            require $viewPath;
            exit;
        }

        echo "<h1>{$statusCode}</h1>";
        echo "<p>" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "</p>";
        exit;
    }

    /**
     * Reálne logovanie riadenej HTTP chyby.
     * Ukladá status, message, context a základné request info.
     */
    private static function logHttpException(HttpException $e): void
    {
        Logger::warning('HTTP exception', [
            'status_code' => $e->getStatusCode(),
            'message' => $e->getMessage(),
            'context' => $e->getContext(),
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
            'user_id' => self::safeUserId(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    /**
     * Reálne logovanie neočakávanej chyby / exceptionu.
     * Ukladá technické detaily pre debugging.
     */
    private static function logThrowable(Throwable $e): void
    {
        Logger::error('Unhandled exception', [
            'message' => $e->getMessage(),
            'type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
            'user_id' => self::safeUserId(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    /**
     * Bezpečný wrapper pre logovanie HttpException.
     * Ak logger alebo auth zlyhá, chyba sa potichu ignoruje,
     * aby nespadol samotný error handler.
     */
    private static function safeLogHttpException(HttpException $e): void
    {
        try {
            self::logHttpException($e);
        } catch (Throwable $loggingError) {
            // Logger nesmie rozbiť error handler
            file_put_contents(
                ROOT_DIR . '/storage/logs/logger_fail.log',
                json_encode([
                    'time' => date('Y-m-d H:i:s'),
                    'message' => $loggingError->getMessage(),
                    'file' => $loggingError->getFile(),
                    'line' => $loggingError->getLine(),
                    'trace' => $loggingError->getTraceAsString(),
                ], JSON_PRETTY_PRINT) . PHP_EOL,
                FILE_APPEND
            );
        }
    }

    /**
     * Bezpečný wrapper pre logovanie neočakávanej chyby.
     * Chráni error flow pred pádom loggera.
     */
    private static function safeLogThrowable(Throwable $e): void
    {
        try {
            self::logThrowable($e);
        } catch (Throwable $loggingError) {
            // Logger nesmie rozbiť error handler
            file_put_contents(
                ROOT_DIR . '/storage/logs/logger_fail.log',
                json_encode([
                    'time' => date('Y-m-d H:i:s'),
                    'message' => $loggingError->getMessage(),
                    'file' => $loggingError->getFile(),
                    'line' => $loggingError->getLine(),
                    'trace' => $loggingError->getTraceAsString(),
                ], JSON_PRETTY_PRINT) . PHP_EOL,
                FILE_APPEND
            );
        }
    }

    /**
     * Bezpečne získa ID aktuálne prihláseného používateľa.
     * Ak Auth vrstva nie je dostupná alebo zlyhá, vráti null.
     */
    private static function safeUserId(): ?int
    {
        try {
            return Auth::id();
        } catch (Throwable $e) {
            return null;
        }
    }

}


// register() → zapne celý error systém
// handleException() → hlavný rozdeľovač
// handleHttpException() → riadené HTTP chyby
// handleThrowable() → všetko nečakané = 500
// handleError() → PHP errory prehodí na exceptiony
// expectsJson() → rozhodne JSON vs HTML
// publicMessage() → bezpečný text pre usera
// renderErrorPage() → zobrazí HTML chybu
// logHttpException() / logThrowable() → reálne logovanie
// safeLog...() → ochrana, aby logger nerozbil handler
// safeUserId() → bezpečné čítanie user ID