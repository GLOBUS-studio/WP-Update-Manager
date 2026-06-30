<?php
/**
 * Tests for Complete_Updates_Manager class.
 */

use Brain\Monkey\Functions;

/**
 * Class Test_Updates_Manager
 */
class Test_Updates_Manager extends WUM_TestCase {

    /** @var Complete_Updates_Manager */
    private $manager;

    protected function setUp(): void {
        parent::setUp();

        $this->manager = new Complete_Updates_Manager();
    }

    public function test_class_exists() {
        $this->assertInstanceOf(Complete_Updates_Manager::class, $this->manager);
    }

    public function test_block_update_requests_blocks_api_wordpress_org() {
        $this->manager->initialize();

        $url = 'https://api.wordpress.org/core/version-check/1.7/';
        $result = $this->manager->block_update_requests(false, [], $url);
        $this->assertTrue($result);
    }

    public function test_block_update_requests_does_not_block_unrelated_urls() {
        $this->manager->initialize();

        $url = 'https://example.com/some-api/';
        $result = $this->manager->block_update_requests(false, [], $url);
        $this->assertFalse($result);
    }

    public function test_block_update_requests_returns_pre_for_empty_url() {
        $this->manager->initialize();

        $result = $this->manager->block_update_requests('default_pre', [], '');
        $this->assertSame('default_pre', $result);
    }

    public function test_filter_cron_events_removes_update_events() {
        $this->manager->initialize();

        $event = (object) ['hook' => 'wp_version_check'];
        $result = $this->manager->filter_cron_events($event);
        $this->assertFalse($result);
    }

    public function test_filter_cron_events_removes_auto_update_event() {
        $this->manager->initialize();

        $auto_event = (object) ['hook' => 'wp_maybe_auto_update'];
        $result = $this->manager->filter_cron_events($auto_event);
        $this->assertFalse($result);
    }

    public function test_remove_update_health_checks_removes_update_tests() {
        $tests = [
            'async' => [
                'background_updates' => 'test',
                'other_test'         => 'keep_me',
            ],
            'direct' => [
                'plugin_theme_auto_updates' => 'test',
                'other_test'                => 'keep_me',
            ],
        ];

        $result = $this->manager->remove_update_health_checks($tests);

        $this->assertArrayNotHasKey('background_updates', $result['async']);
        $this->assertArrayNotHasKey('plugin_theme_auto_updates', $result['direct']);
        $this->assertArrayHasKey('other_test', $result['async']);
        $this->assertArrayHasKey('other_test', $result['direct']);
    }

    public function test_override_version_check_returns_empty_for_disabled_core_updates() {
        Functions\when('current_filter')->justReturn('pre_transient_update_core');

        $this->manager->initialize();

        $transient = (object) ['updates' => []];

        $result = $this->manager->override_version_check($transient);

        $this->assertInstanceOf('stdClass', $result);
        $this->assertTrue(property_exists($result, 'last_checked'));
        $this->assertSame('latest', $result->response);
    }

    public function test_override_version_check_with_frozen_core_version() {
        Functions\when('current_filter')->justReturn('pre_transient_update_core');
        Functions\when('wum_get_frozen_version')->alias(function ($type, $slug = '') {
            return $type === 'core' ? '6.5.0' : null;
        });

        $this->manager->initialize();

        $transient = (object) [
            'updates' => [
                (object) ['version' => '6.6.0'],
                (object) ['version' => '6.5.1'],
                (object) ['version' => '6.5.0'],
            ],
        ];

        $result = $this->manager->override_version_check($transient);

        // After freeze filtering, remaining updates + then return empty due to disable_core_updates=1.
        // Actually: since core updates are disabled, after freeze the empty transient is returned.
        $this->assertInstanceOf('stdClass', $result);
        $this->assertSame('latest', $result->response);
    }
}
