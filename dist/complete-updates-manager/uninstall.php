<?php
/**
 * Uninstall handler for Complete Updates Manager
 *
 * Cleans up all plugin options and data when the plugin is uninstalled.
 *
 * @since 1.0.2
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete all plugin options
delete_option( 'wum_settings' );
delete_option( 'wum_version_freeze' );
delete_option( 'wum_security_issues' );
delete_option( 'wum_first_activation_done' );
delete_option( 'wum_show_activation_notice' );

// Delete all plugin transients
delete_transient( 'wum_security_check' );
delete_site_transient( 'wum_security_check' );

// Clear update-related transients to restore normal WordPress behavior
delete_site_transient( 'update_core' );
delete_site_transient( 'update_plugins' );
delete_site_transient( 'update_themes' );
