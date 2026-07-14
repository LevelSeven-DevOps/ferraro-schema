<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class ImageEntity
 * * Represents a Schema.org ImageObject.
 */
final class ImageEntity extends Entity
{
    public function __construct(
        string $id,
        private readonly string $url,
        private readonly ?int $width = null,
        private readonly ?int $height = null,
        private readonly ?string $caption = null
    ) {
        parent::__construct($id, 'ImageObject');
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            '@type' => $this->getType(),
            '@id' => $this->getId(),
            'url' => $this->url,
            'width' => $this->width,
            'height' => $this->height,
            'caption' => $this->caption,
        ];
    }
}