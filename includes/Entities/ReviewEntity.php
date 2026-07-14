<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class ReviewEntity
 * * Represents client testimonials, mapped to Schema.org 'Review'.
 */
final class ReviewEntity extends Entity
{
    /**
     * @param string $id
     * @param string $authorName Name of the reviewer
     * @param string $reviewBody Content of the testimonial
     * @param float|null $ratingValue Numerical rating value (e.g., 5.0)
     * @param OrganizationEntity|null $itemReviewed Entity being reviewed
     */
    public function __construct(
        string $id,
        private readonly string $authorName,
        private readonly string $reviewBody,
        private readonly ?float $ratingValue = 5.0,
        private readonly ?OrganizationEntity $itemReviewed = null
    ) {
        parent::__construct($id, 'Review');
    }

    /**
     * @inheritDoc
     */
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
            'itemReviewed' => $this->serializeValue($this->itemReviewed),
        ];
    }
}