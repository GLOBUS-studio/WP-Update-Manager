<?php
/**
 * Base test case for Complete Updates Manager tests.
 *
 * Uses Brain\Monkey to stub WordPress functions. Plugin files are loaded
 * ONCE after Brain\Monkey is initialized so that function redefinitions work.
 */

use Brain\Monkey;
use Brain\Monkey\Functions;

abstract class WUM_TestCase extends \PHPUnit\Framework\TestCase {

    /** @var bool */
    private static $plugin_loaded = false;

    /**
     * Load plugin files once, after Brain\Monkey stubs are in place.
     */
    private static function load_plugin_files(): void {
        if (self::$plugin_loaded) {
            return;
        }

        require_once WUM_PLUGIN_DIR . 'includes/helpers.php';
        require_once WUM_PLUGIN_DIR . 'includes/class-updates-manager.php';
        require_once WUM_PLUGIN_DIR . 'includes/class-admin-interface.php';
        require_once WUM_PLUGIN_DIR . 'includes/class-settings.php';

        self::$plugin_loaded = true;
    }

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();

        // Register all WordPress function stubs via Brain\Monkey BEFORE loading plugin files.
        $this->register_wordpress_stubs();

        // Now load plugin files - Brain\Monkey's Patchwork can intercept all WP functions.
        self::load_plugin_files();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Register WordPress function stubs that helpers.php and classes need to parse.
     * These are default stubs; individual tests can override them with Functions\expect().
     */
    private function register_wordpress_stubs(): void {
        Functions\stubEscapeFunctions();
        Functions\stubTranslationFunctions();

        Functions\when('current_user_can')->justReturn(true);
        Functions\when('admin_url')->justReturn('https://example.com/wp-admin/');
        Functions\when('get_option')->justReturn([]);
        Functions\when('add_option')->justReturn(true);
        Functions\when('update_option')->justReturn(true);
        Functions\when('delete_option')->justReturn(true);
        Functions\when('delete_transient')->justReturn(true);
        Functions\when('delete_site_transient')->justReturn(true);
        Functions\when('get_transient')->justReturn(false);
        Functions\when('set_transient')->justReturn(true);
        Functions\when('get_site_transient')->justReturn(false);
        Functions\when('set_site_transient')->justReturn(true);
        Functions\when('wp_parse_args')->alias(function ($args, $defaults) {
            return array_merge((array) $defaults, (array) $args);
        });
        Functions\when('wp_verify_nonce')->justReturn(true);
        Functions\when('wp_nonce_field')->justReturn('');
        Functions\when('wp_nonce_url')->alias(function ($url, $action) { return $url; });
        Functions\when('sanitize_text_field')->alias(function ($str) { return strip_tags((string) $str); });
        Functions\when('sanitize_html_class')->alias(function ($class) { return preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $class); });
        Functions\when('sanitize_key')->alias(function ($key) { return preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $key); });
        Functions\when('wp_json_encode')->alias('json_encode');
        Functions\when('plugin_basename')->alias(function ($file) {
            return basename(dirname($file)) . '/' . basename($file);
        });
        Functions\when('trailingslashit')->alias(function ($string) {
            return rtrim((string) $string, '/\\') . '/';
        });
        Functions\when('add_action')->justReturn(true);
        Functions\when('add_filter')->justReturn(true);
        Functions\when('remove_action')->justReturn(true);
        Functions\when('remove_all_filters')->justReturn(true);
        Functions\when('wp_clear_scheduled_hook')->justReturn(true);
        Functions\when('wp_schedule_single_event')->justReturn(true);
        Functions\when('wp_get_themes')->justReturn([]);
        Functions\when('get_plugins')->justReturn([]);
        Functions\when('get_bloginfo')->justReturn('6.5');
        Functions\when('get_plugin_data')->justReturn(['Name' => 'Complete Updates Manager']);
        Functions\when('is_admin')->justReturn(true);
        Functions\when('load_plugin_textdomain')->justReturn(true);
        Functions\when('current_filter')->justReturn('');
        Functions\when('checked')->justReturn('');
        Functions\when('selected')->justReturn('');
        Functions\when('get_admin_page_title')->justReturn('Updates Manager');
    }
}
