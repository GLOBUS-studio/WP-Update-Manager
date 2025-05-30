<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Complete_Updates_Manager_Admin Class
 *
 * Handles all admin interface related functionality
 *
 * @since 1.0.1
 */
class Complete_Updates_Manager_Admin {

    /**
     * Initialize the admin functionality
     *
     * @since  1.0.1
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

        // Add JS for freeze version field validation and copy
        add_action('admin_footer', function() {
            ?>            <script>
            (function($){
                $(document).on('click', '.wum-copy-version', function(){
                    var version = $(this).data('version');
                    var target = $(this).data('target');
                    // Always use jQuery to select by id (target is always safe now)
                    var $input = $(target);
                    if(version && $input.length) {
                        $input.val(version).trigger('change');
                    }
                });
                $(document).on('click', '.wum-unfreeze-version', function(e){
                    e.preventDefault();
                    var target = $(this).data('target');
                    var $input = $(target);
                    if($input.length) {
                        $input.val('').trigger('change');
                    }
                });
                $(document).on('input', 'input[id^="wum_freeze_"]', function(){
                    var val = $(this).val();
                    if(!/^([0-9]+\.?)+$/.test(val) && val !== '') {
                        $(this).val(val.replace(/[^0-9.]/g, ''));
                    }
                });
            })(jQuery);
            </script>
            <?php
        });
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
        $docs_link = '<a href="https://globus.studio/wp-update-manager-disable-all-wordpress-updates-with-full-control/" target="_blank">' . 
                    esc_html__('Documentation', 'complete-updates-manager') . '</a>';
        
        // Make our links appear first
        array_unshift($links, $status_link, $settings_link, $docs_link);
        
        return $links;
    }

    /**
     * Render freeze version field with copy button and indicator
     *
     * @param string $type core|plugin|theme
     * @param string $slug Plugin file or theme slug ('' for core)
     * @param string $current_version Current version
     * @param string $frozen_version Frozen version value
     * @param bool $show_button_and_indicator Show button and indicator (default true)
     * @return void
     */
    public function render_freeze_version_field($type, $slug, $current_version, $frozen_version, $show_button_and_indicator = true) {
        $safe_slug = $type === 'core' ? 'core' : sanitize_html_class($slug);
        $field_id = 'wum_freeze_' . $safe_slug;
        $has_frozen = !empty($frozen_version);
        
        // Build correct name attribute
        if ($type === 'core') {
            $name = 'wum_version_freeze[core]';
        } elseif ($type === 'plugin') {
            $name = 'wum_version_freeze[plugin][' . esc_attr($slug) . ']';
        } elseif ($type === 'theme') {
            $name = 'wum_version_freeze[theme][' . esc_attr($slug) . ']';
        } else {
            $name = '';
        }
        ?>
        <div class="wum-freeze-version-row" style="margin-bottom:8px;">
            <label for="<?php echo $field_id; ?>" class="screen-reader-text">
                <?php esc_html_e('Freeze version', 'complete-updates-manager'); ?>
            </label>
            <input type="text" id="<?php echo $field_id; ?>" name="<?php echo $name; ?>" value="<?php echo esc_attr($frozen_version); ?>" pattern="^[0-9.]+$" style="width:100px;" autocomplete="off" />
            <?php if ($show_button_and_indicator): ?>
                <button type="button" class="button wum-copy-version" data-version="<?php echo esc_attr($current_version); ?>" data-target="#<?php echo $field_id; ?>" title="<?php esc_attr_e('Copy current version', 'complete-updates-manager'); ?>">&#8594;</button>
                <?php if ($has_frozen): ?>
                    <span class="wum-frozen-indicator" title="<?php esc_attr_e('Frozen version is set', 'complete-updates-manager'); ?>" style="color:#dc3232;font-weight:bold;">&#9679;</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }
}