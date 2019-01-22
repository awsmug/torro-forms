<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

// disable xdebug backtrace
if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}

if ( false !== getenv( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', getenv( 'WP_PLUGIN_DIR' ) );
}

$GLOBALS['wp_tests_options'] = array(
	'active_plugins' => array(
		'sample-plugin/sample-plugin.php',
	),
);

if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$test_root = getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit';
} elseif ( file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
	$test_root = '/tmp/wordpress-tests-lib';
} else {
	$test_root = __FILE__;
	for ( $i = 0; $i < 11; $i++ ) {
		$test_root = dirname( $test_root );
	}
	$test_root .= '/tests/phpunit';
}

define( 'LALPL_TESTS_DATA', dirname( dirname( __FILE__ ) ) . '/data/' );

require $test_root . '/includes/bootstrap.php';

require_once dirname( __FILE__ ) . '/factory.php';
require_once dirname( __FILE__ ) . '/testcase.php';
