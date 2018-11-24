<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\User_Collection;

/**
 * @group db-objects
 * @group collections
 * @group users
 */
class Tests_User_Collection extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_user_collection_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'user' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public function test_instantiation() {
		$user_ids = array( 1, 4, 2 );
		$collection = new User_Collection( self::$manager, $user_ids );
		$this->assertSame( $user_ids, $collection->get_raw() );
	}
}
