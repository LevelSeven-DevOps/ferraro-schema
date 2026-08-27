<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class OrganizationEntity
 * * Represents a Schema.org Organization (specifically the law firm).
 */
final class OrganizationEntity extends Entity
{
    public function __construct(
        string $id,
        private readonly string $name,
        private readonly string $url,
        private readonly ?string $logoId = null,
        private readonly array $sameAs = [],
        private readonly ?string $addressId = null,
        private readonly ?string $openingHours = null,
        private readonly ?string $offerCatalogId = null,
        private readonly array $reviewIds = []
    ) {
        parent::__construct($id, 'LegalService');
    }

    public function toArray(): array
    {
        return [
            '@type' => $this->getType(),
            '@id' => $this->getId(),
            'name' => $this->name,
            'url' => $this->url,
            'logo' => $this->logoId ? ['@id' => $this->logoId] : null,
            'sameAs' => $this->sameAs,
            'address' => $this->addressId ? ['@id' => $this->addressId] : null,
            'openingHours' => $this->openingHours,
            'hasOfferCatalog' => $this->offerCatalogId ? ['@id' => $this->offerCatalogId] : null,
            'review' => !empty($this->reviewIds) ? array_map(fn($rId) => ['@id' => $rId], $this->reviewIds) : null,
        ];
    }
}