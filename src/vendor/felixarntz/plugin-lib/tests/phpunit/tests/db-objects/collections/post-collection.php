<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Post_Collection;

/**
 * @group db-objects
 * @group collections
 * @group posts
 */
class Tests_Post_Collection extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_post_collection_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'post' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public function test_instantiation() {
		$post_ids = array( 1, 4, 2 );
		$collection = new Post_Collection( self::$manager, $post_ids );
		$this->assertSame( $post_ids, $collection->get_raw() );
	}
}
