<?php
namespace App\Core\Web;

use Exception;
use InvalidArgumentException;
use ReflectionMethod;
use ReflectionNamedType;


use App\Core\Http\Request;

use App\Core\Http\Middleware\AuthMiddleware;
use App\Core\Http\Middleware\RoleMiddleware;
use App\Core\Http\Middleware\GuestMiddleware;
use App\Core\Http\Middleware\CsrfMiddleware;
use App\Core\Http\Middleware\PermissionMiddleware;

class Router {

    private array $routes = [];

    private array $middlewareMap = [
        "auth" => AuthMiddleware::class,
        "role" => RoleMiddleware::class,
        "guest" => GuestMiddleware::class,
        'csrf' => CsrfMiddleware::class,
        'can' => PermissionMiddleware::class
    ];

    private array $controllerMap = [
        "auth" => 'App\\Modules\\Auth\\Controllers\\',
        "web" => 'App\\Core\\Web\\',
        "user" => 'App\\Modules\\User\\Controllers\\'
    ];

    private array $currentGroup = [
        'prefix' => '',
        'middleware' => [],
    ];


    /**
     * Registrácia route
     */

    public function get(string $url, string $module, $handler = null): Route
    {
        return $this->addRoute('GET', $url, $module, $handler);
    }

    public function post(string $url, string $module, $handler = null): Route
    {
        return $this->addRoute('POST', $url, $module, $handler);
    }

    public function put(string $url, string $module, $handler = null): Route
    {
        return $this->addRoute('PUT', $url, $module, $handler);
    }

    public function delete(string $url, string $module, $handler = null): Route
    {
        return $this->addRoute('DELETE', $url, $module, $handler);
    }

    private function addRoute(string $method, string $url, string $module, $handler = null): Route
    {
        $route = new Route($method, $this->applyGroupPrefix($url), $module, $handler);
        $this->applyGroupMiddleware($route);
        $this->routes[$method][] = $route;

        return $route;
    }


    /**
     * Route groups
     */

    public function group(array $attributes, callable $callback): void
    {
        $previousGroup = $this->currentGroup;

        $prefix = ($previousGroup['prefix'] ?? '') . ($attributes['prefix'] ?? '');

        $this->currentGroup = [
            'prefix' => $this->normalizePrefix($prefix),
            'middleware' => array_merge(
                $previousGroup['middleware'] ?? [],
                $attributes['middleware'] ?? []
            ),
        ];

        $callback($this);

        $this->currentGroup = $previousGroup;
    }

    private function applyGroupPrefix(string $url): string
    {
        return $this->normalizePrefix(($this->currentGroup['prefix'] ?? '') . $url);
    }

    private function applyGroupMiddleware(Route $route): void {
        $middleware = $this->currentGroup['middleware'] ?? [];

        foreach ($middleware as $m) {
            $route->middleware($m);
        }
    }

    private function normalizePrefix(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }  


    // -------------------------
    // Dispatch
    // -------------------------

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        if (empty($this->routes[$method])) {
            abort(404, 'Stránka neexistuje');
        }

        foreach ($this->routes[$method] as $route) {
            $params = $this->match($route->url, $path);

            if ($params === false) {
                continue;
            }

            $this->runMiddleware($route->middleware, $params, $request);
            $this->runHandler($route, $params, $request);
            return;
        }

        abort(404, 'Stránka neexistuje');
    }



    private function runMiddleware(array $middlewareList, array $params, Request $request): void
    {
        $next = function () {
            // controller sa volá až po middleware chain
        };

        foreach (array_reverse($middlewareList) as $mw) {
            $previousNext = $next;

            $next = function () use ($mw, $request, $params, $previousNext) {
                $parts = explode(':', $mw, 2);
                $mwName = $parts[0];
                $mwParam = $parts[1] ?? null;

                if (!isset($this->middlewareMap[$mwName])) {
                    throw new Exception("Middleware '{$mwName}' not found");
                }

                $mwClass = $this->middlewareMap[$mwName];

                if (!class_exists($mwClass)) {
                    throw new Exception("Middleware class '{$mwClass}' not found");
                }

                $obj = new $mwClass();
                $obj->handle($request, $params, $previousNext, $mwParam);
            };
        }

        $next();
    }


    private function runHandler(Route $route, array $params, Request $request): void
    {
        $handler = $route->handler;
        $data = null;

        if (is_callable($handler)) {
            $data = $handler($params);
        } elseif (is_string($handler) && str_contains($handler, '@')) {

            if (!isset($this->controllerMap[$route->module])) {
                throw new Exception("Controller module '{$route->module}' not found");
            }
            
            [$class, $method] = explode('@', $handler, 2);

            $controllerNamespace = $this->controllerMap[$route->module];

            $controllerClass = $controllerNamespace . $class;

            if (!class_exists($controllerClass)) {
                throw new Exception("Controller '{$controllerClass}' not found");
            }

            $controller = new $controllerClass();
            $data = $this->invokeControllerAction($controller, $method, $request, $params);
        } else {
            throw new Exception('Invalid route handler');
        }

        $this->handleAjaxResponse($data);
    }    

// private function runHandler(Route $route, array $params, Request $request): void
// {
//     $handler = $route->handler;

//     echo '<pre>';
//     echo "MODULE: {$route->module}\n";
//     echo "HANDLER: {$handler}\n";
//     echo '</pre>';

//     if (is_callable($handler)) {
//         $data = $handler($params);
//     } elseif (is_string($handler) && str_contains($handler, '@')) {

//         if (!isset($this->controllerMap[$route->module])) {
//             throw new Exception("Controller module '{$route->module}' not found");
//         }

//         [$class, $method] = explode('@', $handler, 2);

//         $controllerNamespace = $this->controllerMap[$route->module];
//         $controllerClass = $controllerNamespace . $class;

//         echo '<pre>';
//         echo "CONTROLLER CLASS: {$controllerClass}\n";
//         echo "METHOD: {$method}\n";
//         echo "CLASS EXISTS: ";
//         var_dump(class_exists($controllerClass));
//         echo '</pre>';

//         if (!class_exists($controllerClass)) {
//             throw new Exception("Controller '{$controllerClass}' not found");
//         }

//         $controller = new $controllerClass();

//         echo '<pre>';
//         echo "METHOD EXISTS: ";
//         var_dump(method_exists($controller, $method));
//         echo '</pre>';

//         $data = $this->invokeControllerAction($controller, $method, $request, $params);
//     } else {
//         throw new Exception('Invalid route handler');
//     }

//     $this->handleAjaxResponse($data);
// }


// private function invokeControllerAction(object $controller, string $method, Request $request, array $params): mixed
// {
//     try {
//         if (!method_exists($controller, $method)) {
//             throw new Exception('Controller method "' . $method . '" not found in ' . $controller::class);
//         }

//         $reflection = new ReflectionMethod($controller, $method);
//         $arguments = $this->resolveControllerArguments($reflection, $request, $params);

//         return $reflection->invokeArgs($controller, $arguments);

//     } catch (\Throwable $e) {
//         echo '<pre>';
//         echo "EXCEPTION: " . get_class($e) . "\n";
//         echo "MESSAGE: " . $e->getMessage() . "\n";
//         echo "FILE: " . $e->getFile() . ':' . $e->getLine() . "\n\n";
//         echo $e->getTraceAsString();
//         echo '</pre>';
//         exit;
//     }
// }

    private function invokeControllerAction(object $controller, string $method, Request $request, array $params): mixed
    {
        if (!method_exists($controller, $method)) {
            throw new Exception('Controller method "' . $method . '" not found in ' . $controller::class);
        }

        $reflection = new ReflectionMethod($controller, $method);
        $arguments = $this->resolveControllerArguments($reflection, $request, $params);

        return $reflection->invokeArgs($controller, $arguments);
    }


    private function resolveControllerArguments(ReflectionMethod $method, Request $request, array $params): array
    {
        $parameters = $method->getParameters();
        $signature = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin() && $type->getName() !== 'array') {
                throw new InvalidArgumentException(
                    "Unsupported parameter \${$parameter->getName()} in {$method->class}::{$method->name}(). " .
                    "Allowed signatures: (), (Request), (array), (Request, array)."
                );
            }

            $signature[] = $type->getName();
        }

        if ($signature === []) {
            return [];
        }

        if ($signature === [Request::class]) {
            return [$request];
        }

        if ($signature === ['array']) {
            return [$params];
        }

        if ($signature === [Request::class, 'array']) {
            return [$request, $params];
        }

        throw new InvalidArgumentException(
            "Unsupported signature in {$method->class}::{$method->name}(). " .
            "Allowed signatures: (), (Request), (array), (Request, array)."
        );
    }


    private function handleAjaxResponse(mixed $data): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (!$isAjax) {
            return;
        }

        if (!is_array($data)) {
            $data = [];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // -------------------------
    // Route matching
    // -------------------------

private function match(string $pattern, string $path): array|false
{
    $pattern = preg_replace(
        '#^/\{([a-zA-Z0-9_]+)\?\}$#',
        '/(?P<$1>[^/]+)?',
        $pattern
    );

    $pattern = preg_replace(
        '#/\{([a-zA-Z0-9_]+)\?\}#',
        '(?:/(?P<$1>[^/]+))?',
        $pattern
    );

    $pattern = preg_replace(
        '#\{([a-zA-Z0-9_]+)\}#',
        '(?P<$1>[^/]+)',
        $pattern
    );

    $pattern = "#^{$pattern}$#";

    if (preg_match($pattern, $path, $matches)) {
        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    return false;
}
}