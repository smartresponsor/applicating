<?php

declare(strict_types=1);

namespace App\Component\Product\Integration\Vault;

final class VaultClient
{
    public function __construct(
        private readonly string $addr,
        private string $token,
    ) {
    }

    /** @return array<string,mixed>|null */
    public function read(string $path): ?array
    {
        $url = rtrim($this->addr, '/').'/v1/'.ltrim($path, '/');
        $opts = ['http' => [
            'method' => 'GET',
            'header' => 'X-Vault-Token: '.$this->token,
            'timeout' => 3,
        ]];
        $json = @file_get_contents($url, false, stream_context_create($opts));
        if (false === $json) {
            return null;
        }
        $data = json_decode($json, true);

        return $data['data'] ?? $data;
    }

    /** @param array<string,mixed> $data */
    public function write(string $path, array $data): bool
    {
        $url = rtrim($this->addr, '/').'/v1/'.ltrim($path, '/');
        $opts = ['http' => [
            'method' => 'POST',
            'header' => "X-Vault-Token: {$this->token}\r\nContent-Type: application/json",
            'content' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'timeout' => 3,
        ]];

        return (bool) @file_get_contents($url, false, stream_context_create($opts));
    }

    public function renewSelf(): bool
    {
        $url = rtrim($this->addr, '/').'/v1/auth/token/renew-self';
        $opts = ['http' => [
            'method' => 'POST',
            'header' => 'X-Vault-Token: '.$this->token,
            'timeout' => 3,
        ]];

        return (bool) @file_get_contents($url, false, stream_context_create($opts));
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }
}
