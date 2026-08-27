<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class ReviewEntity
 * * Represents client testimonials, mapped to Schema.org 'Review'.
 */
final class ReviewEntity extends Entity
{
    public function __construct(
        string $id,
        private readonly string $authorName,
        private readonly string $reviewBody,
        private readonly ?float $ratingValue = 5.0,
        private readonly ?string $itemReviewedId = null
    ) {
        parent::__construct($id, 'Review');
    }

    public function toArray(): array
    {
        return [
            '@type' => $this->getType(),
            '@id' => $this->getId(),
            'author' => [
                '@type' => 'Person',
                'name' => $this->authorName,
            ],
            'reviewBody' => $this->reviewBody,
            'reviewRating' => $this->ratingValue ? [
                '@type' => 'Rating',
                'ratingValue' => $this->ratingValue,
                'bestRating' => 5.0,
                'worstRating' => 1.0,
            ] : null,
            'itemReviewed' => $this->itemReviewedId ? ['@id' => $this->itemReviewedId] : null,
        ];
    }
}