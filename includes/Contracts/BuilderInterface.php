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
     * Read data from WordPress and ACF for a post and construct its schema Entity representation.
     *
     * @param int $postId
     * @return EntityInterface|null
     */
    public function build(int $postId): ?EntityInterface;
}