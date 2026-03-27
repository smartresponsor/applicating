<?php

declare(strict_types=1);

namespace ApiPlatform\Metadata;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ApiResource
{
    /** @param array<string, mixed> $data */
    public function __construct(array $data = [], mixed ...$named)
    {
    }
}

class Operation
{
}

namespace ApiPlatform\Metadata\GraphQl;

class Query
{
    public function __construct(?string $name = null, ?string $resolver = null)
    {
    }
}

class QueryCollection
{
    public function __construct(?string $name = null, ?string $resolver = null)
    {
    }
}

namespace ApiPlatform\State;

use ApiPlatform\Metadata\Operation;

interface ProviderInterface
{
    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): mixed;
}

namespace ApiPlatform\GraphQl\Resolver;

interface QueryCollectionResolverInterface
{
    /**
     * @param iterable<mixed> $collection
     * @param array<string, mixed> $context
     * @return iterable<mixed>
     */
    public function __invoke(iterable $collection, array $context): iterable;
}
