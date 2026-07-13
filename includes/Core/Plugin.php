<?php

declare(strict_types=1);

namespace Ferraro\Schema\Core;

final class Plugin
{
    public static function boot(): void
    {
        $plugin = new self();

        $plugin->registerHooks();
    }

    private function registerHooks(): void
    {
        add_action('init', [$this, 'init']);
    }

    public function init(): void
    {
        // Future initialization.
    }
}