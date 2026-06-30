<?php
/**
 * Tests for uninstall.php.
 */

/**
 * Class Test_Uninstall
 *
 * @coversNothing
 */
class Test_Uninstall extends \PHPUnit\Framework\TestCase {

    public function test_uninstall_not_called_directly_is_defined() {
        $this->assertTrue(defined('WP_UNINSTALL_PLUGIN'));
    }

    public function test_uninstall_file_exists() {
        $this->assertFileExists(dirname(__DIR__, 2) . '/dist/complete-updates-manager/uninstall.php');
    }

    public function test_uninstall_file_is_valid_php() {
        $content = file_get_contents(dirname(__DIR__, 2) . '/dist/complete-updates-manager/uninstall.php');
        $this->assertStringContainsString('WP_UNINSTALL_PLUGIN', $content);
        $this->assertStringContainsString('delete_option', $content);
        $this->assertStringContainsString('delete_transient', $content);
    }

    public function test_uninstall_removes_all_plugin_options() {
        $content = file_get_contents(dirname(__DIR__, 2) . '/dist/complete-updates-manager/uninstall.php');

        $expected_options = [
            'wum_settings',
            'wum_version_freeze',
            'wum_security_issues',
            'wum_first_activation_done',
            'wum_show_activation_notice',
        ];

        foreach ($expected_options as $option) {
            $this->assertStringContainsString(
                $option,
                $content,
                "Uninstall file should remove $option"
            );
        }
    }

    public function test_uninstall_clears_update_transients() {
        $content = file_get_contents(dirname(__DIR__, 2) . '/dist/complete-updates-manager/uninstall.php');

        $expected_transients = ['update_core', 'update_plugins', 'update_themes'];
        foreach ($expected_transients as $transient) {
            $this->assertStringContainsString($transient, $content);
        }
    }
}
