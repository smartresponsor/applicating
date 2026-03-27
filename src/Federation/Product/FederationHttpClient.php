<?php

declare(strict_types=1);

namespace App\Federation\Product;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class FederationHttpClient
{
    public function __construct(private HttpClientInterface $http, private string $jwtServiceToken)
    {
    }

    public function fetchSnapshot(string $baseUrl, string $federationId): array
    {
        $url = rtrim($baseUrl, '/').'/api/federation/snapshot/'.$federationId;
        $resp = $this->http->request('GET', $url, ['headers' => ['Authorization' => 'Bearer '.$this->jwtServiceToken], 'timeout' => 10]);
        if (200 !== $resp->getStatusCode()) {
            throw new \RuntimeException('Federation fetch failed: '.$resp->getStatusCode());
        }

return $resp->toArray();
    }
}
