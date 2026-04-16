<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Exceptions\ErrorHandler;
use App\Core\Http\Request;
use App\Core\Web\Router;

ErrorHandler::register();
$request = Request::capture();

$router = new Router();
require_once ROOT_DIR . '/_src/Routes/_init.php';
$router->dispatch($request);
