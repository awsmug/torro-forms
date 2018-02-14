<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

/**
 * @group general
 * @group service
 */
class Tests_Service extends Unit_Test_Case {
	public function test_prefix() {
		require_once LALPL_TESTS_DATA . 'test-service-class.php';

		$prefix = 'foo_bar_';

		$service = new \Test_Service_Class( $prefix );
		$this->assertSame( $prefix, $service->get_prefix() );
	}
}
