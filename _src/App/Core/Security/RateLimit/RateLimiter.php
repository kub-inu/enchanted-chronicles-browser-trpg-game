<?php
namespace App\Core\Security\RateLimit;

use App\Core\Security\RateLimit\RateLimitRepository;
use InvalidArgumentException;

final class RateLimiter
{
    private string $domain;
    private string $identifier;
    private string $key;
    private int $maxAttempts;
    private int $windowSeconds;

    private RateLimitRepository $repository;

    public function __construct(string $domain, string|int $identifier)
    {
        $this->domain = $domain;
        $this->identifier = (string) $identifier;
        $this->repository = new RateLimitRepository();

        $this->resolveDomainConfig();
        $this->buildKey();
    }

    private function resolveDomainConfig(): void
    {
        switch ($this->domain) {
            case 'login':
                $this->maxAttempts = 5;
                $this->windowSeconds = 900;
                break;

            case 'register':
                $this->maxAttempts = 3;
                $this->windowSeconds = 3600;
                break;

            case 'password_reset':
                $this->maxAttempts = 3;
                $this->windowSeconds = 1800;
                break;

            default:
                throw new InvalidArgumentException('Unknown rate limit domain.');
        }
    }

    private function buildKey(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $this->key = $this->domain . ':' . hash('sha256', $ip . '|' . $this->identifier);
    }

    public function checkOrFail(): void
    {
        $record = $this->repository->get($this->key);
        $now = time();

        if (!$record) {
            return;
        }

        $expiresAt = (int) $record['expires_at'];
        $attempts = (int) $record['attempts'];

        if ($expiresAt <= $now) {
            $this->clear();
            return;
        }

        if ($attempts >= $this->maxAttempts) {
            Response::error('Too many attempts. Try again later.', ['retry_after' => $expiresAt - $now], 429);
        }
    }

    public function hit(): void
    {
        $record = $this->repository->get($this->key);
        $now = time();

        if (!$record || (int) $record['expires_at'] <= $now) {
            $this->repository->save($this->key, 1, $now + $this->windowSeconds);
            return;
        }

        $this->repository->save($this->key, (int) $record['attempts'] + 1, (int) $record['expires_at']);
    }

    public function clear(): void
    {
        $this->repository->clear($this->key);
    }
}