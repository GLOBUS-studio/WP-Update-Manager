<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if the current user has the capability to manage plugin settings
 *
 * @since  1.0.1
 * @return boolean True if user can manage settings, false otherwise
 */
function wum_current_user_can_manage() {
	return current_user_can( 'manage_options' );
}

/**
 * Get plugin settings URL
 *
 * @since  1.0.1
 * @return string Settings page URL
 */
function wum_get_settings_url() {
	return esc_url( admin_url( 'options-general.php?page=complete-updates-manager' ) );
}

/**
 * Simple logging function for debugging
 *
 * @since  1.0.1
 * @param  mixed  $data       Data to log
 * @param  string $log_type   Type of log data (error, info, debug)
 * @return void
 */
function wum_log( $data, $log_type = 'info' ) {
	if ( WP_DEBUG === true ) {
		if ( is_array( $data ) || is_object( $data ) ) {
			error_log( print_r( $data, true ) );
		} else {
			error_log( '[Complete Updates Manager] ' . $log_type . ': ' . $data );
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
function wum_is_update_disabled( $type ) {
	if ( ! in_array( $type, array( 'core', 'plugin', 'theme' ) ) ) {
		return false;
	}

	$settings = get_option( 'wum_settings' );

	if ( empty( $settings ) || ! is_array( $settings ) ) {
		return false;
	}

	$setting_key = 'disable_' . $type . '_updates';
	return ! empty( $settings[ $setting_key ] );
}

/**
 * Check if security monitoring is enabled
 *
 * @since 1.0.1
 * @return boolean True if security monitoring is enabled
 */
function wum_is_security_monitoring_enabled() {
	$settings = get_option( 'wum_settings' );

	if ( empty( $settings ) || ! is_array( $settings ) ) {
		return false;
	}

	return ! empty( $settings['monitor_security_updates'] );
}

/**
 * Verify nonce for settings forms
 *
 * @since 1.0.1
 * @param string $nonce Nonce value
 * @param string $action Nonce action
 * @return boolean True if nonce is valid
 */
function wum_verify_nonce( $nonce, $action ) {
	if ( empty( $nonce ) || empty( $action ) ) {
		return false;
	}
	return wp_verify_nonce( $nonce, $action );
}

/**
 * Plugin activation hook callback
 *
 * @since  1.0.1
 * @return void
 */
function wum_plugin_activation() {
	// Clear any existing update caches
	delete_site_transient( 'update_core' );
	delete_site_transient( 'update_plugins' );
	delete_site_transient( 'update_themes' );

	// Check if this is first activation
	$is_first_activation = ! get_option( 'wum_settings' ) && ! get_option( 'wum_first_activation_done' );

	// Set default options if they don't exist
	if ( ! get_option( 'wum_settings' ) ) {
		$default_settings = array(
			'disable_core_updates'     => 1,
			'disable_plugin_updates'   => 1,
			'disable_theme_updates'    => 1,
			'monitor_security_updates' => 0,
			'security_check_interval'  => 'daily',
		);
		add_option( 'wum_settings', $default_settings );
	}

	// Set first activation flag and notice
	if ( $is_first_activation ) {
		add_option( 'wum_first_activation_done', true );
		add_option( 'wum_show_activation_notice', true );
	}

	delete_transient( 'wum_security_check' );
	delete_option( 'wum_security_issues' );
}

/**
 * Plugin deactivation hook callback
 *
 * @since  1.0.1
 * @return void
 */
function wum_plugin_deactivation() {
	// Clear any existing update caches
	delete_site_transient( 'update_core' );
	delete_site_transient( 'update_plugins' );
	delete_site_transient( 'update_themes' );

	delete_transient( 'wum_security_check' );

	// Restore update checks by forcing a fresh check on next page load
	wp_schedule_single_event( time() + 10, 'wp_version_check' );
	wp_schedule_single_event( time() + 10, 'wp_update_plugins' );
	wp_schedule_single_event( time() + 10, 'wp_update_themes' );
}

/**
 * Get frozen version for a component
 *
 * @param string $type core|plugin|theme
 * @param string $slug plugin file or theme slug (optional)
 * @return string|null
 */
function wum_get_frozen_version( $type, $slug = '' ) {
	$frozen = get_option( 'wum_version_freeze', array() );
	if ( $type === 'core' ) {
		return isset( $frozen['core'] ) ? $frozen['core'] : null;
	}
	if ( $type === 'plugin' && $slug && isset( $frozen['plugin'][ $slug ] ) ) {
		return $frozen['plugin'][ $slug ];
	}
	if ( $type === 'theme' && $slug && isset( $frozen['theme'][ $slug ] ) ) {
		return $frozen['theme'][ $slug ];
	}
	return null;
}

/**
 * Check if update is allowed for a component
 *
 * @param string $type core|plugin|theme
 * @param string $slug plugin file or theme slug (optional)
 * @param string $new_version
 * @return bool
 */
function wum_is_update_allowed( $type, $slug, $new_version ) {
	$frozen = wum_get_frozen_version( $type, $slug );
	if ( $frozen && version_compare( $new_version, $frozen, '>' ) ) {
		return false;
	}
	return true;
}

/**
 * Render freeze version field with copy button and indicator
 *
 * @param string $type core|plugin|theme
 * @param string $slug Plugin file or theme slug ('' for core)
 * @param string $current_version Current version
 * @param string $frozen_version Frozen version value
 * @param bool   $show_button_and_indicator Show button and indicator (default true)
 * @return void
 */
function wum_render_freeze_version_field( $type, $slug, $current_version, $frozen_version, $show_button_and_indicator = true ) {
	$safe_slug  = $type === 'core' ? 'core' : sanitize_html_class( $slug );
	$field_id   = 'wum_freeze_' . $safe_slug;
	$has_frozen = ! empty( $frozen_version );

	if ( $type === 'core' ) {
		$name = 'wum_version_freeze[core]';
	} elseif ( $type === 'plugin' ) {
		$name = 'wum_version_freeze[plugin][' . esc_attr( $slug ) . ']';
	} elseif ( $type === 'theme' ) {
		$name = 'wum_version_freeze[theme][' . esc_attr( $slug ) . ']';
	} else {
		$name = '';
	}
	?>
	<div class="wum-freeze-version-row" style="margin-bottom:8px;">
		<label for="<?php echo esc_attr( $field_id ); ?>" class="screen-reader-text">
			<?php esc_html_e( 'Freeze version', 'complete-updates-manager' ); ?>
		</label>
		<input type="text" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $frozen_version ); ?>" pattern="^[0-9.]+$" style="width:100px;" autocomplete="off" />
		<?php if ( $show_button_and_indicator ) : ?>
			<button type="button" class="button wum-copy-version" data-version="<?php echo esc_attr( $current_version ); ?>" data-target="#<?php echo esc_attr( $field_id ); ?>" title="<?php esc_attr_e( 'Copy current version', 'complete-updates-manager' ); ?>">&#8594;</button>
			<?php if ( $has_frozen ) : ?>
				<span class="wum-frozen-indicator" title="<?php esc_attr_e( 'Frozen version is set', 'complete-updates-manager' ); ?>" style="color:#dc3232;font-weight:bold;">&#9679;</span>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Validate an array of arguments
 *
 * @param array $args Arguments to validate
 * @return bool True if all arguments are non-empty
 */
function wum_validate_args( $args ) {
	if ( ! is_array( $args ) ) {
		return false;
	}

	foreach ( $args as $arg ) {
		if ( empty( $arg ) ) {
			return false;
		}
	}

	return true;
}
