<?php

const ROOT_DIR = __DIR__;

require_once ROOT_DIR . '/_src/Helpers/functions.php';
loadEnv(ROOT_DIR . '/.env');

$app = config('app');

if ($app['debug']) {
    error_reporting(E_ALL);
    ini_set('display_startup_errors', '1');
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_startup_errors', '0');
    ini_set('display_errors', '0');
}

date_default_timezone_set($app['timezone']);

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = ROOT_DIR . '/_src/App/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});