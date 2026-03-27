<?php

declare(strict_types=1);

namespace App\Component\Product\Http\Product;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ApiProductCatalogController
{
    #[Route('/api/catalog', name: 'api_catalog_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return new JsonResponse(['items' => [], 'next_cursor' => null]);
    }
}
