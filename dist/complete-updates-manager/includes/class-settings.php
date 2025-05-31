<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Complete_Updates_Manager_Settings Class
 *
 * Handles settings page and functionality
 *
 * @since 1.0.1
 */
class Complete_Updates_Manager_Settings {

    /**
     * Default settings
     *
     * @since 1.0.1
     * @var array
     */
    private $default_settings = [
        'disable_core_updates' => 1,
        'disable_plugin_updates' => 1,
        'disable_theme_updates' => 1,
        'monitor_security_updates' => 0,
        'security_check_interval' => 'daily',
        'disable_plugins_api_filter' => 0, // New setting
    ];

    /**
     * Initialize the settings functionality
     *
     * @since  1.0.1
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
     * @since  1.0.1
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
     * @since  1.0.1
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

        // Disable plugins_api filter Setting
        add_settings_field(
            'disable_plugins_api_filter',
            __('Completely Disable plugins_api Filter', 'complete-updates-manager'),
            [$this, 'render_checkbox_field'],
            'complete-updates-manager',
            'wum_general_section',
            [
                'id' => 'disable_plugins_api_filter',
                'label' => __('Disable plugins_api filter', 'complete-updates-manager'),
                'description' => __('Warning: Enabling this option will call remove_all_filters(\'plugins_api\'). This can prevent all plugin update checks but may also interfere with other plugins that use this filter for legitimate purposes (e.g., modifying plugin information screens). Use with caution.', 'complete-updates-manager')
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
        $checkboxes = ['disable_core_updates', 'disable_plugin_updates', 'disable_theme_updates', 'monitor_security_updates', 'disable_plugins_api_filter'];
        foreach ($checkboxes as $checkbox) {
            $sanitized[$checkbox] = isset($input[$checkbox]) ? 1 : 0;
        }
        
        // Sanitize select fields
        if (isset($input['security_check_interval'])) {
            $sanitized['security_check_interval'] = sanitize_text_field($input['security_check_interval']);
        }
        
        return $sanitized;
    }    /**
     * Render the general section description
     *
     * @since  1.0.1
     * @return void
     */
    public function render_general_section() {
        echo '<div class="notice notice-warning inline" style="margin: 10px 0; padding: 10px;">';
        echo '<p><strong>' . esc_html__('Security Warning:', 'complete-updates-manager') . '</strong> ';
        echo esc_html__('Disabling updates may leave your site vulnerable to security threats. Use with caution and enable security monitoring below.', 'complete-updates-manager') . '</p>';
        echo '</div>';
        echo '<p>' . esc_html__('Configure which WordPress update functionality should be disabled.', 'complete-updates-manager') . '</p>';
    }

    /**
     * Render the security section description
     *
     * @since  1.0.1
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
     * Render the Version Freeze tab
     *
     * @since 1.1.0
     * @return void
     */
    public function render_version_freeze_tab() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $frozen = get_option('wum_version_freeze', []);
        $plugins = get_plugins();
        $themes = wp_get_themes();
        $core_version = get_bloginfo('version');
        if (!class_exists('Complete_Updates_Manager_Admin')) {
            require_once WUM_PLUGIN_DIR . 'includes/class-admin-interface.php';
        }
        $admin = new Complete_Updates_Manager_Admin();
        ?>
        <h2><?php esc_html_e('Version Freeze', 'complete-updates-manager'); ?></h2>
        <form method="post">
            <?php wp_nonce_field('wum_version_freeze_action', 'wum_version_freeze_nonce'); ?>
            <table class="widefat fixed" style="max-width:900px;">
                <thead>
                <tr>
                    <th><?php esc_html_e('Component', 'complete-updates-manager'); ?></th>
                    <th><?php esc_html_e('Current Version', 'complete-updates-manager'); ?></th>
                    <th></th>
                    <th><?php esc_html_e('Freeze at Version', 'complete-updates-manager'); ?></th>
                    <th><?php esc_html_e('Status', 'complete-updates-manager'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><strong><?php esc_html_e('WordPress Core', 'complete-updates-manager'); ?></strong></td>
                    <td><?php echo esc_html($core_version); ?></td>
                    <td style="text-align:center;">
                        <button type="button" class="button wum-copy-version" data-version="<?php echo esc_attr($core_version); ?>" data-target="#wum_freeze_core" title="<?php esc_attr_e('Copy current version', 'complete-updates-manager'); ?>">&#8594;</button>
                    </td>                    <td>
                        <?php
                        $frozen_core = isset($frozen['core']) ? $frozen['core'] : '';
                        $admin->render_freeze_version_field('core', '', $core_version, $frozen_core, false);
                        ?>
                    </td>                    <td>
                        <?php
                        if (!empty($frozen_core)) {
                            echo '<span style="color:#dc3232;font-weight:bold;">' . esc_html__('Freeze is active', 'complete-updates-manager') . '</span>';
                            echo ' <a href="#" class="wum-unfreeze-version" data-target="#wum_freeze_core" style="color:#dc3232;text-decoration:none;" title="' . esc_attr__('Cancel freeze', 'complete-updates-manager') . '">(' . esc_html__('cancel', 'complete-updates-manager') . ')</a>';
                        } else {
                            echo '<span style="color:#999;">' . esc_html__('Not frozen', 'complete-updates-manager') . '</span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php foreach ($plugins as $file => $data): 
                    $safe_slug = sanitize_html_class($file);
                ?>
                <tr>
                    <td><?php echo esc_html($data['Name']); ?></td>
                    <td><?php echo esc_html($data['Version']); ?></td>
                    <td style="text-align:center;">
                        <button type="button" class="button wum-copy-version" data-version="<?php echo esc_attr($data['Version']); ?>" data-target="#wum_freeze_<?php echo esc_attr($safe_slug); ?>" title="<?php esc_attr_e('Copy current version', 'complete-updates-manager'); ?>">&#8594;</button>
                    </td>                    <td>
                        <?php
                        $frozen_plugin = isset($frozen['plugin'][$file]) ? $frozen['plugin'][$file] : '';
                        $admin->render_freeze_version_field('plugin', $file, $data['Version'], $frozen_plugin, false);
                        ?>
                    </td>                    <td>
                        <?php
                        if (!empty($frozen_plugin)) {
                            echo '<span style="color:#dc3232;font-weight:bold;">' . esc_html__('Freeze is active', 'complete-updates-manager') . '</span>';
                            echo ' <a href="#" class="wum-unfreeze-version" data-target="#wum_freeze_' . esc_attr($safe_slug) . '" style="color:#dc3232;text-decoration:none;" title="' . esc_attr__('Cancel freeze', 'complete-updates-manager') . '">(' . esc_html__('cancel', 'complete-updates-manager') . ')</a>';
                        } else {
                            echo '<span style="color:#999;">' . esc_html__('Not frozen', 'complete-updates-manager') . '</span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php foreach ($themes as $slug => $theme): 
                    $safe_slug = sanitize_html_class($slug);
                ?>
                <tr>
                    <td><?php echo esc_html($theme->get('Name')); ?></td>
                    <td><?php echo esc_html($theme->get('Version')); ?></td>
                    <td style="text-align:center;">
                        <button type="button" class="button wum-copy-version" data-version="<?php echo esc_attr($theme->get('Version')); ?>" data-target="#wum_freeze_<?php echo esc_attr($safe_slug); ?>" title="<?php esc_attr_e('Copy current version', 'complete-updates-manager'); ?>">&#8594;</button>
                    </td>                    <td>
                        <?php
                        $frozen_theme = isset($frozen['theme'][$slug]) ? $frozen['theme'][$slug] : '';
                        $admin->render_freeze_version_field('theme', $slug, $theme->get('Version'), $frozen_theme, false);
                        ?>
                    </td>                    <td>
                        <?php
                        if (!empty($frozen_theme)) {
                            echo '<span style="color:#dc3232;font-weight:bold;">' . esc_html__('Freeze is active', 'complete-updates-manager') . '</span>';
                            echo ' <a href="#" class="wum-unfreeze-version" data-target="#wum_freeze_' . esc_attr($safe_slug) . '" style="color:#dc3232;text-decoration:none;" title="' . esc_attr__('Cancel freeze', 'complete-updates-manager') . '">(' . esc_html__('cancel', 'complete-updates-manager') . ')</a>';
                        } else {
                            echo '<span style="color:#999;">' . esc_html__('Not frozen', 'complete-updates-manager') . '</span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><input type="submit" class="button button-primary" value="<?php esc_attr_e('Save Freeze Settings', 'complete-updates-manager'); ?>" /></p>
        </form>
        <p class="description"><?php esc_html_e('Specify a version to freeze. Updates above this version will be blocked, even manually.', 'complete-updates-manager'); ?></p>
        <?php
    }

    /**
     * Handle saving version freeze settings
     *
     * @since 1.1.0
     * @return void
     */
    public function maybe_save_version_freeze() {
        if (!current_user_can('manage_options')) return;
        if (isset($_POST['wum_version_freeze_nonce']) && wp_verify_nonce($_POST['wum_version_freeze_nonce'], 'wum_version_freeze_action')) {
            $freeze = isset($_POST['wum_version_freeze']) ? $_POST['wum_version_freeze'] : [];
            $clean = [];
            if (!empty($freeze['core'])) {
                $clean['core'] = sanitize_text_field($freeze['core']);
            }
            if (!empty($freeze['plugin']) && is_array($freeze['plugin'])) {
                foreach ($freeze['plugin'] as $file => $ver) {
                    if (!empty($ver)) $clean['plugin'][$file] = sanitize_text_field($ver);
                }
            }
            if (!empty($freeze['theme']) && is_array($freeze['theme'])) {
                foreach ($freeze['theme'] as $slug => $ver) {
                    if (!empty($ver)) $clean['theme'][$slug] = sanitize_text_field($ver);
                }
            }
            update_option('wum_version_freeze', $clean);
        }
    }

    /**
     * Add Version Freeze tab to settings page navigation
     *
     * @since 1.1.0
     * @return void
     */
    public function render_settings_tabs($active = '') {
        $tabs = [
            'general' => __('General', 'complete-updates-manager'),
            'security' => __('Security Monitoring', 'complete-updates-manager'),
            'freeze'   => __('Version Freeze', 'complete-updates-manager'),
        ];
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $label) {
            $class = ($active === $tab) ? ' nav-tab-active' : '';
            $url = admin_url('options-general.php?page=complete-updates-manager&tab=' . $tab);
            echo '<a href="' . esc_url($url) . '" class="nav-tab' . esc_attr($class) . '">' . esc_html($label) . '</a>';
        }
        echo '</h2>';
    }

    /**
     * Render the settings page with tabs
     *
     * @since 1.1.0
     * @return void
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
        $this->maybe_save_version_freeze();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <?php $this->render_settings_tabs($tab); ?>
            <?php if ($tab === 'general') : ?>
                <form action="options.php" method="post">
                    <?php
                    settings_fields('wum_settings_group');
                    do_settings_sections('complete-updates-manager');
                    wp_nonce_field('wum_settings_action', 'wum_settings_nonce');
                    submit_button(__('Save Settings', 'complete-updates-manager'));
                    ?>
                </form>
            <?php elseif ($tab === 'security') : ?>
                <div style="margin-top:20px;">
                    <?php $this->render_security_section(); ?>
                </div>
            <?php elseif ($tab === 'freeze') : ?>
                <div style="margin-top:20px;">
                    <?php $this->render_version_freeze_tab(); ?>
                </div>
            <?php endif; ?>
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
     * @since  1.0.1
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
    }    /**
     * Fetch security updates from WordPress API
     *
     * @since  1.0.1
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
        
        // Check for plugin security updates
        $this->check_plugin_security_updates($security_issues);
        
        // Check for theme security updates  
        $this->check_theme_security_updates($security_issues);
        
        if (!empty($security_issues)) {
            update_option('wum_security_issues', $security_issues);
        } else {
            delete_option('wum_security_issues');
        }
    }

    /**
     * Check plugin security updates
     *
     * @param array $security_issues Reference to security issues array
     * @return void
     */
    private function check_plugin_security_updates(&$security_issues) {
        // Temporarily allow plugin update checks
        delete_site_transient('update_plugins');
        wp_update_plugins();
        
        $plugin_updates = get_site_transient('update_plugins');
        
        if (!empty($plugin_updates->response)) {
            foreach ($plugin_updates->response as $plugin_file => $plugin_data) {
                if (!empty($plugin_data->new_version) && file_exists(WP_PLUGIN_DIR . '/' . $plugin_file)) {
                    $plugin_info = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file, false, false);
                    $security_issues[] = [
                        'type' => 'plugin',
                        'message' => sprintf(
                            /* translators: 1: Plugin name, 2: Current version, 3: New version */
                            __('Plugin "%1$s" has an update available (v%2$s → v%3$s). This may include security fixes.', 'complete-updates-manager'),
                            $plugin_info['Name'],
                            $plugin_info['Version'],
                            $plugin_data->new_version
                        )
                    ];
                }
            }
        }
    }

    /**
     * Check theme security updates
     *
     * @param array $security_issues Reference to security issues array
     * @return void
     */
    private function check_theme_security_updates(&$security_issues) {
        // Temporarily allow theme update checks
        delete_site_transient('update_themes');
        wp_update_themes();
        
        $theme_updates = get_site_transient('update_themes');
        
        if (!empty($theme_updates->response)) {
            foreach ($theme_updates->response as $theme_slug => $theme_data) {
                if (!empty($theme_data['new_version'])) {
                    $theme = wp_get_theme($theme_slug);
                    if ($theme->exists()) {
                        $security_issues[] = [
                            'type' => 'theme',
                            'message' => sprintf(
                                /* translators: 1: Theme name, 2: Current version, 3: New version */
                                __('Theme "%1$s" has an update available (v%2$s → v%3$s). This may include security fixes.', 'complete-updates-manager'),
                                $theme->get('Name'),
                                $theme->get('Version'),
                                $theme_data['new_version']
                            )
                        ];
                    }
                }
            }
        }
    }    /**
     * Display security update notices
     *
     * @since  1.0.1
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
            $message = is_array($issue) && isset($issue['message']) ? $issue['message'] : $issue;
            echo '<li>' . esc_html($message) . '</li>';
        }
        
        echo '</ul>';
        echo '<p>' . esc_html__('Consider temporarily enabling updates to apply these security fixes.', 'complete-updates-manager') . '</p>';
        echo '</div>';
    }
}