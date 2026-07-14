<?php

declare(strict_types=1);

namespace Ferraro\Schema\Core;

use Ferraro\Schema\Builders\AttorneyBuilder;
use Ferraro\Schema\Engine\SchemaEngine;
use Ferraro\Schema\Graph\EntityRegistry;
use Ferraro\Schema\Output\JsonLdGenerator;
use Ferraro\Schema\Output\OutputManager;
use Ferraro\Schema\Parser\HtmlParser;
use Ferraro\Schema\Parser\RelationshipParser;
use Ferraro\Schema\Support\Cache;
use Ferraro\Schema\Support\Validator;

/**
 * Class Plugin
 * * The initialization orchestrator for the Ferraro Structured Data Engine.
 */
final class Plugin
{
    /**
     * Boot the plugin and initialize dependencies.
     * Called directly from ferraro-schema.php.
     *
     * @return void
     */
    public static function boot(): void
    {
        $plugin = new self();
        $plugin->init();
    }

    /**
     * Wire up dependencies and register hooks.
     *
     * @return void
     */
    private function init(): void
    {
        // 1. Initialize Support Services
        $cache = new Cache();
        $validator = new Validator();
        $registry = new EntityRegistry();
        $htmlParser = new HtmlParser();
        $relationParser = new RelationshipParser();

        // 2. Initialize Core Engine
        $schemaEngine = new SchemaEngine($cache, $validator);

        // 3. Register Builders (Sprint 5 Integration)
        $attorneyBuilder = new AttorneyBuilder($registry, $htmlParser, $relationParser);
        $schemaEngine->registerBuilder($attorneyBuilder);

        // 4. Initialize Output Layer
        $jsonLdGenerator = new JsonLdGenerator();
        $outputManager = new OutputManager($schemaEngine, $jsonLdGenerator);

        // 5. Hook into WordPress
        $outputManager->registerHooks();
    }
}