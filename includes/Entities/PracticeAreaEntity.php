<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class PracticeAreaEntity
 * * Represents a Practice Area, mapped to Schema.org 'Service'.
 */
final class PracticeAreaEntity extends Entity
{
    /**
     * @param string $id
     * @param string $name
     * @param string $url
     * @param string|null $description
     * @param OrganizationEntity|null $provider The law firm providing the service.
     */
    public function __construct(
        string $id,
        private readonly string $name,
        private readonly string $url,
        private readonly ?string $description = null,
        private readonly ?OrganizationEntity $provider = null
    ) {
        parent::__construct($id, 'Service');
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
            'description' => $this->description,
            'provider' => $this->serializeValue($this->provider),
        ];
    }
}