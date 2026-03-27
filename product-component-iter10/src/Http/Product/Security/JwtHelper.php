<?php
declare(strict_types=1);

namespace App\Component\Product\Http\Product\Security;

final class JwtHelper
{
    public function __construct(private readonly string $secret) {}

    /** @return array<string,mixed>|null */
    public function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        [$h64, $p64, $s64] = $parts;
        $payload = json_decode($this->b64($p64), true);
        $sig = $this->b64url(hash_hmac('sha256', $h64 . '.' . $p64, $this->secret, true));
        if (!hash_equals($sig, $s64)) return null;
        if (isset($payload['exp']) && time() >= (int)$payload['exp']) return null;
        return $payload;
    }

    private function b64(string $s): string { return (string) base64_decode(strtr($s, '-_', '+/')); }
    private function b64url(string $bin): string { return rtrim(strtr(base64_encode($bin), '+/', '-_'), '='); }
}
