<?php
/**
 * PHPStan bootstrap - defines plugin constants for static analysis.
 */

if (!defined('ABSPATH')) {
    define('ABSPATH', '/var/www/html/');
}
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}
if (!defined('WUM_VERSION')) {
    define('WUM_VERSION', '1.0.2');
}
if (!defined('WUM_PLUGIN_DIR')) {
    define('WUM_PLUGIN_DIR', '/var/www/html/wp-content/plugins/complete-updates-manager/');
}
if (!defined('WUM_PLUGIN_URL')) {
    define('WUM_PLUGIN_URL', 'https://example.com/wp-content/plugins/complete-updates-manager/');
}
if (!defined('WUM_PLUGIN_BASENAME')) {
    define('WUM_PLUGIN_BASENAME', 'complete-updates-manager/complete-updates-manager.php');
}
if (!defined('WUM_MIN_PHP_VERSION')) {
    define('WUM_MIN_PHP_VERSION', '7.4');
}
if (!defined('WUM_MIN_WP_VERSION')) {
    define('WUM_MIN_WP_VERSION', '3.8');
}
