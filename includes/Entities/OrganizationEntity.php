<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class OrganizationEntity
 * * Represents a Schema.org Organization (specifically the law firm).
 */
final class OrganizationEntity extends Entity
{
    /**
     * @param string $id
     * @param string $name
     * @param string $url
     * @param ImageEntity|string|null $logo
     * @param array<string> $sameAs Social media connections and directories.
     * @param PostalAddressEntity|null $address Physical address of the organization.
     * @param string|null $openingHours Business operating hours.
     */
    public function __construct(
        string $id,
        private readonly string $name,
        private readonly string $url,
        private readonly ImageEntity|string|null $logo = null,
        private readonly array $sameAs = [],
        private readonly ?PostalAddressEntity $address = null,
        private readonly ?string $openingHours = null // ADDED THIS PARAMETER
    ) {
        // Since we are adding physical/local business traits (address & opening hours),
        // we map this to the LegalService type (which extends LocalBusiness & Organization)
        // for optimal local SEO.
        parent::__construct($id, 'LegalService');
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            '@type' => $this->getType(),
            '@id' => $this->getId(),
            'name' => $this->name,
            'url' => $this->url,
            'logo' => $this->serializeValue($this->logo),
            'sameAs' => $this->sameAs,
            'address' => $this->serializeValue($this->address),
            'openingHours' => $this->openingHours, // ADDED THIS PROPERTY
        ];
    }
}