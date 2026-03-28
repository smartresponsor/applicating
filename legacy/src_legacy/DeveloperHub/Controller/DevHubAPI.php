<?php

declare(strict_types=1);

namespace App\Component\Product\DeveloperHub\Controller;

use App\Component\Product\DeveloperHub\APIKeyManager;
use App\Component\Product\DeveloperHub\Publisher;
use App\Component\Product\DeveloperHub\SubgraphTester;
use App\Component\Product\DeveloperHub\TemplateGenerator;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class DevHubAPI
{
    public function __construct(
        private readonly Connection $db,
        private readonly APIKeyManager $keys,
        private readonly TemplateGenerator $tpl,
        private readonly Publisher $pub,
        private readonly SubgraphTester $tester,
    ) {
    }

    #[Route('/api/devhub/keys/issue', methods: ['POST'])]
    public function issueKey(Request $req): JsonResponse
    {
        $dev = (string) ($req->get('developer') ?? 'dev1');
        $label = (string) ($req->get('label') ?? 'default');
        $key = $this->keys->issue($dev, $label);

        return new JsonResponse(['ok' => true, 'api_key' => $key]);
    }

    #[Route('/api/devhub/keys/revoke', methods: ['POST'])]
    public function revoke(Request $req): JsonResponse
    {
        $key = (string) ($req->get('api_key') ?? '');
        $this->keys->revoke($key);

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/devhub/plugin/template', methods: ['GET'])]
    public function template(Request $req): JsonResponse
    {
        $name = (string) ($req->get('name') ?? 'my-plugin');
        $author = (string) ($req->get('author') ?? 'you');
        $price = (float) ($req->get('price') ?? 0);

        return new JsonResponse($this->tpl->make($name, $author, $price));
    }

    #[Route('/api/devhub/plugin/publish', methods: ['POST'])]
    public function publish(Request $req): JsonResponse
    {
        $apiKey = (string) ($req->get('api_key') ?? '');
        $manifest = (string) ($req->get('manifest') ?? '{}');
        $id = $this->pub->publish($apiKey, $manifest, 'dev');

        return new JsonResponse(['ok' => true, 'plugin_id' => $id]);
    }

    #[Route('/api/devhub/subgraph/test', methods: ['POST'])]
    public function testSubgraph(Request $req): JsonResponse
    {
        $sdl = (string) ($req->get('sdl') ?? '');
        $errors = $this->tester->validateSDL($sdl);

        return new JsonResponse(['ok' => 0 === count($errors), 'errors' => $errors]);
    }
}
