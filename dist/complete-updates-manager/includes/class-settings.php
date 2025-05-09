<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Complete_Updates_Manager_Settings Class
 *
 * Handles settings page and functionality
 *
 * @since 1.0.0
 */
class Complete_Updates_Manager_Settings {

    /**
     * Default settings
     *
     * @since 1.0.0
     * @var array
     */
    private $default_settings = [
        'disable_core_updates' => 1,
        'disable_plugin_updates' => 1,
        'disable_theme_updates' => 1,
        'monitor_security_updates' => 0,
        'security_check_interval' => 'daily',
    ];

    /**
     * Initialize the settings functionality
     *
     * @since  1.0.0
     * @return void
     */
    public function initialize() {
        // Register settings page
        add_action('admin_menu', [$this, 'register_settings_menu']);
        
        // Register settings
        add_action('admin_init', [$this, 'register_settings']);
        
        // Check for security updates if enabled
        if ($this->is_security_monitoring_enabled()) {
            add_action('admin_init', [$this, 'check_security_updates']);
            add_action('admin_notices', [$this, 'display_security_notices']);
        }
    }

    /**
     * Register settings page under Settings menu
     *
     * @since  1.0.0
     * @return void
     */
    public function register_settings_menu() {
        add_options_page(
            __('Updates Manager Settings', 'complete-updates-manager'),
            __('Updates Manager', 'complete-updates-manager'),
            'manage_options',
            'complete-updates-manager',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Register plugin settings
     *
     * @since  1.0.0
     * @return void
     */
    public function register_settings() {
        register_setting(
            'wum_settings_group',
            'wum_settings',
            [$this, 'sanitize_settings']
        );

        // General Settings Section
        add_settings_section(
            'wum_general_section',
            __('Update Control Settings', 'complete-updates-manager'),
            [$this, 'render_general_section'],
            'complete-updates-manager'
        );

        // Core Updates Setting
        add_settings_field(
            'disable_core_updates',
            __('WordPress Core Updates', 'complete-updates-manager'),
            [$this, 'render_checkbox_field'],
            'complete-updates-manager',
            'wum_general_section',
            [
                'id' => 'disable_core_updates',
                'label' => __('Disable WordPress core updates', 'complete-updates-manager'),
                'description' => __('Prevents WordPress from checking for core updates', 'complete-updates-manager')
            ]
        );

        // Plugin Updates Setting
        add_settings_field(
            'disable_plugin_updates',
            __('Plugin Updates', 'complete-updates-manager'),
            [$this, 'render_checkbox_field'],
            'complete-updates-manager',
            'wum_general_section',
            [
                'id' => 'disable_plugin_updates',
                'label' => __('Disable plugin updates', 'complete-updates-manager'),
                'description' => __('Prevents WordPress from checking for plugin updates', 'complete-updates-manager')
            ]
        );

        // Theme Updates Setting
        add_settings_field(
            'disable_theme_updates',
            __('Theme Updates', 'complete-updates-manager'),
            [$this, 'render_checkbox_field'],
            'complete-updates-manager',
            'wum_general_section',
            [
                'id' => 'disable_theme_updates',
                'label' => __('Disable theme updates', 'complete-updates-manager'),
                'description' => __('Prevents WordPress from checking for theme updates', 'complete-updates-manager')
            ]
        );

        // Security Monitoring Section
        add_settings_section(
            'wum_security_section',
            __('Security Monitoring', 'complete-updates-manager'),
            [$this, 'render_security_section'],
            'complete-updates-manager'
        );

        // Security Monitoring Setting
        add_settings_field(
            'monitor_security_updates',
            __('Security Update Monitoring', 'complete-updates-manager'),
            [$this, 'render_checkbox_field'],
            'complete-updates-manager',
            'wum_security_section',
            [
                'id' => 'monitor_security_updates',
                'label' => __('Monitor for security updates', 'complete-updates-manager'),
                'description' => __('Even with updates disabled, check for and notify about critical security updates', 'complete-updates-manager')
            ]
        );

        // Security Check Interval Setting
        add_settings_field(
            'security_check_interval',
            __('Check Frequency', 'complete-updates-manager'),
            [$this, 'render_select_field'],
            'complete-updates-manager',
            'wum_security_section',
            [
                'id' => 'security_check_interval',
                'label' => __('How often to check for security updates', 'complete-updates-manager'),
                'options' => [
                    'daily' => __('Daily', 'complete-updates-manager'),
                    'weekly' => __('Weekly', 'complete-updates-manager'),
                ]
            ]
        );
    }

    /**
     * Sanitize settings before saving
     *
     * @param  array $input The settings input
     * @return array        Sanitized settings
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        
        // Sanitize checkboxes (0 or 1)
        $checkboxes = ['disable_core_updates', 'disable_plugin_updates', 'disable_theme_updates', 'monitor_security_updates'];
        foreach ($checkboxes as $checkbox) {
            $sanitized[$checkbox] = isset($input[$checkbox]) ? 1 : 0;
        }
        
        // Sanitize select fields
        if (isset($input['security_check_interval'])) {
            $sanitized['security_check_interval'] = sanitize_text_field($input['security_check_interval']);
        }
        
        return $sanitized;
    }

    /**
     * Render the general section description
     *
     * @since  1.0.0
     * @return void
     */
    public function render_general_section() {
        echo '<p>' . esc_html__('Configure which WordPress update functionality should be disabled.', 'complete-updates-manager') . '</p>';
    }

    /**
     * Render the security section description
     *
     * @since  1.0.0
     * @return void
     */
    public function render_security_section() {
        echo '<p>' . esc_html__('Configure security monitoring options while updates are disabled.', 'complete-updates-manager') . '</p>';
    }

    /**
     * Render checkbox field
     *
     * @param  array $args Arguments for the field
     * @return void
     */
    public function render_checkbox_field($args) {
        $settings = $this->get_settings();
        $id = $args['id'];
        $value = isset($settings[$id]) ? $settings[$id] : 0;
        
        echo '<label for="wum_' . esc_attr($id) . '">';
        echo '<input type="checkbox" id="wum_' . esc_attr($id) . '" name="wum_settings[' . esc_attr($id) . ']" value="1" ' . checked(1, $value, false) . '/>';
        echo esc_html($args['label']);
        echo '</label>';
        
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Render select field
     *
     * @param  array $args Arguments for the field
     * @return void
     */
    public function render_select_field($args) {
        $settings = $this->get_settings();
        $id = $args['id'];
        $value = isset($settings[$id]) ? $settings[$id] : '';
        
        echo '<select id="wum_' . esc_attr($id) . '" name="wum_settings[' . esc_attr($id) . ']">';
        foreach ($args['options'] as $option_value => $option_label) {
            echo '<option value="' . esc_attr($option_value) . '" ' . selected($option_value, $value, false) . '>' . esc_html($option_label) . '</option>';
        }
        echo '</select>';
        
        if (isset($args['label'])) {
            echo '<p class="description">' . esc_html($args['label']) . '</p>';
        }
    }

    /**
     * Render the settings page
     *
     * @since  1.0.0
     * @return void
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('wum_settings_group');
                do_settings_sections('complete-updates-manager');
                wp_nonce_field('wum_settings_action', 'wum_settings_nonce');
                submit_button(__('Save Settings', 'complete-updates-manager'));
                ?>
            </form>
            <div class="wum-info-box" style="margin-top: 20px; padding: 15px; background: #f8f8f8; border-left: 4px solid #dc3232;">
                <h3><?php esc_html_e('Important Security Notice', 'complete-updates-manager'); ?></h3>
                <p><?php esc_html_e('Disabling updates may expose your site to security vulnerabilities. Use this plugin with caution and check for important security updates regularly.', 'complete-updates-manager'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Get plugin settings
     *
     * @return array Plugin settings
     */
    public function get_settings() {
        $settings = get_option('wum_settings', $this->default_settings);
        return wp_parse_args($settings, $this->default_settings);
    }

    /**
     * Check if security monitoring is enabled
     *
     * @return boolean True if security monitoring is enabled
     */
    public function is_security_monitoring_enabled() {
        $settings = $this->get_settings();
        return (bool) $settings['monitor_security_updates'];
    }

    /**
     * Check for security updates
     *
     * @since  1.0.0
     * @return void
     */
    public function check_security_updates() {
        $settings = $this->get_settings();
        $interval = $settings['security_check_interval'] === 'weekly' ? WEEK_IN_SECONDS : DAY_IN_SECONDS;
        
        if (false === get_transient('wum_security_check')) {
            // Use WordPress core APIs to get security update information safely
            $this->fetch_security_updates();
            
            // Set transient to prevent frequent checks
            set_transient('wum_security_check', 1, $interval);
        }
    }

    /**
     * Fetch security updates from WordPress API
     *
     * @since  1.0.0
     * @return void
     */
    private function fetch_security_updates() {
        // Get WordPress version information
        include ABSPATH . WPINC . '/version.php';

        if (!isset($wp_version)) {
            
            global $wp_version;
            
            if (!isset($wp_version)) {
                return; 
            }
        }
        
        // Use a safe API endpoint to check for security updates
        $url = 'https://api.wordpress.org/core/stable-check/1.0/';
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return;
        }
        
        $body = wp_remote_retrieve_body($response);
        $releases = json_decode($body, true);
        
        if (!is_array($releases) || empty($releases)) {
            return;
        }
        
        $security_issues = [];
        
        // Check if current version has security issues
        if (isset($releases[$wp_version]) && $releases[$wp_version] === 'insecure') {
            $security_issues[] = [
                'type' => 'core',
                'message' => sprintf(
                    /* translators: %s: WordPress version */
                    __('Your WordPress version (%s) has known security issues.', 'complete-updates-manager'),
                    $wp_version
                )
            ];
        }
        
        // Also check for plugin security updates if possible
        if (function_exists('wp_get_update_data')) {
            $update_data = wp_get_update_data();
            if ($update_data['counts']['plugins'] > 0) {
                // Try to get more detailed plugin update info
                wp_update_plugins();
                $plugin_updates = get_site_transient('update_plugins');
                
                if (!empty($plugin_updates->response)) {
                    foreach ($plugin_updates->response as $plugin_file => $plugin_data) {
                        // Look for plugins with security updates
                        if (strpos($plugin_data->new_version, 'security') !== false || strpos($plugin_data->upgrade_notice, 'security') !== false) {
                            $plugin_info = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);
                            $security_issues[] = [
                                'type' => 'plugin',
                                'message' => sprintf(
                                    /* translators: %s: Plugin name */
                                    __('Security update available for plugin: %s', 'complete-updates-manager'),
                                    $plugin_info['Name']
                                )
                            ];
                        }
                    }
                }
            }
        }
        
        if (!empty($security_issues)) {
            update_option('wum_security_issues', $security_issues);
        } else {
            delete_option('wum_security_issues');
        }
    }

    /**
     * Display security update notices
     *
     * @since  1.0.0
     * @return void
     */
    public function display_security_notices() {
        $security_issues = get_option('wum_security_issues', []);
        
        if (empty($security_issues)) {
            return;
        }
        
        echo '<div class="notice notice-error is-dismissible">';
        echo '<h3>' . esc_html__('Important WordPress Security Updates', 'complete-updates-manager') . '</h3>';
        echo '<p>' . esc_html__('Even though updates are disabled, the following security issues were detected:', 'complete-updates-manager') . '</p>';
        echo '<ul>';
        
        foreach ($security_issues as $issue) {
            echo '<li>' . esc_html($issue['message']) . '</li>';
        }
        
        echo '</ul>';
        echo '<p>' . esc_html__('Consider temporarily enabling updates to apply these security fixes.', 'complete-updates-manager') . '</p>';
        echo '</div>';
    }
}