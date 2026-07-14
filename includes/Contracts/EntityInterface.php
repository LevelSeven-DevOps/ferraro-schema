<?php

declare(strict_types=1);

namespace Ferraro\Schema\Contracts;

/**
 * Interface EntityInterface
 * * Defines the contract for all Schema.org entities within the FSDE knowledge graph.
 */
interface EntityInterface
{
    /**
     * Get the canonical @id of the schema entity.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Get the Schema.org type (e.g., 'Person', 'Attorney', 'Organization').
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Convert the entity into an associative array representation suitable for JSON-LD serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}