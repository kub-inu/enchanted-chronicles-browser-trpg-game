<?php
namespace App\Core\Security;

final class CspPolicy
{
    private array $directives = [
        'default-src' => ["'self'"],
        'script-src' => ["'self'"],
        'style-src' => ["'self'"],
        'img-src' => ["'self'", 'data:'],
        'font-src' => ["'self'"],
        'connect-src' => ["'self'"],
        'object-src' => ["'none'"],
        'base-uri' => ["'self'"],
        'frame-ancestors' => ["'none'"],
        'form-action' => ["'self'"],
    ];

    public function addSource(string $directive, string $source): self
    {
        if (!isset($this->directives[$directive])) {
            $this->directives[$directive] = [];
        }

        if (!in_array($source, $this->directives[$directive], true)) {
            $this->directives[$directive][] = $source;
        }

        return $this;
    }

    public function setDirective(string $directive, array $sources): self
    {
        $this->directives[$directive] = $sources;
        return $this;
    }

    public function build(): string
    {
        $parts = [];

        foreach ($this->directives as $directive => $sources) {
            $parts[] = $directive . ' ' . implode(' ', $sources);
        }

        return implode('; ', $parts);
    }
}