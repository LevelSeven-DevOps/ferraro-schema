<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class ArticleEntity
 * * Represents a Blog Post or Press Release, mapped to Schema.org 'BlogPosting' or 'NewsArticle'.
 */
final class ArticleEntity extends Entity
{
    /**
     * @param string $id
     * @param string $type Schema.org type (e.g. 'BlogPosting', 'NewsArticle')
     * @param string $headline
     * @param string $url
     * @param string|null $datePublished
     * @param ImageEntity|null $image
     * @param PersonEntity|OrganizationEntity|null $author
     */
    public function __construct(
        string $id,
        string $type,
        private readonly string $headline,
        private readonly string $url,
        private readonly ?string $datePublished = null,
        private readonly ?ImageEntity $image = null,
        private readonly PersonEntity|OrganizationEntity|null $author = null
    ) {
        parent::__construct($id, $type);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            '@type' => $this->getType(),
            '@id' => $this->getId(),
            'headline' => $this->headline,
            'url' => $this->url,
            'datePublished' => $this->datePublished,
            'image' => $this->serializeValue($this->image),
            'author' => $this->serializeValue($this->author),
        ];
    }
}