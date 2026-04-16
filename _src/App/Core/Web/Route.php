<?php
namespace App\Core\Web;

class Route {

    public const METHODS = ['GET', 'POST', 'PUT', 'DELETE'];

    public string $method;
    public string $url;
    public string $module;
    public mixed $handler;
    public array $middleware = [];
    public ?string $layout = ROOT_DIR . '/views/layouts/master_layout.php';

    public function __construct(string $method, string $url, string $module, callable|string|null $handler = null) {
        
        if (!in_array($method, self::METHODS, true)) {
            throw new \InvalidArgumentException("Invalid HTTP method: {$method}");
        }

        $this->method = $method;
        $this->url = $url;
        $this->module = $module;
        $this->handler = $handler;
    }

    public function middleware(string|array ...$middleware): self {
        foreach ((array) $middleware as $m) {
            $this->middleware[] = $m;
        }
        return $this;
    }

    public function setLayout(string $layout): self {
        $this->layout = $layout;
        return $this;
    }
}