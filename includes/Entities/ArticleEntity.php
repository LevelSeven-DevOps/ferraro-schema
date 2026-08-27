<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class ArticleEntity
 * * Represents a Blog Post or Press Release, mapped to Schema.org 'BlogPosting' or 'NewsArticle'.
 */
final class ArticleEntity extends Entity
{
    public function __construct(
        string $id,
        string $type,
        private readonly string $headline,
        private readonly string $url,
        private readonly ?string $datePublished = null,
        private readonly ?string $dateModified = null,
        private readonly ?string $authorId = null,
        private readonly ?string $publisherId = null,
        private readonly ?ImageEntity $image = null
    ) {
        parent::__construct($id, $type);
    }

    public function toArray(): array
    {
        return [
            '@type' => $this->getType(),
            '@id' => $this->getId(),
            'headline' => $this->headline,
            'url' => $this->url,
            'datePublished' => $this->datePublished,
            'dateModified' => $this->dateModified,
            'author' => $this->authorId ? ['@id' => $this->authorId] : null,
            'publisher' => $this->publisherId ? ['@id' => $this->publisherId] : null,
            'image' => $this->serializeValue($this->image),
        ];
    }
}