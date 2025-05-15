<?php
/*
Plugin Name: Complete Update Manager
Description: Advanced tool to fully disable WordPress theme, plugin and core update checking, related cronjobs and notifications.
Plugin URI:  https://globus.studio
Version:     1.0.1
Author:      Yevhen Leonidov
Author URI:  https://leonidov.dev
Text Domain: complete-updates-manager
Domain Path: /languages
License:     GPL2

Copyright 2024 GLOBUS.studio

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

define('WUM_MIN_PHP_VERSION', '7.4');
define('WUM_MIN_WP_VERSION', '3.8');

/**
 * Check if the current environment meets requirements
 *
 * @return boolean True if requirements are met, false otherwise
 */
function wum_requirements_check() {
    $php_version = phpversion();
    $wp_version = get_bloginfo('version');
    $requirements_met = true;

    if (version_compare($php_version, WUM_MIN_PHP_VERSION, '<')) {
        add_action('admin_notices', function() use ($php_version) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php printf(
                        /* translators: %1$s: minimum PHP version required, %2$s: current PHP version */
                        esc_html__('Complete Updates Manager requires PHP version %1$s or later. You are running version %2$s. Please upgrade PHP or disable the plugin.', 'complete-updates-manager'),
                        esc_html(WUM_MIN_PHP_VERSION),
                        esc_html($php_version)
                    ); ?>
                </p>
            </div>
            <?php
        });
        $requirements_met = false;
    }

    if (version_compare($wp_version, WUM_MIN_WP_VERSION, '<')) {
        add_action('admin_notices', function() use ($wp_version) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php printf(
                        /* translators: %1$s: minimum PHP version required, %2$s: current PHP version */
                        esc_html__('Complete Updates Manager requires WordPress version %1$s or later. You are running version %2$s. Please upgrade WordPress or disable the plugin.', 'complete-updates-manager'),
                        esc_html(WUM_MIN_WP_VERSION),
                        esc_html($wp_version)
                    ); ?>
                </p>
            </div>
            <?php
        });
        $requirements_met = false;
    }

    return $requirements_met;
}

// Define plugin version and constants
define('WUM_VERSION', '1.0.0');
define('WUM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WUM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WUM_PLUGIN_BASENAME', plugin_basename(__FILE__));

register_activation_hook(__FILE__, 'wum_plugin_activation');
register_deactivation_hook(__FILE__, 'wum_plugin_deactivation');

// Initialize the plugin only if requirements are met
if (wum_requirements_check()) {
    // Include required files
    require_once WUM_PLUGIN_DIR . 'includes/class-updates-manager.php';
    require_once WUM_PLUGIN_DIR . 'includes/class-admin-interface.php';
    require_once WUM_PLUGIN_DIR . 'includes/class-settings.php';
    require_once WUM_PLUGIN_DIR . 'includes/helpers.php';

    // Initialize the plugin
    function wum_initialize() {
        if (!function_exists('load_plugin_textdomain') || 
            !function_exists('plugin_basename') || 
            !function_exists('add_action')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Complete Updates Manager: Required WordPress functions are missing.');
            }
            return;
        }

        // Load text domain
        load_plugin_textdomain('complete-updates-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');

        // Initialize core functionality
        $updates_manager = new Complete_Updates_Manager();
        $updates_manager->initialize();
        
        // Initialize admin interface if in admin area
        if (is_admin()) {
            $admin_interface = new Complete_Updates_Manager_Admin();
            $admin_interface->initialize();
            
            // Initialize settings page
            $settings = new Complete_Updates_Manager_Settings();
            $settings->initialize();
        }
        
        // Set constants that WordPress might use for updates
        if (!defined('AUTOMATIC_UPDATER_DISABLED')) {
            define('AUTOMATIC_UPDATER_DISABLED', true);
        }
        
        if (!defined('WP_AUTO_UPDATE_CORE')) {
            define('WP_AUTO_UPDATE_CORE', false);
        }
    }

    // Hook to WordPress init to start the plugin
    add_action('init', 'wum_initialize', 5);
}