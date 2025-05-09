<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Complete_Updates_Manager_Admin Class
 *
 * Handles all admin interface related functionality
 *
 * @since 1.0.0
 */
class Complete_Updates_Manager_Admin {

    /**
     * Initialize the admin functionality
     *
     * @since  1.0.0
     * @return void
     */
    public function initialize() {
        // Add notice to admin bar when plugin is active
        add_action('admin_bar_menu', [$this, 'add_admin_bar_notice'], 100);
        
        // Add admin CSS styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
        
        // Add plugin action links
        add_filter('plugin_action_links_' . WUM_PLUGIN_BASENAME, [$this, 'add_plugin_action_links']);
    }

    /**
     * Add notice to admin bar when plugin is active
     *
     * @param  WP_Admin_Bar $admin_bar The admin bar object
     * @return void
     */
    public function add_admin_bar_notice($admin_bar) {
        // Only add if user can update core
        if (!current_user_can('manage_options')) {
            return;
        }

        $plugin_file = WUM_PLUGIN_DIR . 'complete-updates-manager.php';

        if (!file_exists($plugin_file)) {
            return;
        }

        $plugin_data = get_plugin_data($plugin_file);

        if (empty($plugin_data) || !is_array($plugin_data) || empty($plugin_data['Name'])) {
            return;
        }

        $admin_bar->add_menu([
            'id' => 'wum-notice',
            'title' => '<span class="dashicons dashicons-shield" aria-hidden="true"></span> ' . 
                       esc_html__('Updates Disabled', 'complete-updates-manager'),
            'href' => esc_url(wum_get_settings_url()),
            'meta' => [
                'class' => 'wp-admin-bar-wum-notice',
                'title' => sprintf(
                    /* translators: %s: Name of the plugin */
                    esc_attr__('"%s" plugin is active - updates are disabled', 'complete-updates-manager'),
                    esc_attr($plugin_data['Name'])
                )
            ],
        ]);
    }

    /**
     * Add CSS styles to admin interface
     *
     * @return void
     */
    public function enqueue_admin_styles() {
        wp_add_inline_style('admin-bar', 
            '.wp-admin-bar-wum-notice { 
                background-color: rgba(255, 37, 37, 0.4) !important; 
            } 
            .wp-admin-bar-wum-notice .dashicons { 
                font-family: dashicons !important;
                padding-top: 7px;
            }
            .plugins .wum-settings {
                color:rgb(139, 13, 13);
                font-weight: bold;
            }'
        );
    }
    
    /**
     * Add plugin action links on plugin page
     *
     * @param  array $links Plugin action links array
     * @return array        Modified links array
     */
    public function add_plugin_action_links($links) {
        // Add settings link
        $settings_link = '<a href="' . esc_url(wum_get_settings_url()) . '">' . 
                          esc_html__('Settings', 'complete-updates-manager') . '</a>';
                          
        // Add status link
        $status_link = '<span class="wum-settings">' . 
                       esc_html__('Updates disabled', 'complete-updates-manager') . '</span>';
        
        // Add documentation link
        $docs_link = '<a href="https://globus.studio" target="_blank">' . 
                    esc_html__('Documentation', 'complete-updates-manager') . '</a>';
        
        // Make our links appear first
        array_unshift($links, $status_link, $settings_link, $docs_link);
        
        return $links;
    }
}