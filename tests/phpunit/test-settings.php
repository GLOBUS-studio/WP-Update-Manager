<?php
/**
 * Tests for Complete_Updates_Manager_Settings class.
 */

use Brain\Monkey\Functions;

/**
 * Class Test_Settings
 */
class Test_Settings extends WUM_TestCase {

    /** @var Complete_Updates_Manager_Settings */
    private $settings;

    protected function setUp(): void {
        parent::setUp();

        // Override settings default
        Functions\when('get_option')->alias(function ($name, $default = []) {
            $values = [
                'wum_settings' => [
                    'disable_core_updates'        => 1,
                    'disable_plugin_updates'      => 1,
                    'disable_theme_updates'       => 1,
                    'monitor_security_updates'    => 1,
                    'security_check_interval'     => 'daily',
                    'disable_plugins_api_filter'  => 0,
                ],
            ];
            return isset($values[$name]) ? $values[$name] : $default;
        });

        $this->settings = new Complete_Updates_Manager_Settings();
    }

    public function test_get_settings_returns_array() {
        $result = $this->settings->get_settings();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('disable_core_updates', $result);
    }

    public function test_get_settings_includes_default_disable_plugins_api_filter() {
        $result = $this->settings->get_settings();
        $this->assertArrayHasKey('disable_plugins_api_filter', $result);
        $this->assertSame(0, $result['disable_plugins_api_filter']);
    }

    public function test_get_settings_returns_all_expected_keys() {
        $result = $this->settings->get_settings();
        $expected = [
            'disable_core_updates',
            'disable_plugin_updates',
            'disable_theme_updates',
            'monitor_security_updates',
            'security_check_interval',
            'disable_plugins_api_filter',
        ];
        foreach ($expected as $key) {
            $this->assertArrayHasKey($key, $result, "Missing key: $key");
        }
    }

    public function test_is_security_monitoring_enabled() {
        $this->assertTrue($this->settings->is_security_monitoring_enabled());
    }

    public function test_sanitize_settings_sets_checkboxes_to_1_or_0() {
        // Keys present -> 1; keys absent -> 0.
        $input = [
            'disable_core_updates'   => '1',
            'disable_theme_updates'  => 'foo',
            // disable_plugin_updates intentionally omitted to test 0 case
        ];

        $result = $this->settings->sanitize_settings($input);

        $this->assertSame(1, $result['disable_core_updates']);
        $this->assertSame(0, $result['disable_plugin_updates']);
        $this->assertSame(1, $result['disable_theme_updates']); // isset('foo') is true -> 1
    }

    public function test_sanitize_settings_sanitizes_select_values() {
        $input = [
            'security_check_interval' => '<script>alert(1)</script>weekly',
        ];

        $result = $this->settings->sanitize_settings($input);

        $this->assertStringNotContainsString('<script>', $result['security_check_interval']);
    }

    public function test_sanitize_settings_handles_empty_input() {
        $result = $this->settings->sanitize_settings([]);

        $expected_keys = ['disable_core_updates', 'disable_plugin_updates', 'disable_theme_updates', 'monitor_security_updates', 'disable_plugins_api_filter'];
        foreach ($expected_keys as $key) {
            $this->assertArrayHasKey($key, $result);
            $this->assertSame(0, $result[$key], "Key $key should be 0 for empty input");
        }
    }

    public function test_security_tab_content_is_rendered() {
        ob_start();
        $this->settings->render_security_section();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
    }

    public function test_render_settings_tabs_outputs_nav_tabs() {
        ob_start();
        $this->settings->render_settings_tabs('general');
        $output = ob_get_clean();

        $this->assertStringContainsString('nav-tab-wrapper', $output);
        $this->assertStringContainsString('Version Freeze', $output);
    }
}
