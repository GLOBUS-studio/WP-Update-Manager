<?php
/**
 * Tests for helper functions (helpers.php).
 */

use Brain\Monkey\Functions;

/**
 * Class Test_Helpers
 */
class Test_Helpers extends WUM_TestCase {

    public function test_wum_get_settings_url_returns_escaped_url() {
        Functions\when('admin_url')->justReturn('https://example.com/wp-admin/options-general.php?page=complete-updates-manager');

        $result = wum_get_settings_url();
        $this->assertStringContainsString('options-general.php?page=complete-updates-manager', $result);
    }

    public function test_wum_get_version_returns_wum_version_constant() {
        $this->assertSame('1.0.2-test', wum_get_version());
    }

    public function test_wum_is_update_disabled_returns_false_for_invalid_type() {
        $this->assertFalse(wum_is_update_disabled('invalid_type'));
    }

    public function test_wum_is_update_disabled_returns_false_when_settings_empty() {
        Functions\when('get_option')->justReturn(false);

        $this->assertFalse(wum_is_update_disabled('core'));
    }

    public function test_wum_is_update_disabled_returns_true_when_core_disabled() {
        Functions\when('get_option')->justReturn(['disable_core_updates' => 1]);

        $this->assertTrue(wum_is_update_disabled('core'));
    }

    public function test_wum_is_security_monitoring_enabled_returns_false_by_default() {
        Functions\when('get_option')->justReturn(false);

        $this->assertFalse(wum_is_security_monitoring_enabled());
    }

    public function test_wum_is_security_monitoring_enabled_returns_true_when_enabled() {
        Functions\when('get_option')->justReturn(['monitor_security_updates' => 1]);

        $this->assertTrue(wum_is_security_monitoring_enabled());
    }

    public function test_wum_verify_nonce_returns_false_for_empty_nonce() {
        $this->assertFalse(wum_verify_nonce('', 'action'));
    }

    public function test_wum_verify_nonce_returns_false_for_empty_action() {
        $this->assertFalse(wum_verify_nonce('nonce', ''));
    }

    public function test_wum_verify_nonce_calls_wordpress_function() {
        Functions\when('wp_verify_nonce')->justReturn(true);

        $this->assertTrue(wum_verify_nonce('valid_nonce', 'valid_action'));
    }

    public function test_wum_get_frozen_version_returns_null_when_no_data() {
        Functions\when('get_option')->justReturn([]);

        $this->assertNull(wum_get_frozen_version('core'));
        $this->assertNull(wum_get_frozen_version('plugin', 'test/plugin.php'));
        $this->assertNull(wum_get_frozen_version('theme', 'twentytwenty'));
    }

    public function test_wum_get_frozen_version_returns_core_version() {
        Functions\when('get_option')->justReturn(['core' => '6.5.0']);

        $this->assertSame('6.5.0', wum_get_frozen_version('core'));
    }

    public function test_wum_get_frozen_version_returns_plugin_version() {
        Functions\when('get_option')->justReturn(['plugin' => ['test/plugin.php' => '2.0.0']]);

        $this->assertSame('2.0.0', wum_get_frozen_version('plugin', 'test/plugin.php'));
    }

    public function test_wum_get_frozen_version_returns_theme_version() {
        Functions\when('get_option')->justReturn(['theme' => ['twentytwenty' => '1.5']]);

        $this->assertSame('1.5', wum_get_frozen_version('theme', 'twentytwenty'));
    }

    public function test_wum_is_update_allowed_returns_false_when_version_greater_than_frozen() {
        Functions\when('get_option')->justReturn(['core' => '6.5.0']);

        $this->assertFalse(wum_is_update_allowed('core', '', '6.5.1'));
    }

    public function test_wum_is_update_allowed_returns_true_when_version_equal_to_frozen() {
        Functions\when('get_option')->justReturn(['core' => '6.5.0']);

        $this->assertTrue(wum_is_update_allowed('core', '', '6.5.0'));
    }

    public function test_wum_is_update_allowed_returns_true_when_no_frozen_version() {
        Functions\when('get_option')->justReturn([]);

        $this->assertTrue(wum_is_update_allowed('core', '', '6.6.0'));
    }

    public function test_wum_validate_args_returns_false_for_non_array() {
        $this->assertFalse(wum_validate_args('not_an_array'));
        $this->assertFalse(wum_validate_args(null));
        $this->assertFalse(wum_validate_args(123));
    }

    public function test_wum_validate_args_returns_false_when_arg_is_empty() {
        $this->assertFalse(wum_validate_args(['foo', '', 'bar']));
    }

    public function test_wum_validate_args_returns_true_for_valid_args() {
        $this->assertTrue(wum_validate_args(['foo', 'bar', 'baz']));
        $this->assertTrue(wum_validate_args([]));
    }
}
