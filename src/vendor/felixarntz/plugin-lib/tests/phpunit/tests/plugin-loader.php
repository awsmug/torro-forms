<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love_Plugin_Loader;
use WP_Error;

/**
 * @group general
 * @group plugin-loader
 */
class Tests_Plugin_Loader extends Unit_Test_Case {
	public function test_load() {
		$result = Leaves_And_Love_Plugin_Loader::load( 'SP_Main', __FILE__ );
		$this->assertFalse( $result );

		$result = Leaves_And_Love_Plugin_Loader::load( 'WP_Error', __FILE__ );
		$this->assertFalse( $result );

		require_once LALPL_TESTS_DATA . 'another-sample-class.php';
		require_once LALPL_TESTS_DATA . 'yet-another-sample-class.php';

		$result = Leaves_And_Love_Plugin_Loader::load( 'Another_Sample_Class', __FILE__ );
		$this->assertTrue( $result );

		$result = Leaves_And_Love_Plugin_Loader::load( 'Yet_Another_Sample_Class', __FILE__ );
		$this->assertFalse( $result );
	}

	public function test_get() {
		$result = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' );
		$this->assertInstanceOf( 'SP_Main', $result );

		$result = Leaves_And_Love_Plugin_Loader::get( 'WP_Error' );
		$this->assertNull( $result );

		require_once LALPL_TESTS_DATA . 'yet-another-sample-class.php';

		Leaves_And_Love_Plugin_Loader::load( 'Yet_Another_Sample_Class', __FILE__ );
		$result = Leaves_And_Love_Plugin_Loader::get( 'Yet_Another_Sample_Class' );
		$this->assertWPError( $result );
	}

	public function test_error_notice() {
		require_once LALPL_TESTS_DATA . 'yet-another-sample-class.php';

		Leaves_And_Love_Plugin_Loader::load( 'Yet_Another_Sample_Class', __FILE__ );

		ob_start();
		Leaves_And_Love_Plugin_Loader::error_notice();
		$output = ob_get_clean();

		$this->assertNotFalse( strpos( $output, 'Yet Another Sample Class cannot be initialized because your setup uses a PHP version older than 99.0.' ) );
	}
}
