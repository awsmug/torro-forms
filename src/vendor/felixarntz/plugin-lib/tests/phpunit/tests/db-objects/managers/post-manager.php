<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

/**
 * @group db-objects
 * @group managers
 * @group posts
 */
class Tests_Post_Manager extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	protected static $post_id;
	protected static $page_id;
	protected static $attachment_id;

	protected $original_post_types;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_post_manager_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'post' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public static function wpSetUpBeforeClass( $factory ) {
		self::$post_id = $factory->post->create( array( 'post_type' => 'post' ) );
		self::$page_id = $factory->post->create( array( 'post_type' => 'page' ) );
		self::$attachment_id = $factory->attachment->create();
	}

	public static function wpTearDownAfterClass() {
		wp_delete_attachment( self::$attachment_id, true );
		wp_delete_post( self::$page_id, true );
		wp_delete_post( self::$post_id, true );
	}

	public function setUp() {
		parent::setUp();

		$this->original_post_types = array_keys( get_post_types() );
	}

	public function tearDown() {
		parent::tearDown();

		foreach ( get_post_types() as $slug => $obj ) {
			if ( in_array( $slug, $this->original_post_types, true ) ) {
				continue;
			}

			self::$manager->unregister_type( $slug );
		}

		$this->original_post_types = array();
	}

	public function test_create() {
		$post = self::$manager->create();
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Post', $post );
	}

	public function test_get() {
		$post = self::$manager->get( self::$post_id );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Post', $post );
		$this->assertEquals( self::$post_id, $post->id );

		$result = self::$manager->get( 333333 );
		$this->assertNull( $result );
	}

	public function test_query() {
		$expected = array( self::$page_id );
		$result = self::$manager->query( array(
			'fields'    => 'ids',
			'post_type' => 'page',
		) );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Post_Collection', $result );
		$this->assertEquals( $expected, $result->get_raw() );
	}

	public function test_get_collection() {
		$post_ids = array( 1, 7, 9 );

		$collection = self::$manager->get_collection( $post_ids, 10, 'ids' );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Post_Collection', $collection );
		$this->assertEquals( $post_ids, $collection->get_raw() );
		$this->assertEquals( 10, $collection->get_total() );
	}

	public function test_add() {
		$args = array(
			'post_type'         => 'page',
			'post_status'       => 'publish',
			'post_title'        => 'Hello World!',
		);
		$post_id = self::$manager->add( $args );
		$this->assertInternalType( 'int', $post_id );

		$wp_post = get_post( $post_id );
		$this->assertInstanceOf( 'WP_Post', $wp_post );
	}

	public function test_update() {
		$new_content = 'Some post content.';
		$result = self::$manager->update( self::$post_id, array( 'post_content' => $new_content ) );
		$this->assertTrue( $result );

		$wp_post = get_post( self::$post_id );
		$this->assertInstanceOf( 'WP_Post', $wp_post );
		$this->assertEquals( $new_content, $wp_post->post_content );

		$result = self::$manager->update( 333333, array( 'post_content' => 'Hello.' ) );
		$this->assertFalse( $result );
	}

	public function test_delete() {
		$result = self::$manager->delete( self::$post_id );
		$this->assertTrue( $result );

		$result = self::$manager->delete( 333333 );
		$this->assertFalse( $result );
	}

	public function test_fetch() {
		$expected = get_post( self::$post_id );
		$result = self::$manager->fetch( self::$post_id );
		$this->assertInstanceOf( 'WP_Post', $result );
		$this->assertEquals( $expected->ID, $result->ID );
		$this->assertEquals( $expected->post_type, $result->post_type );
	}

	public function test_add_to_cache() {
		$result = self::$manager->add_to_cache( 'randomkey1', 'foo' );
		$this->assertTrue( $result );

		$result = self::$manager->add_to_cache( 'randomkey1', 'bar' );
		$this->assertFalse( $result );

		$result = wp_cache_get( 'randomkey1', 'posts' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_delete_from_cache() {
		$result = self::$manager->delete_from_cache( 'randomkey2' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey2', 'foo', 'posts' );

		$result = self::$manager->delete_from_cache( 'randomkey2' );
		$this->assertTrue( $result );
	}

	public function test_get_from_cache() {
		$result = self::$manager->get_from_cache( 'randomkey3' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey3', 'foo', 'posts' );

		$result = self::$manager->get_from_cache( 'randomkey3' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_replace_in_cache() {
		$result = self::$manager->replace_in_cache( 'randomkey4', 'foo' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey4', 'foo', 'posts' );

		$result = self::$manager->replace_in_cache( 'randomkey4', 'bar' );
		$this->assertTrue( $result );
	}

	public function test_set_in_cache() {
		$result = self::$manager->set_in_cache( 'randomkey1', 'foo' );
		$this->assertTrue( $result );

		$result = self::$manager->set_in_cache( 'randomkey1', 'bar' );
		$this->assertTrue( $result );

		$result = wp_cache_get( 'randomkey1', 'posts' );
		$this->assertEquals( 'bar', $result );
	}

	public function test_add_meta() {
		$result = self::$manager->add_meta( self::$post_id, 'key', 'foo' );
		$this->assertInternalType( 'int', $result );

		$result = get_post_meta( self::$post_id, 'key', true );
		$this->assertEquals( 'foo', $result );
	}

	public function test_update_meta() {
		$result = self::$manager->update_meta( self::$post_id, 'key', 'foo' );
		$this->assertInternalType( 'int', $result );
	}

	public function test_delete_meta() {
		add_post_meta( self::$post_id, 'key', 'foo' );

		$result = self::$manager->delete_meta( self::$post_id, 'key' );
		$this->assertTrue( $result );
	}

	public function test_get_meta() {
		add_post_meta( self::$post_id, 'key', 'foo' );

		$result = self::$manager->get_meta( self::$post_id, 'key', true );
		$this->assertEquals( 'foo', $result );
	}

	public function test_meta_exists() {
		add_post_meta( self::$post_id, 'key', 'foo' );

		$result = self::$manager->meta_exists( self::$post_id, 'key' );
		$this->assertTrue( $result );
	}

	public function test_delete_all_meta() {
		for ( $i = 1; $i <= 5; $i++ ) {
			add_post_meta( self::$post_id, 'key' . $i, 'foo' );
		}

		$result = self::$manager->delete_all_meta( self::$post_id );
		$this->assertFalse( $result );
	}

	public function test_register_type() {
		$result = self::$manager->register_type( 'type1' );
		$this->assertTrue( $result );

		$result = get_post_type_object( 'type1' );
		$this->assertInstanceOf( 'WP_Post_Type', $result );
	}

	public function test_get_type() {
		register_post_type( 'type2' );

		$type = self::$manager->get_type( 'type2' );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Types\Post_Type', $type );
	}

	public function test_query_types() {
		$args = array( 'capability_type' => 'foo' );
		register_post_type( 'type3', $args );

		$types = self::$manager->query_types( array_merge( $args, array(
			'field' => 'slug',
		) ) );
		$this->assertEquals( array( 'type3' => 'type3' ), $types );
	}

	public function test_unregister_type() {
		register_post_type( 'type4' );

		$result = self::$manager->unregister_type( 'type4' );
		$this->assertTrue( $result );

		$result = self::$manager->unregister_type( 'invalid' );
		$this->assertFalse( $result );
	}
}
