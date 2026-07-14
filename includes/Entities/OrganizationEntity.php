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
     */
    public function __construct(
        string $id,
        private readonly string $name,
        private readonly string $url,
        private readonly ImageEntity|string|null $logo = null,
        private readonly array $sameAs = []
    ) {
        parent::__construct($id, 'Organization');
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
        ];
    }
}