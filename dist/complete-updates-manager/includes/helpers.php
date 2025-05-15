<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if the current user has the capability to manage plugin settings
 *
 * @since  1.0.1
 * @return boolean True if user can manage settings, false otherwise
 */
function wum_current_user_can_manage() {
    return current_user_can('manage_options');
}

/**
 * Get plugin settings URL
 *
 * @since  1.0.1
 * @return string Settings page URL
 */
function wum_get_settings_url() {
    return esc_url(admin_url('options-general.php?page=complete-updates-manager'));
}

/**
 * Simple logging function for debugging
 *
 * @since  1.0.1
 * @param  mixed  $data       Data to log
 * @param  string $log_type   Type of log data (error, info, debug)
 * @return void
 */
function wum_log($data, $log_type = 'info') {
    if (WP_DEBUG === true) {
        if (is_array($data) || is_object($data)) {
            error_log(print_r($data, true));
        } else {
            error_log('[Complete Updates Manager] ' . $log_type . ': ' . $data);
        }
    }
}

/**
 * Get plugin version
 *
 * @since  1.0.1
 * @return string Plugin version
 */
function wum_get_version() {
    return WUM_VERSION;
}

/**
 * Check if specific update type is disabled
 *
 * @since  1.0.1
 * @param  string $type Update type (core, plugin, theme)
 * @return boolean True if update type is disabled
 */
function wum_is_update_disabled($type) {
    if (!in_array($type, ['core', 'plugin', 'theme'])) {
        return false;
    }
    
    $settings = get_option('wum_settings');
    
    if (empty($settings) || !is_array($settings)) {
        return false;
    }
    
    $setting_key = 'disable_' . $type . '_updates';
    return !empty($settings[$setting_key]);
}

/**
 * Check if security monitoring is enabled
 * 
 * @since 1.0.1
 * @return boolean True if security monitoring is enabled
 */
function wum_is_security_monitoring_enabled() {
    $settings = get_option('wum_settings');
    
    if (empty($settings) || !is_array($settings)) {
        return false;
    }
    
    return !empty($settings['monitor_security_updates']);
}

/**
 * Verify nonce for settings forms
 *
 * @since 1.0.1
 * @param string $nonce Nonce value
 * @param string $action Nonce action
 * @return boolean True if nonce is valid
 */
function wum_verify_nonce($nonce, $action) {
    if (empty($nonce) || empty($action)) {
        return false;
    }
    return wp_verify_nonce($nonce, $action);
}

/**
 * Plugin activation hook callback
 * 
 * @since  1.0.1
 * @return void
 */
function wum_plugin_activation() {
    // Clear any existing update caches
    delete_site_transient('update_core');
    delete_site_transient('update_plugins');
    delete_site_transient('update_themes');
    
    // Set default options if they don't exist
    if (!get_option('wum_settings')) {
        $default_settings = [
            'disable_core_updates' => 1,
            'disable_plugin_updates' => 1,
            'disable_theme_updates' => 1,
            'monitor_security_updates' => 0,
            'security_check_interval' => 'daily',
        ];
        add_option('wum_settings', $default_settings);
    }
    
    delete_transient('wum_security_check');
    delete_option('wum_security_issues');
}

/**
 * Plugin deactivation hook callback
 * 
 * @since  1.0.1
 * @return void
 */
function wum_plugin_deactivation() {
    // Clear any existing update caches
    delete_site_transient('update_core');
    delete_site_transient('update_plugins');
    delete_site_transient('update_themes');
    
    delete_transient('wum_security_check');
    
    // Restore update checks by forcing a fresh check on next page load
    wp_schedule_single_event(time() + 10, 'wp_version_check');
    wp_schedule_single_event(time() + 10, 'wp_update_plugins');
    wp_schedule_single_event(time() + 10, 'wp_update_themes');
}

function wum_validate_args($args) {
    if (!is_array($args)) {
        return false;
    }
    
    foreach ($args as $arg) {
        if (empty($arg)) {
            return false;
        }
    }
    
    return true;
}