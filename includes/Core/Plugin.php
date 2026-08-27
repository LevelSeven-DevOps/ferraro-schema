<?php

declare(strict_types=1);

namespace Ferraro\Schema\Core;

use Ferraro\Schema\Builders\AttorneyBuilder;
use Ferraro\Schema\Builders\PostBuilder;
use Ferraro\Schema\Builders\PracticeAreaBuilder;
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
    public static function boot(): void
    {
        $plugin = new self();
        $plugin->init();
    }

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

        // 3. Register All Active Builders
        $schemaEngine->registerBuilder(new AttorneyBuilder($registry, $htmlParser, $relationParser));
        $schemaEngine->registerBuilder(new PracticeAreaBuilder($registry, $htmlParser));
        $schemaEngine->registerBuilder(new PostBuilder($registry));

        // 4. Initialize Output Layer
        $jsonLdGenerator = new JsonLdGenerator();
        $outputManager = new OutputManager($schemaEngine, $jsonLdGenerator);

        // 5. Hook into WordPress
        $outputManager->registerHooks();
    }
}