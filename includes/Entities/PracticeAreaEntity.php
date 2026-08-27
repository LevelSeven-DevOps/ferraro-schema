<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class PracticeAreaEntity
 * * Represents a Practice Area, mapped to Schema.org 'Service'.
 */
final class PracticeAreaEntity extends Entity
{
    public function __construct(
        string $id,
        private readonly string $name,
        private readonly string $url,
        private readonly ?string $description = null,
        private readonly ?string $providerId = null
    ) {
        parent::__construct($id, 'Service');
    }

    public function toArray(): array
    {
        return [
            '@type' => $this->getType(),
            '@id' => $this->getId(),
            'name' => $this->name,
            'url' => $this->url,
            'description' => $this->description,
            'provider' => $this->providerId ? ['@id' => $this->providerId] : null,
        ];
    }
}