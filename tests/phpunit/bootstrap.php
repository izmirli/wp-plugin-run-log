<?php
/**
 * PHPUnit Bootstrap File for Run Log Plugin
 */

// Determine the tests directory.
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Check if the bootstrap exists inside the test directory.
if ( ! file_exists( $_tests_dir . '/includes/bootstrap.php' ) ) {
	echo "Could not find $_tests_dir/includes/bootstrap.php" . PHP_EOL;
	exit( 1 );
}

// Load PHPUnit Polyfills.
require_once dirname( dirname( __DIR__ ) ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __DIR__ ) ) . '/run-log.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
