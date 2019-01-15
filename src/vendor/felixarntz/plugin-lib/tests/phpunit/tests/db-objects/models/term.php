<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Term;

/**
 * @group db-objects
 * @group models
 * @group terms
 */
class Tests_Term extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	protected static $term_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_term_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'term' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public static function wpSetUpBeforeClass( $factory ) {
		self::$term_id = $factory->term->create( array( 'taxonomy' => 'post_tag' ) );
	}

	public static function wpTearDownAfterClass() {
		wp_delete_term( self::$term_id, 'post_tag' );
	}

	public function test_setgetisset_property() {
		$term = new Term( self::$manager, get_term( self::$term_id ) );

		$this->assertTrue( isset( $term->id ) );
		$this->assertTrue( isset( $term->taxonomy ) );
		$this->assertTrue( isset( $term->group ) );
		$this->assertTrue( isset( $term->term_group ) );
		$this->assertTrue( isset( $term->count ) );
		$this->assertFalse( isset( $term->filter ) );

		$this->assertEquals( self::$term_id, $term->id );
		$this->assertEquals( 'post_tag', $term->taxonomy );
		$this->assertEquals( $term->term_group, $term->group );
		$this->assertEquals( 0, $term->count );

		$term->id = 22;
		$this->assertEquals( self::$term_id, $term->id );

		$term->taxonomy = 'category';
		$this->assertEquals( 'category', $term->taxonomy );

		$term->count = 3;
		$this->assertEquals( 0, $term->count );
	}

	public function test_setgetisset_meta() {
		$term = new Term( self::$manager, get_term( self::$term_id ) );

		$this->assertFalse( isset( $term->random_value ) );
		$this->assertNull( $term->random_value );

		$value = 'foobar';
		$term->random_value = $value;
		$this->assertTrue( isset( $term->random_value ) );
		$this->assertSame( $value, $term->random_value );

		$term->random_value = null;
		$this->assertFalse( isset( $term->random_value ) );
	}
}
