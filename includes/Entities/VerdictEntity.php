<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class VerdictEntity
 * * Represents a Case Verdict, mapped as a CreativeWork for schema compliance.
 */
final class VerdictEntity extends Entity
{
    /**
     * @param string $id
     * @param string $title
     * @param string|null $description
     * @param string|null $amount
     */
    public function __construct(
        string $id,
        private readonly string $title,
        private readonly ?string $description = null,
        private readonly ?string $amount = null
    ) {
        // FIX: Changed from 'Thing' to 'CreativeWork' to satisfy publishingPrinciples target type
        parent::__construct($id, 'CreativeWork'); 
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_filter([
            '@type' => $this->getType(),
            '@id' => $this->getId(),
            // Outputting the amount and title clearly for the search engine
            'name' => $this->amount ? $this->amount . ' - ' . $this->title : $this->title,
            'headline' => $this->title,
            'description' => $this->description,
            'abstract' => $this->amount ? "Verdict Amount: {$this->amount}" : null,
        ]);
    }
}