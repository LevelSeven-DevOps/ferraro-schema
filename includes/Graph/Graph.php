<?php

declare(strict_types=1);

namespace Ferraro\Schema\Graph;

use Ferraro\Schema\Contracts\EntityInterface;

/**
 * Class Graph
 * * Manages the collection of schema entities and handles automatic deduplication by canonical @id.
 */
final class Graph
{
    /**
     * Map of entities indexed by their canonical @id string.
     *
     * @var array<string, EntityInterface>
     */
    private array $entities = [];

    /**
     * Add an entity to the graph. If an entity with the same @id exists, it will be merged/overwritten.
     *
     * @param EntityInterface $entity
     * @return void
     */
    public function add(EntityInterface $entity): void
    {
        $id = $entity->getId();

        // If the entity already exists, merge missing properties or prioritize the richer instance
        if (isset($this->entities[$id])) {
            $this->entities[$id] = $this->mergeEntities($this->entities[$id], $entity);
        } else {
            $this->entities[$id] = $entity;
        }
    }

    /**
     * Convert the entire graph array into a standard Schema.org @graph array structure.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $graphData = [];

        foreach ($this->entities as $entity) {
            $data = $entity->toArray();
            
            // Filter out null/empty values at the entity level before outputting
            $cleanData = array_filter($data, static fn($value) => $value !== null && $value !== '');
            
            if (!empty($cleanData)) {
                $graphData[] = $cleanData;
            }
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graphData,
        ];
    }

    /**
     * Merges two entity definitions sharing the same @id, preserving non-empty attributes.
     *
     * @param EntityInterface $existing
     * @param EntityInterface $new
     * @return EntityInterface
     */
    private function mergeEntities(EntityInterface $existing, EntityInterface $new): EntityInterface
    {
        $existingArray = array_filter($existing->toArray());
        $newArray = array_filter($new->toArray());

        // Favor the instance that carries more populated schema properties
        return count($newArray) >= count($existingArray) ? $new : $existing;
    }
}