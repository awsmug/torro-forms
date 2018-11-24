<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

/**
 * @group db-objects
 * @group managers
 * @group comments
 */
class Tests_Comment_Manager extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	protected static $post_id;
	protected static $comment_id;
	protected static $pingback_id;
	protected static $trackback_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_comment_manager_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'comment' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public static function wpSetUpBeforeClass( $factory ) {
		self::$post_id = $factory->post->create();
		self::$comment_id = $factory->comment->create( array( 'comment_post_ID' => self::$post_id, 'comment_approved' => '1' ) );
		self::$pingback_id = $factory->comment->create( array( 'comment_post_ID' => self::$post_id, 'comment_approved' => '1', 'comment_type' => 'pingback' ) );
		self::$trackback_id = $factory->comment->create( array( 'comment_post_ID' => self::$post_id, 'comment_approved' => '1', 'comment_type' => 'trackback' ) );
	}

	public static function wpTearDownAfterClass() {
		wp_delete_comment( self::$trackback_id, true );
		wp_delete_comment( self::$pingback_id, true );
		wp_delete_comment( self::$comment_id, true );
		wp_delete_post( self::$post_id, true );
	}

	public function test_create() {
		$comment = self::$manager->create();
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Comment', $comment );
	}

	public function test_get() {
		$comment = self::$manager->get( self::$comment_id );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Comment', $comment );
		$this->assertEquals( self::$comment_id, $comment->id );

		$result = self::$manager->get( 333333 );
		$this->assertNull( $result );
	}

	public function test_query() {
		$expected = array( self::$comment_id, self::$pingback_id, self::$trackback_id );
		$result = self::$manager->query( array(
			'fields'  => 'ids',
			'post_id' => self::$post_id,
			'orderby' => array( 'comment_ID' => 'ASC' ),
		) );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Comment_Collection', $result );
		$this->assertEquals( $expected, $result->get_raw() );
	}

	public function test_get_collection() {
		$comment_ids = array( 1, 7, 9 );

		$collection = self::$manager->get_collection( $comment_ids, 10, 'ids' );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Comment_Collection', $collection );
		$this->assertEquals( $comment_ids, $collection->get_raw() );
		$this->assertEquals( 10, $collection->get_total() );
	}

	public function test_add() {
		$args = array(
			'comment_author'       => 'John Doe',
			'comment_author_email' => 'johndoe@example.com',
			'comment_content'      => 'Hello there!',
			'comment_post_ID'      => self::$post_id,
			'comment_approved'     => '1',
		);
		$comment_id = self::$manager->add( $args );
		$this->assertInternalType( 'int', $comment_id );

		$wp_comment = get_comment( $comment_id );
		$this->assertInstanceOf( 'WP_Comment', $wp_comment );
	}

	public function test_update() {
		$new_content = 'A comment text.';
		$result = self::$manager->update( self::$comment_id, array( 'comment_content' => $new_content ) );
		$this->assertTrue( $result );

		$wp_comment = get_comment( self::$comment_id );
		$this->assertInstanceOf( 'WP_Comment', $wp_comment );
		$this->assertEquals( $new_content, $wp_comment->comment_content );

		$result = self::$manager->update( 333333, array( 'comment_content' => 'Hello.' ) );
		$this->assertFalse( $result );
	}

	public function test_delete() {
		$result = self::$manager->delete( self::$comment_id );
		$this->assertTrue( $result );

		$result = self::$manager->delete( 333333 );
		$this->assertFalse( $result );
	}

	public function test_fetch() {
		$expected = get_comment( self::$comment_id );
		$result = self::$manager->fetch( self::$comment_id );
		$this->assertInstanceOf( 'WP_Comment', $result );
		$this->assertEquals( $expected->comment_ID, $result->comment_ID );
		$this->assertEquals( $expected->comment_post_ID, $result->comment_post_ID );
	}

	public function test_add_to_cache() {
		$result = self::$manager->add_to_cache( 'randomkey1', 'foo' );
		$this->assertTrue( $result );

		$result = self::$manager->add_to_cache( 'randomkey1', 'bar' );
		$this->assertFalse( $result );

		$result = wp_cache_get( 'randomkey1', 'comment' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_delete_from_cache() {
		$result = self::$manager->delete_from_cache( 'randomkey2' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey2', 'foo', 'comment' );

		$result = self::$manager->delete_from_cache( 'randomkey2' );
		$this->assertTrue( $result );
	}

	public function test_get_from_cache() {
		$result = self::$manager->get_from_cache( 'randomkey3' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey3', 'foo', 'comment' );

		$result = self::$manager->get_from_cache( 'randomkey3' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_replace_in_cache() {
		$result = self::$manager->replace_in_cache( 'randomkey4', 'foo' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey4', 'foo', 'comment' );

		$result = self::$manager->replace_in_cache( 'randomkey4', 'bar' );
		$this->assertTrue( $result );
	}

	public function test_set_in_cache() {
		$result = self::$manager->set_in_cache( 'randomkey1', 'foo' );
		$this->assertTrue( $result );

		$result = self::$manager->set_in_cache( 'randomkey1', 'bar' );
		$this->assertTrue( $result );

		$result = wp_cache_get( 'randomkey1', 'comment' );
		$this->assertEquals( 'bar', $result );
	}

	public function test_add_meta() {
		$result = self::$manager->add_meta( self::$comment_id, 'key', 'foo' );
		$this->assertInternalType( 'int', $result );

		$result = get_comment_meta( self::$comment_id, 'key', true );
		$this->assertEquals( 'foo', $result );
	}

	public function test_update_meta() {
		$result = self::$manager->update_meta( self::$comment_id, 'key', 'foo' );
		$this->assertInternalType( 'int', $result );
	}

	public function test_delete_meta() {
		add_comment_meta( self::$comment_id, 'key', 'foo' );

		$result = self::$manager->delete_meta( self::$comment_id, 'key' );
		$this->assertTrue( $result );
	}

	public function test_get_meta() {
		add_comment_meta( self::$comment_id, 'key', 'foo' );

		$result = self::$manager->get_meta( self::$comment_id, 'key', true );
		$this->assertEquals( 'foo', $result );
	}

	public function test_meta_exists() {
		add_comment_meta( self::$comment_id, 'key', 'foo' );

		$result = self::$manager->meta_exists( self::$comment_id, 'key' );
		$this->assertTrue( $result );
	}

	public function test_delete_all_meta() {
		for ( $i = 1; $i <= 5; $i++ ) {
			add_comment_meta( self::$comment_id, 'key' . $i, 'foo' );
		}

		$result = self::$manager->delete_all_meta( self::$comment_id );
		$this->assertFalse( $result );
	}
}
