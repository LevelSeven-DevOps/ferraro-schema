<?php

declare(strict_types=1);

namespace Ferraro\Schema\Output;

use Ferraro\Schema\Engine\SchemaEngine;
use Ferraro\Schema\Support\Cache;

/**
 * Class OutputManager
 * * Bridges the WordPress ecosystem with the FSDE Engine by registering hooks and managing output.
 */
final class OutputManager
{
    public function __construct(
        private readonly SchemaEngine $engine,
        private readonly JsonLdGenerator $generator
    ) {
    }

    /**
     * Register WordPress hooks for outputting schema and clearing cache on save.
     *
     * @return void
     */
    public function registerHooks(): void
    {
        add_action('wp_head', [$this, 'renderSchema'], 99);
        add_action('save_post', [$this, 'flushCacheOnSave'], 10, 1);
    }

    /**
     * Flushes transient cache when a post is updated.
     *
     * @param int $postId
     * @return void
     */
    public function flushCacheOnSave(int $postId): void
    {
        if (wp_is_post_revision($postId) || wp_is_post_autosave($postId)) {
            return;
        }

        (new Cache())->delete((string) $postId);
    }

    /**
     * Determine if schema should be rendered, fetch it, and output it.
     *
     * @return void
     */
    public function renderSchema(): void
    {
        if (!is_singular()) {
            return;
        }

        $postId = get_the_ID();
        if (!$postId) {
            return;
        }

        // Fetch schema from the engine (which handles caching and validation)
        $schemaData = $this->engine->generateSchema((int) $postId);

        if ($schemaData === null) {
            return;
        }

        // We explicitly echo here because this is the designated Output layer.
        echo $this->generator->generate($schemaData);
    }
}