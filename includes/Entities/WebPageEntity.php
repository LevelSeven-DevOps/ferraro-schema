<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class WebPageEntity
 * * Represents the current webpage wrapping the main entity context.
 */
final class WebPageEntity extends Entity
{
    public function __construct(
        string $id,
        private readonly string $url,
        private readonly string $name,
        private readonly string $mainEntityId
    ) {
        parent::__construct($id, 'WebPage');
    }

    public function toArray(): array
    {
        return [
            '@type' => $this->getType(),
            '@id' => $this->getId(),
            'url' => $this->url,
            'name' => $this->name,
            'mainEntity' => ['@id' => $this->mainEntityId]
        ];
    }
}