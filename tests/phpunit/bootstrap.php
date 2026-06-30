<?php
/**
 * PHPUnit bootstrap for Complete Updates Manager tests.
 *
 * Sets up WordPress function/constant stubs via Brain\Monkey before loading any plugin code.
 */

// Define WordPress constants that the plugin expects.
define('ABSPATH', dirname(__DIR__, 2) . '/.wordpress/');
define('WPINC', 'wp-includes');
define('WP_PLUGIN_DIR', dirname(__DIR__, 2) . '/.wordpress/wp-content/plugins');
define('WP_DEBUG', false);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);
define('DAY_IN_SECONDS', 86400);
define('WEEK_IN_SECONDS', 604800);
define('WP_UNINSTALL_PLUGIN', true);

// Define plugin constants (skip ABSPATH check).
define('WUM_VERSION', '1.0.2-test');
define('WUM_PLUGIN_DIR', dirname(__DIR__, 2) . '/dist/complete-updates-manager/');
define('WUM_PLUGIN_URL', 'https://example.com/wp-content/plugins/complete-updates-manager/');
define('WUM_PLUGIN_BASENAME', 'complete-updates-manager/complete-updates-manager.php');
define('WUM_MIN_PHP_VERSION', '7.4');
define('WUM_MIN_WP_VERSION', '3.8');

// Load Composer autoloader first.
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
