<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

use Ferraro\Schema\Contracts\EntityInterface;

/**
 * Class Entity
 * * Abstract base class for all Schema.org entities. Handles ID, Context, and Base serialization.
 */
abstract class Entity implements EntityInterface
{
    /**
     * Entity constructor.
     *
     * @param string $id   The canonical URI identifying this object.
     * @param string $type The Schema.org type definition.
     */
    public function __construct(
        protected readonly string $id,
        protected readonly string $type
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Converts common entity objects or sub-entities recursively.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function serializeValue(mixed $value): mixed
    {
        if ($value instanceof EntityInterface) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return array_map([$this, 'serializeValue'], $value);
        }

        return $value;
    }
}