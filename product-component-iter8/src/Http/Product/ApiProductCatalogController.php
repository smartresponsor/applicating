<?php
declare(strict_types=1);

namespace App\Component\Product\Http\Product;

use App\Component\Product\Catalog\Product\ProductCatalogAdapter;
use App\Component\Product\DTO\Product\CatalogQueryDTO;
use App\Component\Product\DTO\Product\CursorDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ApiProductCatalogController
{
    public function __construct(private readonly ProductCatalogAdapter $adapter) {}

    /**
     * List products with filters, facets, cursor pagination.
     *
     * @Route('/api/catalog', name='api_catalog_list', methods={'GET'})
     * @OA\Get(
     *   path="/api/catalog",
     *   summary="List catalog items",
     *   @OA\Parameter(name="q", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"draft","active","archived"})),
     *   @OA\Parameter(name="min_price", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="max_price", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="category_ids[]", in="query", required=false, @OA\Schema(type="array", @OA\Items(type="string"))),
     *   @OA\Parameter(name="sort", in="query", required=false, @OA\Schema(type="string", enum={"price_asc","price_desc","stock_asc","stock_desc","updated_asc","updated_desc"})),
     *   @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="cursor", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function list(Request $request): JsonResponse
    {
        $dto = new CatalogQueryDTO(
            q: $request->query->get('q'),
            status: $request->query->get('status'),
            min_price: $request->query->getInt('min_price', null),
            max_price: $request->query->getInt('max_price', null),
            category_ids: $request->query->all('category_ids'),
            attrs: null, // можно парсить из JSON-параметра
            sort: $request->query->get('sort', 'updated_desc'),
            limit: $request->query->getInt('limit', 20),
            cursor: $request->query->get('cursor')
        );

        $payload = [
            'q' => $dto->q,
            'status' => $dto->status,
            'min_price' => $dto->min_price,
            'max_price' => $dto->max_price,
            'category_ids' => $dto->category_ids,
            'attrs' => $dto->attrs,
            'sort' => $dto->sort,
            'limit' => $dto->limit,
            'cursor' => $dto->cursor,
        ];

        $data = $this->adapter->list($payload);
        return new JsonResponse($data, 200);
    }

    /**
     * Show single product by id.
     *
     * @Route('/api/catalog/{id}', name='api_catalog_show', methods={'GET'})
     * @OA\Get(
     *   path="/api/catalog/{id}",
     *   summary="Show a catalog item",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $data = $this->adapter->show(['id' => $id]);
            return new JsonResponse($data, 200);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => 'Not Found'], 404);
        }
    }

    /**
     * Facets only (lightweight).
     *
     * @Route('/api/catalog/facets', name='api_catalog_facets', methods={'GET'})
     * @OA\Get(
     *   path="/api/catalog/facets",
     *   summary="Get facet stats",
     *   @OA\Parameter(name="q", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function facets(Request $request): JsonResponse
    {
        $filters = [
            'q' => $request->query->get('q'),
            'status' => $request->query->get('status'),
        ];
        $data = $this->adapter->facetStats($filters);
        return new JsonResponse(['facets' => $data], 200);
    }
}
