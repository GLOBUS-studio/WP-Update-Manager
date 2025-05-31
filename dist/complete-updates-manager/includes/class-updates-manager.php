<?php

if (!defined('ABSPATH')) {
    exit; // Prevents direct access to file
}

/**
 * Complete_Updates_Manager Class
 *
 * Main class that manages complete disabling of WordPress updates.
 * Disables all core, plugin, theme updates and update notifications.
 *
 * @since 1.0.1
 */
class Complete_Updates_Manager {

    /**
     * Plugin settings
     *
     * @since  1.0.1
     * @var array
     */
    private $settings;

    /**
     * Initialize the class and set up hooks
     *
     * Registers all necessary hooks and filters to disable updates
     * in different parts of WordPress.
     *
     * @since  1.0.1
     * @return void
     */
    public function initialize() {
        // Load settings
        $this->settings = $this->get_settings();
        
        // Disable updates in admin panel
        add_action('admin_init', [$this, 'disable_updates_admin_init']);
        
        // Filter transients before they are set
        // to prevent update checks
        add_filter('pre_transient_update_themes', [$this, 'override_version_check']);
        add_filter('pre_site_transient_update_themes', [$this, 'override_version_check']); 
        add_filter('pre_transient_update_plugins', [$this, 'override_version_check']);
        add_filter('pre_site_transient_update_plugins', [$this, 'override_version_check']); 
        add_filter('pre_transient_update_core', [$this, 'override_version_check']);
        add_filter('pre_site_transient_update_core', [$this, 'override_version_check']);
        
        // Priority 21 ensures our filter runs after standard WordPress checks
        add_action('pre_set_site_transient_update_plugins', [$this, 'override_version_check'], 21); 
        add_action('pre_set_site_transient_update_themes', [$this, 'override_version_check'], 21); 

        // Filter cron events and block HTTP requests to update API
        add_action('schedule_event', [$this, 'filter_cron_events']);
        add_filter('pre_http_request', [$this, 'block_update_requests'], 10, 3);
        
        // Disable all automatic updates
        $this->disable_automatic_updates();
        
        // Hide update UI elements
        $this->hide_update_ui();
    }
    
    /**
     * Get plugin settings
     *
     * @since  1.0.1
     * @return array Plugin settings
     */
    private function get_settings() {
        $default_settings = [
            'disable_core_updates' => 1,
            'disable_plugin_updates' => 1,
            'disable_theme_updates' => 1,
            'monitor_security_updates' => 0,
            'security_check_interval' => 'daily',
        ];
        
        $settings = get_option('wum_settings', []);
        
        // Проверка на корректность полученных данных
        if (empty($settings) || !is_array($settings)) {
            $settings = [];
        }
        
        return wp_parse_args($settings, $default_settings);
    }
    
    /**
     * Disable various update checks and notifications in the admin area
     *
     * Disables various update checks and notifications in the admin panel,
     * including maintenance notifications, checks in the Site Health tool, etc.
     *
     * @since  1.0.1
     * @return void
     */
    public function disable_updates_admin_init() {
        // Check function availability for security
        if (!function_exists("remove_action")) {
            return;
        }
        
        // Hide maintenance and update notifications
        add_filter('site_status_tests', [$this, 'remove_update_health_checks']);
        
        // Remove update notifications in admin panel
        remove_action('admin_notices', 'update_nag', 3);
        remove_action('network_admin_notices', 'update_nag', 3);
        remove_action('admin_notices', 'maintenance_nag');
        remove_action('network_admin_notices', 'maintenance_nag');
        
        // Remove ability to update plugins
        $this->remove_update_capabilities();
        
        // Comprehensive disabling of updates for various WordPress components
        if (!empty($this->settings['disable_theme_updates'])) {
            $this->disable_theme_updates();    // Disable theme updates
        }
        
        if (!empty($this->settings['disable_plugin_updates'])) {
            $this->disable_plugin_updates();   // Disable plugin updates
        }
        
        if (!empty($this->settings['disable_core_updates'])) {
            $this->disable_core_updates();     // Disable core updates
        }
    }

    /**
     * Block HTTP requests to WordPress update API
     *
     * Blocks HTTP requests to WordPress update API to prevent
     * background update checks.
     *
     * @param  mixed  $pre  Default return value
     * @param  array  $args HTTP request arguments
     * @param  string $url  The URL to check
     * @return mixed        True to block request, default value otherwise
     */
    public function block_update_requests($pre, $args, $url) {
        // Check for empty URL
        if (empty($url)) {
            return $pre;
        }

        // Check for valid URL
        if (!$host = parse_url($url, PHP_URL_HOST)) {
            return $pre;
        }

        $url_data = parse_url($url);

        // Block specific requests to WordPress API
        // Block check-version, browse-happy and serve-happy requests
        if (false !== stripos($host, 'api.wordpress.org') &&
            isset($url_data['path']) &&
            (false !== stripos($url_data['path'], 'update-check') ||
             false !== stripos($url_data['path'], 'version-check') ||
             false !== stripos($url_data['path'], 'browse-happy') ||
             false !== stripos($url_data['path'], 'serve-happy'))) {
            
            // Allow security monitoring if enabled
            if ($this->settings['monitor_security_updates'] && 
                false !== stripos($url_data['path'], 'version-check')) {
                return $pre;
            }
            
            // Return true to block request
            return true;
        }

        // Don't block other requests
        return $pre;
    }
    
    /**
     * Filter WordPress cron events to remove update checks
     *
     * Filters WordPress scheduler events to remove tasks
     * related to update checking.
     *
     * @param  object $event The scheduled event
     * @return mixed         False to remove event, event object otherwise
     */
    public function filter_cron_events($event) {
        // Skip if security monitoring is enabled and this is a version check
        if ($this->settings['monitor_security_updates'] && 
            $event->hook === 'wp_version_check') {
            return $event;
        }
        
        // Remove update check events from scheduler
        switch ($event->hook) {
            case 'wp_version_check':       // Core update check
                if ($this->settings['disable_core_updates']) {
                    $event = false;
                }
                break;
            case 'wp_update_plugins':      // Plugin update check
                if ($this->settings['disable_plugin_updates']) {
                    $event = false;
                }
                break;
            case 'wp_update_themes':       // Theme update check
                if ($this->settings['disable_theme_updates']) {
                    $event = false;
                }
                break;
            case 'wp_maybe_auto_update':   // Automatic updates
                $event = false;
                break;
        }
        return $event;
    }
    
    /**
     * Override version check information with empty data
     *
     * Overrides version check data, replacing it with empty data,
     * so WordPress thinks all components are updated to the latest version.
     *
     * @param  object $transient The transient value containing the update data
     * @return object            Modified transient value
     */
    public function override_version_check($transient) {
        global $wp_version; // Ensure $wp_version is available if needed later.

        $current_filter = current_filter();

        // --- 1. Apply Version Freeze logic to $transient ---
        // This modifies $transient directly by unsetting updates that are newer than frozen versions.
        if (function_exists('wum_get_frozen_version')) {
            // Core Version Freeze
            if (strpos($current_filter, 'update_core') !== false && isset($transient->updates) && is_array($transient->updates)) {
                $frozen_core_v = wum_get_frozen_version('core');
                if ($frozen_core_v) {
                    foreach ($transient->updates as $k => $update_obj) {
                        if (is_object($update_obj)) {
                            $update_version_to_check = '';
                            if (isset($update_obj->version)) {
                                $update_version_to_check = $update_obj->version;
                            } elseif (isset($update_obj->current)) { // Fallback for older structures
                                $update_version_to_check = $update_obj->current;
                            }
                            
                            if ($update_version_to_check && version_compare($update_version_to_check, $frozen_core_v, '>')) {
                                unset($transient->updates[$k]);
                            }
                        }
                    }
                }
            }

            // Plugin Version Freeze
            if (strpos($current_filter, 'update_plugins') !== false && isset($transient->response) && is_array($transient->response)) {
                foreach ($transient->response as $plugin_file => $plugin_update_data) {
                    if (is_object($plugin_update_data)) {
                        $frozen_plugin_v = wum_get_frozen_version('plugin', $plugin_file);
                        if ($frozen_plugin_v && isset($plugin_update_data->new_version) && version_compare($plugin_update_data->new_version, $frozen_plugin_v, '>')) {
                            unset($transient->response[$plugin_file]);
                        }
                    }
                }
            }

            // Theme Version Freeze
            if (strpos($current_filter, 'update_themes') !== false && isset($transient->response) && is_array($transient->response)) {
                foreach ($transient->response as $theme_slug => $theme_update_data) {
                    if (is_object($theme_update_data)) {
                        $frozen_theme_v = wum_get_frozen_version('theme', $theme_slug);
                        if ($frozen_theme_v && isset($theme_update_data->new_version) && version_compare($theme_update_data->new_version, $frozen_theme_v, '>')) {
                            unset($transient->response[$theme_slug]);
                        }
                    }
                }
            }
        }

        // --- 2. Handle security monitoring for core ---
        // If monitoring security updates for core, return the $transient (which has now been processed by Version Freeze).
        if (!empty($this->settings['monitor_security_updates']) && (strpos($current_filter, 'update_core') !== false)) {
            return $transient;
        }

        // --- 3. Handle global disable settings ---
        // Determine if an empty transient should be returned based on global settings.
        $return_empty_transient_flag = false;
        if (strpos($current_filter, 'update_themes') !== false && !empty($this->settings['disable_theme_updates'])) {
            $return_empty_transient_flag = true;
        } elseif (strpos($current_filter, 'update_plugins') !== false && !empty($this->settings['disable_plugin_updates'])) {
            $return_empty_transient_flag = true;
        } elseif (strpos($current_filter, 'update_core') !== false && !empty($this->settings['disable_core_updates'])) {
            // This condition is met if core updates are globally disabled AND security monitoring for core is OFF
            $return_empty_transient_flag = true;
        }

        if ($return_empty_transient_flag) {
            $empty_transient_obj = new stdClass;
            $empty_transient_obj->last_checked = time();

            if (strpos($current_filter, 'update_core') !== false) {
                if (!isset($wp_version)) { // $wp_version is needed for core's version_checked
                     include_once ABSPATH . WPINC . '/version.php';
                }
                $empty_transient_obj->version_checked = isset($wp_version) ? $wp_version : '';
                $empty_transient_obj->updates = [];
                $empty_transient_obj->response = 'latest'; // Standard for no core updates
            } else { // Plugins or Themes
                $empty_transient_obj->response = [];
                $empty_transient_obj->translations = [];
            }
            return $empty_transient_obj;
        }

        // --- 4. If not returned yet, updates for this type are generally enabled ---
        // Return the $transient, which has been processed by Version Freeze and not overridden by global disables.
        return $transient;
    }
    
    /**
     * Remove update health checks from Site Health
     *
     * Removes update checks from Site Health tool,
     * to prevent warnings about disabled updates.
     *
     * @param  array $tests Site health tests array
     * @return array        Modified tests array
     */
    public function remove_update_health_checks($tests) {
        // Remove tests related to updates
        unset($tests['async']['background_updates']);
        unset($tests['direct']['plugin_theme_auto_updates']);
        return $tests;
    }
    
    /**
     * Remove update capabilities from users
     *
     * Removes plugin update capabilities from users
     * for additional protection against updates.
     *
     * @return void
     */
    private function remove_update_capabilities() {
        global $current_user;
        
        // Disable plugin update capability if enabled in settings
        if ($this->settings['disable_plugin_updates']) {
            $current_user->allcaps['update_plugins'] = 0;
        }
        
        // Disable theme update capability if enabled in settings
        if ($this->settings['disable_theme_updates']) {
            $current_user->allcaps['update_themes'] = 0;
        }
        
        // Disable core update capability if enabled in settings
        if ($this->settings['disable_core_updates']) {
            $current_user->allcaps['update_core'] = 0;
        }
    }
    
    /**
     * Disable all automatic updates
     *
     * Disables all automatic updates via filters
     * and removes corresponding actions.
     *
     * @return void
     */
    private function disable_automatic_updates() {
        // Disable all types of automatic updates via filters

        // Disable automatic translation updates
        add_filter('auto_update_translation', '__return_false');

        // Completely disable the automatic updater system
        add_filter('automatic_updater_disabled', '__return_true');

        // Disable minor core updates (security/maintenance releases)
        add_filter('allow_minor_auto_core_updates', '__return_false');

        // Disable major core updates (feature releases)
        add_filter('allow_major_auto_core_updates', '__return_false');

        // Disable development core updates (nightly/beta/RC)
        add_filter('allow_dev_auto_core_updates', '__return_false');

        // Disable all core auto-updates
        add_filter('auto_update_core', '__return_false');

        // Disable core auto-updates (alternative filter)
        add_filter('wp_auto_update_core', '__return_false');
        
        // Disable email notifications about core updates
        add_filter('auto_core_update_send_email', '__return_false');

        // Disable email notifications about available core updates
        add_filter('send_core_update_notification_email', '__return_false');

        // Disable auto-updates for plugins
        add_filter('auto_update_plugin', '__return_false');

        // Disable auto-updates for themes
        add_filter('auto_update_theme', '__return_false');
        
        // Disable debug email messages for automatic updates
        add_filter('automatic_updates_send_debug_email', '__return_false');

        // Always treat the install as being under version control (disables auto-updates)
        add_filter('automatic_updates_is_vcs_checkout', '__return_true');
        
        // Remove scheduled update check actions
        remove_action('init', 'wp_schedule_update_checks');
        
        // Conditionally remove all plugin API filters (prevents plugin update checks)
        if (!empty($this->settings['disable_plugins_api_filter'])) {
            remove_all_filters('plugins_api');
        }
    }

    /**
     * Disable theme updates
     *
     * Disables WordPress theme update mechanisms for different versions.
     *
     * @return void
     */
    private function disable_theme_updates() {
        // Remove theme update check when loading the themes page (WP 2.8 - 3.0)
        remove_action('load-themes.php', 'wp_update_themes');
        // Remove theme update check when loading the update page (WP 2.8 - 3.0)
        remove_action('load-update.php', 'wp_update_themes');
        // Remove maybe update themes action on admin init (WP 2.8 - 3.0)
        remove_action('admin_init', '_maybe_update_themes');
        // Remove scheduled theme update check (WP 2.8 - 3.0)
        remove_action('wp_update_themes', 'wp_update_themes');
        // Clear scheduled theme update hook (WP 2.8 - 3.0)
        wp_clear_scheduled_hook('wp_update_themes');
        
        // Remove theme update check when loading update-core.php (WP 3.0+)
        remove_action('load-update-core.php', 'wp_update_themes');
        // Clear scheduled theme update hook again for newer WP
        wp_clear_scheduled_hook('wp_update_themes');
        
        // Disable theme update information in options
        add_filter('pre_option_theme_updates', '__return_empty_array');
    }
    
    /**
     * Disable plugin updates
     *
     * Disables WordPress plugin update mechanisms for different versions.
     *
     * @return void
     */
    private function disable_plugin_updates() {
        // Remove plugin update check when loading the plugins page (WP 2.8 - 3.0)
        remove_action('load-plugins.php', 'wp_update_plugins');
        // Remove plugin update check when loading the update page (WP 2.8 - 3.0)
        remove_action('load-update.php', 'wp_update_plugins');
        // Remove maybe update plugins action on admin init (WP 2.8 - 3.0)
        remove_action('admin_init', '_maybe_update_plugins');
        // Remove scheduled plugin update check (WP 2.8 - 3.0)
        remove_action('wp_update_plugins', 'wp_update_plugins');
        // Clear scheduled plugin update hook (WP 2.8 - 3.0)
        wp_clear_scheduled_hook('wp_update_plugins');
        
        // Remove plugin update check when loading update-core.php (WP 3.0+)
        remove_action('load-update-core.php', 'wp_update_plugins');
        // Clear scheduled plugin update hook again for newer WP
        wp_clear_scheduled_hook('wp_update_plugins');
        
        // Disable plugin update information in options
        add_filter('pre_option_plugin_updates', '__return_empty_array');
    }
    
    /**
     * Disable core updates
     *
     * Disables WordPress core update mechanisms for different system versions.
     *
     * @return void
     */
    private function disable_core_updates() {
        // Disable core update information in options (WP 2.8 - 3.0)
        add_filter('pre_option_update_core', '__return_null');
        // Remove scheduled core version check (WP 2.8 - 3.0)
        remove_action('wp_version_check', 'wp_version_check');
        // Remove maybe update core action on admin init (WP 2.8 - 3.0)
        remove_action('admin_init', '_maybe_update_core');
        // Clear scheduled core version check hook (WP 2.8 - 3.0)
        wp_clear_scheduled_hook('wp_version_check');
        
        // Clear scheduled core version check hook again for newer WP (WP 3.0+)
        wp_clear_scheduled_hook('wp_version_check');
        
        // Remove automatic update actions (WP 3.7+)
        remove_action('wp_maybe_auto_update', 'wp_maybe_auto_update');
        remove_action('admin_init', 'wp_maybe_auto_update');
        remove_action('admin_init', 'wp_auto_update_core');
        wp_clear_scheduled_hook('wp_maybe_auto_update');
        
        // Remove all plugin API filters to prevent update checks
        remove_all_filters('plugins_api');
        
        // Additionally: hide notifications about available core updates
        add_filter('pre_option_update_core', '__return_null');
        add_filter('pre_site_transient_update_core', '__return_null');
    }
    
    /**
     * Hide UI elements related to updates
     *
     * Removes or hides menu items and interface elements related to updates
     *
     * @since 1.0.1
     * @return void
     */
    public function hide_update_ui() {
        // Hide updates from admin panel menu
        add_action('admin_menu', function() {
            remove_submenu_page('index.php', 'update-core.php');
        }, 999);
        
        // Add CSS to hide update counters
        add_action('admin_head', function() {
            echo '<style>
                .update-plugins, .update-count, .plugin-count, 
                .theme-count, .update-message, .updates-available { 
                    display: none !important; 
                }
            </style>';
        });
    }
}