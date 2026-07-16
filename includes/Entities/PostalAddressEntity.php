<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class PostalAddressEntity
 * * Represents a Schema.org PostalAddress.
 */
final class PostalAddressEntity extends Entity
{
    /**
     * @param string $id
     * @param string $streetAddress
     * @param string $postalCode
     * @param string $addressLocality
     * @param string $addressRegion
     * @param string $addressCountry
     */
    public function __construct(
        string $id,
        private readonly string $streetAddress,
        private readonly string $postalCode,
        private readonly string $addressLocality,
        private readonly string $addressRegion,
        private readonly string $addressCountry = 'US'
    ) {
        parent::__construct($id, 'PostalAddress');
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            '@type' => $this->getType(),
            '@id' => $this->getId(),
            'streetAddress' => $this->streetAddress,
            'postalCode' => $this->postalCode,
            'addressLocality' => $this->addressLocality,
            'addressRegion' => $this->addressRegion,
            'addressCountry' => [
                '@type' => 'Country',
                'name' => $this->addressCountry
            ],
        ];
    }
}