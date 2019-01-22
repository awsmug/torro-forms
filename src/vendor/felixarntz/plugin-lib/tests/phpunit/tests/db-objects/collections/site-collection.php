<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Site_Collection;

if ( is_multisite() ) :

/**
 * @group db-objects
 * @group collections
 * @group sites
 */
class Tests_Site_Collection extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_site_collection_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'site' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public function test_instantiation() {
		$site_ids = array( 1, 4, 2 );
		$collection = new Site_Collection( self::$manager, $site_ids );
		$this->assertSame( $site_ids, $collection->get_raw() );
	}
}

endif;
