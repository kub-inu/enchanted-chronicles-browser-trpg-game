<?php
//Idčko requestu - do logu
$GLOBALS['app_request_id'] = bin2hex(random_bytes(16));

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Session\Session;
use App\Core\Exceptions\ErrorHandler;
use App\Core\Http\Request;
use App\Core\Web\Router;

if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    Session::start();
}

ErrorHandler::register();
$request = Request::capture();

$router = new Router();
require_once ROOT_DIR . '/_src/Routes/_init.php';

$router->dispatch($request);
