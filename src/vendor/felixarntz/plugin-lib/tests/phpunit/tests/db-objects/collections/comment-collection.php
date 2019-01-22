<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Comment_Collection;

/**
 * @group db-objects
 * @group collections
 * @group comments
 */
class Tests_Comment_Collection extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_comment_collection_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'comment' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public function test_instantiation() {
		$comment_ids = array( 1, 4, 2 );
		$collection = new Comment_Collection( self::$manager, $comment_ids );
		$this->assertSame( $comment_ids, $collection->get_raw() );
	}
}
