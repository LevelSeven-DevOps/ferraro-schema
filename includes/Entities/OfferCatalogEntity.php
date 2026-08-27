<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class OfferCatalogEntity
 * * Groups multiple Service entities under a unified law firm catalog.
 */
final class OfferCatalogEntity extends Entity
{
    public function __construct(
        string $id,
        private readonly string $name,
        private readonly array $serviceIds
    ) {
        parent::__construct($id, 'OfferCatalog');
    }

    public function toArray(): array
    {
        $items = array_map(fn(string $serviceId) => ['@id' => $serviceId], $this->serviceIds);
        
        return [
            '@type' => $this->getType(),
            '@id' => $this->getId(),
            'name' => $this->name,
            'itemListElement' => $items
        ];
    }
}