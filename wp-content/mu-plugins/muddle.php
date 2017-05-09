<?php
/**
 * Plugin Name: Muddle
 * Description: Must-use plugin directory loader.
 * Version: 0.3
 * Author: Luís Rodrigues
 * Author URI: https://github.com/goblindegook
 */

namespace goblindegook\WP\Muddle;

if ( ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) ) {
	return;
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Load must-use plugins.
 */
\add_action( 'muplugins_loaded', function () {

	$cache_key   = 'muddle_plugin_cache';
	$flush_cache = false;

	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$flush_cache = strpos( $_SERVER['REQUEST_URI'], '/wp-admin/plugins.php' ) !== false;
	}

	if ( $flush_cache ) {
		\delete_site_transient( $cache_key );
	}

	foreach ( get_mu_plugins( $cache_key ) as $plugin ) {
		include_once WPMU_PLUGIN_DIR . '/' . $plugin;
	}
} );

/**
 * Get list of must-use plugins residing in subdirectories.
 *
 * @param  string $cache_key Cache key to store plugins under.
 * @return array             List of plugin files to load.
 */
function get_mu_plugins( $cache_key = 'muddle_plugin_cache' ) {
	$plugins = \get_site_transient( $cache_key );

	if ( is_array( $plugins ) ) {
		foreach ( $plugins as $plugin ) {
			if ( ! is_readable( WPMU_PLUGIN_DIR . '/' . $plugin ) ) {
				$plugins = array();
				break;
			}
		}

		if ( ! empty( $plugins ) ) {
			return $plugins;
		}
	}

	$plugins = array();

	foreach ( array_keys( \get_plugins( '/../mu-plugins/' ) ) as $plugin ) {
		if ( dirname( $plugin ) !== '.' ) {
			$plugins[] = $plugin;
		}
	}

	\set_site_transient( $cache_key, $plugins );

	return $plugins;
}
