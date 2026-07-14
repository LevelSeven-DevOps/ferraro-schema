<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class VerdictEntity
 * * Represents case results, verdicts, and settlements mapped as a specialized Schema.org 'Thing' context.
 */
final class VerdictEntity extends Entity
{
    /**
     * @param string $id
     * @param string $title Case name or summary (e.g. "$10M Mesothelioma Verdict")
     * @param string|null $description Case narrative
     * @param string|null $amount Amount recovered (to map custom structured telemetry)
     */
    public function __construct(
        string $id,
        private readonly string $title,
        private readonly ?string $description = null,
        private readonly ?string $amount = null
    ) {
        parent::__construct($id, 'Thing');
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            '@type' => $this->getType(),
            '@id' => $this->getId(),
            'name' => $this->title,
            'description' => $this->description,
            'value' => $this->amount, // Custom key representation optimized for internal search feeds
        ];
    }
}