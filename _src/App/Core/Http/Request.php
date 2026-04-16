<?php
namespace App\Core\Http;

class Request
{
    protected array $get;
    protected array $post;
    protected array $server;
    protected array $files;

    // ----------------------------
    // Zachytenie requestu
    // ----------------------------
    public function __construct(array $get = [], array $post = [], array $server = [], array $files = [])
    {
        $this->get = $get;
        $this->post = $post;
        $this->server = $server;
        $this->files = $files;

        //Pre AXIOS requesty
        if ($this->isPost() && $this->server['CONTENT_TYPE'] ?? '' === 'application/json') {
            $json = json_decode(file_get_contents('php://input'), true);
            if (is_array($json)) {
                // merge alebo prepíše POST parametre
                $this->post = array_merge($this->post, $json);
            }
        }
    }

    // ----------------------------
    // Factory: zachyť aktuálny request
    // ----------------------------
    public static function capture(): self
    {
        return new self($_GET, $_POST, $_SERVER, $_FILES);
    }

    // ----------------------------
    // HTTP metóda
    // ----------------------------
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    // ----------------------------
    // Path
    // ----------------------------
    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);
        return rtrim($uri, '/') ?: '/';
    }

    // ----------------------------
    // Získa GET alebo POST parameter
    // ----------------------------
    public function input(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    // ----------------------------
    // Kontrola, či je POST
    // ----------------------------
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    // ----------------------------
    // Celý GET/POST array
    // ----------------------------
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    // ----------------------------
    // Súbory
    // ----------------------------
    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }
}
