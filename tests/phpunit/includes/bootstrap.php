<?php
/**
 * @package TorroForms
 * @subpackage Tests
 */

// disable xdebug backtrace
if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}

if ( false !== getenv( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', getenv( 'WP_PLUGIN_DIR' ) );
}

if ( empty( $GLOBALS['wp_tests_options']['active_plugins'] ) ) {
	$GLOBALS['wp_tests_options'] = array(
		'active_plugins' => array( 'torro-forms/torro-forms.php' ),
	);
}

function _manually_load_plugin() {
	require dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/torro-forms.php';
}

if ( false !== getenv( 'WP_TESTS_DIR' ) ) {
	$test_root    = rtrim( getenv( 'WP_TESTS_DIR' ), '/' );
	$_manual_load = true;
} elseif ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$test_root    = getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit';
	$_manual_load = true;
} elseif ( file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
	$test_root    = '/tmp/wordpress-tests-lib';
	$_manual_load = true;
} else {
	$test_root    = dirname( dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) ) ) . '/tests/phpunit';
	$_manual_load = false;
}

require_once $test_root . '/includes/functions.php';

if ( $_manual_load ) {
	define( 'TORRO_MANUAL_LOAD', true );

	tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
} else {
	define( 'TORRO_MANUAL_LOAD', false );
}

require $test_root . '/includes/bootstrap.php';

define( 'TORRO_TEST_ROOT', dirname( __DIR__ ) );

require_once TORRO_TEST_ROOT . '/includes/factory.php';
require_once TORRO_TEST_ROOT . '/includes/screen-mock.php';
require_once TORRO_TEST_ROOT . '/includes/testcase.php';

echo "Installing Torro Forms...\n";

activate_plugin( 'torro-forms/torro-forms.php' );
