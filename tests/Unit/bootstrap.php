<?php
/**
 * The following snippets uses `PLUGIN` to prefix
 * the constants and class names. You should replace
 * it with something that matches your plugin name.
 *
 * @package WCVendors
 */

// Load autoloader
require_once __DIR__ . '/../../vendor/yoast/wp-test-utils/src/BrainMonkey/bootstrap.php';
require_once __DIR__ . '/../../vendor/autoload.php';

$GLOBALS['wp_tests_options'] = [
    'active_plugins' => [ 'wc-vendors/wc-vendors.php' ],
];

// Load functions files. 
require_once __DIR__ . '/../../inc/vendor-functions.php';