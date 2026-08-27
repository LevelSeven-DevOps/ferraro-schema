<?php

declare(strict_types=1);

namespace Ferraro\Schema\Contracts;

/**
 * Interface BuilderInterface
 * * Defines the contract for all post-type schema builders.
 */
interface BuilderInterface
{
    /**
     * Determine if this builder supports the given post ID.
     *
     * @param int $postId
     * @return bool
     */
    public function supports(int $postId): bool;

    /**
     * Read data from WordPress and ACF for a post and construct its schema Entity representation(s).
     *
     * @param int $postId
     * @return array<EntityInterface>|EntityInterface|null
     */
    public function build(int $postId): array|EntityInterface|null;
}