<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Term_Collection;

/**
 * @group db-objects
 * @group collections
 * @group terms
 */
class Tests_Term_Collection extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_term_collection_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'term' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public function test_instantiation() {
		$term_ids = array( 1, 4, 2 );
		$collection = new Term_Collection( self::$manager, $term_ids );
		$this->assertSame( $term_ids, $collection->get_raw() );
	}
}
