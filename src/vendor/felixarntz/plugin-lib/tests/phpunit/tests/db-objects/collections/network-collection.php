<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Network_Collection;

if ( is_multisite() ) :

/**
 * @group db-objects
 * @group collections
 * @group networks
 */
class Tests_Network_Collection extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_network_collection_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'network' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public function test_instantiation() {
		$network_ids = array( 1, 4, 2 );
		$collection = new Network_Collection( self::$manager, $network_ids );
		$this->assertSame( $network_ids, $collection->get_raw() );
	}
}

endif;
