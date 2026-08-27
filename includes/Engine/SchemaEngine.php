<?php

declare(strict_types=1);

namespace Ferraro\Schema\Engine;

use Ferraro\Schema\Contracts\BuilderInterface;
use Ferraro\Schema\Contracts\EntityInterface;
use Ferraro\Schema\Graph\Graph;
use Ferraro\Schema\Support\Cache;
use Ferraro\Schema\Support\Validator;

/**
 * Class SchemaEngine
 * * The execution core orchestrating registry mapping, Builders, graph creation, validation, and cache management.
 */
final class SchemaEngine
{
    /**
     * Registered Builders for structural processing.
     *
     * @var array<BuilderInterface>
     */
    private array $builders = [];

    /**
     * SchemaEngine constructor.
     *
     * @param Cache $cache
     * @param Validator $validator
     */
    public function __construct(
        private readonly Cache $cache,
        private readonly Validator $validator
    ) {
    }

    /**
     * Register a custom builder inside the engine pipelines.
     *
     * @param BuilderInterface $builder
     * @return void
     */
    public function registerBuilder(BuilderInterface $builder): void
    {
        $this->builders[] = $builder;
    }

    /**
     * Build and sanitize the graph for a specific post.
     *
     * @param int $postId
     * @return array<string, mixed>|null
     */
    public function generateSchema(int $postId): ?array
    {
        $cacheKey = (string) $postId;
        $cachedSchema = $this->cache->get($cacheKey);

        if ($cachedSchema !== null) {
            return $cachedSchema;
        }

        $graph = new Graph();
        $processed = false;

        foreach ($this->builders as $builder) {
            if ($builder->supports($postId)) {
                $result = $builder->build($postId);
                
                if ($result !== null) {
                    if (is_array($result)) {
                        foreach ($result as $entity) {
                            if ($entity instanceof EntityInterface) {
                                $graph->add($entity);
                            }
                        }
                    } elseif ($result instanceof EntityInterface) {
                        $graph->add($result);
                    }
                    $processed = true;
                }
            }
        }

        if (!$processed) {
            return null;
        }

        $schemaArray = $graph->toArray();
        $cleanSchemaArray = $this->validator->validateAndClean($schemaArray);

        if (empty($cleanSchemaArray['@graph'])) {
            return null;
        }

        $this->cache->set($cacheKey, $cleanSchemaArray);

        return $cleanSchemaArray;
    }
}