<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

/**
 * @group db-objects
 * @group managers
 * @group terms
 */
class Tests_Term_Manager extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	protected static $category_id;
	protected static $tag_id;

	protected $original_taxonomies;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_term_manager_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'term' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public static function wpSetUpBeforeClass( $factory ) {
		self::$category_id = $factory->term->create( array( 'taxonomy' => 'category' ) );
		self::$tag_id = $factory->term->create( array( 'taxonomy' => 'post_tag' ) );
	}

	public static function wpTearDownAfterClass() {
		wp_delete_term( self::$tag_id, 'post_tag' );
		wp_delete_term( self::$category_id, 'category' );
	}

	public function setUp() {
		parent::setUp();

		$this->original_taxonomies = array_keys( get_taxonomies() );
	}

	public function tearDown() {
		parent::tearDown();

		foreach ( get_taxonomies() as $slug => $obj ) {
			if ( in_array( $slug, $this->original_taxonomies, true ) ) {
				continue;
			}

			self::$manager->unregister_type( $slug );
		}

		$this->original_taxonomies = array();
	}

	public function test_create() {
		$term = self::$manager->create();
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Term', $term );
	}

	public function test_get() {
		$term = self::$manager->get( self::$category_id );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Term', $term );
		$this->assertEquals( self::$category_id, $term->id );

		$result = self::$manager->get( 333333 );
		$this->assertNull( $result );
	}

	public function test_query() {
		$expected = array( self::$tag_id );
		$result = self::$manager->query( array(
			'fields'     => 'ids',
			'taxonomy'   => 'post_tag',
			'hide_empty' => false,
		) );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Term_Collection', $result );
		$this->assertEquals( $expected, $result->get_raw() );
	}

	public function test_get_collection() {
		$term_ids = array( 1, 7, 9 );

		$collection = self::$manager->get_collection( $term_ids, 10, 'ids' );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Term_Collection', $collection );
		$this->assertEquals( $term_ids, $collection->get_raw() );
		$this->assertEquals( 10, $collection->get_total() );
	}

	public function test_add() {
		$args = array(
			'name'     => 'Hello',
			'taxonomy' => 'category',
		);
		$term_id = self::$manager->add( $args );
		$this->assertInternalType( 'int', $term_id );

		$wp_term = get_term( $term_id );
		$this->assertInstanceOf( 'WP_Term', $wp_term );

		$result = self::$manager->add( array(
			'taxonomy' => 'category',
		) );
		$this->assertFalse( $result );
	}

	public function test_update() {
		$new_description = 'Some term description.';
		$result = self::$manager->update( self::$category_id, array( 'description' => $new_description ) );
		$this->assertTrue( $result );

		$wp_term = get_term( self::$category_id );
		$this->assertInstanceOf( 'WP_Term', $wp_term );
		$this->assertEquals( $new_description, $wp_term->description );

		$result = self::$manager->update( 333333, array( 'description' => 'Hello.' ) );
		$this->assertFalse( $result );
	}

	public function test_delete() {
		$result = self::$manager->delete( self::$category_id );
		$this->assertTrue( $result );

		$result = self::$manager->delete( 333333 );
		$this->assertFalse( $result );
	}

	public function test_fetch() {
		$expected = get_term( self::$category_id );
		$result = self::$manager->fetch( self::$category_id );
		$this->assertInstanceOf( 'WP_Term', $result );
		$this->assertEquals( $expected->term_id, $result->term_id );
		$this->assertEquals( $expected->taxonomy, $result->taxonomy );
	}

	public function test_add_to_cache() {
		$result = self::$manager->add_to_cache( 'randomkey1', 'foo' );
		$this->assertTrue( $result );

		$result = self::$manager->add_to_cache( 'randomkey1', 'bar' );
		$this->assertFalse( $result );

		$result = wp_cache_get( 'randomkey1', 'terms' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_delete_from_cache() {
		$result = self::$manager->delete_from_cache( 'randomkey2' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey2', 'foo', 'terms' );

		$result = self::$manager->delete_from_cache( 'randomkey2' );
		$this->assertTrue( $result );
	}

	public function test_get_from_cache() {
		$result = self::$manager->get_from_cache( 'randomkey3' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey3', 'foo', 'terms' );

		$result = self::$manager->get_from_cache( 'randomkey3' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_replace_in_cache() {
		$result = self::$manager->replace_in_cache( 'randomkey4', 'foo' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey4', 'foo', 'terms' );

		$result = self::$manager->replace_in_cache( 'randomkey4', 'bar' );
		$this->assertTrue( $result );
	}

	public function test_set_in_cache() {
		$result = self::$manager->set_in_cache( 'randomkey1', 'foo' );
		$this->assertTrue( $result );

		$result = self::$manager->set_in_cache( 'randomkey1', 'bar' );
		$this->assertTrue( $result );

		$result = wp_cache_get( 'randomkey1', 'terms' );
		$this->assertEquals( 'bar', $result );
	}

	public function test_add_meta() {
		$result = self::$manager->add_meta( self::$category_id, 'key', 'foo' );
		$this->assertInternalType( 'int', $result );

		$result = get_term_meta( self::$category_id, 'key', true );
		$this->assertEquals( 'foo', $result );
	}

	public function test_update_meta() {
		$result = self::$manager->update_meta( self::$category_id, 'key', 'foo' );
		$this->assertInternalType( 'int', $result );
	}

	public function test_delete_meta() {
		add_term_meta( self::$category_id, 'key', 'foo' );

		$result = self::$manager->delete_meta( self::$category_id, 'key' );
		$this->assertTrue( $result );
	}

	public function test_get_meta() {
		add_term_meta( self::$category_id, 'key', 'foo' );

		$result = self::$manager->get_meta( self::$category_id, 'key', true );
		$this->assertEquals( 'foo', $result );
	}

	public function test_meta_exists() {
		add_term_meta( self::$category_id, 'key', 'foo' );

		$result = self::$manager->meta_exists( self::$category_id, 'key' );
		$this->assertTrue( $result );
	}

	public function test_delete_all_meta() {
		for ( $i = 1; $i <= 5; $i++ ) {
			add_term_meta( self::$category_id, 'key' . $i, 'foo' );
		}

		$result = self::$manager->delete_all_meta( self::$category_id );
		$this->assertFalse( $result );
	}

	public function test_register_type() {
		$result = self::$manager->register_type( 'type1' );
		$this->assertTrue( $result );

		$result = get_taxonomy( 'type1' );
		$this->assertInstanceOf( 'WP_Taxonomy', $result );
	}

	public function test_get_type() {
		register_taxonomy( 'type2', array() );

		$type = self::$manager->get_type( 'type2' );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Types\Taxonomy', $type );
	}

	public function test_query_types() {
		$args = array( 'capability_type' => 'foo' );
		register_taxonomy( 'type3', array(), $args );

		$types = self::$manager->query_types( array_merge( $args, array(
			'field' => 'slug',
		) ) );
		$this->assertEquals( array( 'type3' => 'type3' ), $types );
	}

	public function test_unregister_type() {
		register_taxonomy( 'type4', array() );

		$result = self::$manager->unregister_type( 'type4' );
		$this->assertTrue( $result );

		$result = self::$manager->unregister_type( 'invalid' );
		$this->assertFalse( $result );
	}
}
