<?php
/**
 * PHPUnit bootstrap file
 *
 * @package WCVendors
 */

use Yoast\WPTestUtils\WPIntegration;

if ( getenv( 'WP_PLUGIN_DIR' ) !== false ) {
    define( 'WP_PLUGIN_DIR', getenv( 'WP_PLUGIN_DIR' ) );
}

$GLOBALS['wp_tests_options'] = [
    'active_plugins' => [ 'wc-vendors/wc-vendors.php' ],
];

require_once dirname( dirname( __DIR__ ) ) . '/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __DIR__ ) ) . '/wc-vendors.php';
}

/**
 * Filter the plugins URL to pretend the plugin is installed in the test environment.
 *
 * @param string $url    The complete URL to the plugins directory including scheme and path.
 * @param string $path   Path relative to the URL to the plugins directory. Blank string
 *                       if no path is specified.
 * @param string $plugin The plugin file path to be relative to. Blank string if no plugin
 *                       is specified.
 *
 * @return string
 */
function _plugins_url( $url, $path, $plugin ) {
	$plugin_dir = dirname( dirname( __DIR__ ) );
	if ( $plugin === $plugin_dir . '/wc-vendors.php' ) {
		$url = str_replace( dirname( $plugin_dir ), '', $url );
	}

	return $url;
}

WPIntegration\bootstrap_it();
