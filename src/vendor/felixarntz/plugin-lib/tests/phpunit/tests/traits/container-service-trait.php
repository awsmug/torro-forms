<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\Error_Handler;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Options;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_Error_Handler;

/**
 * @group traits
 */
class Tests_Container_Service_Trait extends Unit_Test_Case {
	public function test_services() {
		require_once LALPL_TESTS_DATA . 'test-container-service-trait-class.php';

		$prefix = 'foo_bar_';

		$error_handler = new Error_Handler( $prefix, new Translations_Error_Handler() );

		$services = array(
			'cache'         => new Cache( $prefix ),
			'options'       => new Options( $prefix ),
			'error_handler' => $error_handler,
		);

		$service = new \Test_Container_Service_Trait_Class( $services );

		foreach ( $services as $name => $instance ) {
			$result = call_user_func( array( $service, $name ) );

			$this->assertInstanceOf( get_class( $instance ), $result );
		}

		$this->assertNull( $service->invalid() );
	}
}
