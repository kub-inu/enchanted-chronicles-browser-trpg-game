<?php
namespace App\Core\Http\Middleware;

use App\Core\Http\Request;

abstract class Middleware
{
    abstract public function handle(Request $request, array $params, callable $next, mixed $mwParam = null);
}