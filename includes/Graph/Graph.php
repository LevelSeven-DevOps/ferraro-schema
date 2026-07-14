<?php

declare(strict_types=1);

namespace Ferraro\Schema\Graph;

use Ferraro\Schema\Contracts\EntityInterface;

/**
 * Class Graph
 * * Graph container that maintains individual schema entities and formats them as a valid Schema.org knowledge graph.
 */
final class Graph
{
    /**
     * Internal lookup array mapping Entity @id to the Entity object.
     *
     * @var array<string, EntityInterface>
     */
    private array $entities = [];

    /**
     * Add an entity to the graph. Prevents duplicates by overwriting keys.
     *
     * @param EntityInterface $entity
     * @return void
     */
    public function add(EntityInterface $entity): void
    {
        $this->entities[$entity->getId()] = $entity;
    }

    /**
     * Find an entity in the graph by its @id.
     *
     * @param string $id
     * @return EntityInterface|null
     */
    public function find(string $id): ?EntityInterface
    {
        return $this->entities[$id] ?? null;
    }

    /**
     * Convert the collective graph to standard JSON-LD representation.
     *
     * @return array{
     * @context: string,
     * @graph: array<int, array<string, mixed>>
     * }
     */
    public function toArray(): array
    {
        $graphArray = [];
        foreach ($this->entities as $entity) {
            $graphArray[] = $entity->toArray();
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graphArray,
        ];
    }
}