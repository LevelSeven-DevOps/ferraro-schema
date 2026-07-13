<?php
/**
 * Plugin Name: Ferraro Structured Data Engine
 * Plugin URI: https://github.com/ferrarolaw/ferraro-schema
 * Description: Enterprise Structured Data Engine for Ferraro Law.
 * Version: 0.1.0
 * Requires at least: 6.8
 * Requires PHP: 8.1
 * Author: Ferraro Law
 * License: GPL-2.0-or-later
 * Text Domain: ferraro-schema
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

/*
|--------------------------------------------------------------------------
| Plugin Constants
|--------------------------------------------------------------------------
*/

define('FSDE_VERSION', '0.1.0');
define('FSDE_PLUGIN_FILE', __FILE__);
define('FSDE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FSDE_PLUGIN_URL', plugin_dir_url(__FILE__));

/*
|--------------------------------------------------------------------------
| Composer Autoloader
|--------------------------------------------------------------------------
*/

$autoload = FSDE_PLUGIN_PATH . 'vendor/autoload.php';

if (file_exists($autoload)) {
    require_once $autoload;
} else {
    add_action('admin_notices', static function (): void {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('Ferraro Structured Data Engine requires Composer dependencies. Please run "composer install".', 'ferraro-schema');
        echo '</p></div>';
    });

    return;
}

/*
|--------------------------------------------------------------------------
| Bootstrap Plugin
|--------------------------------------------------------------------------
*/

Ferraro\Schema\Core\Plugin::boot();