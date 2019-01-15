<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Post;

/**
 * @group db-objects
 * @group models
 * @group posts
 */
class Tests_Post extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	protected static $post_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_post_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'post' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public static function wpSetUpBeforeClass( $factory ) {
		self::$post_id = $factory->post->create( array( 'post_type' => 'page' ) );
	}

	public static function wpTearDownAfterClass() {
		wp_delete_post( self::$post_id, true );
	}

	public function test_setgetisset_property() {
		$post = new Post( self::$manager, get_post( self::$post_id ) );

		$this->assertTrue( isset( $post->id ) );
		$this->assertTrue( isset( $post->type ) );
		$this->assertTrue( isset( $post->post_type ) );
		$this->assertTrue( isset( $post->comment_count ) );
		$this->assertFalse( isset( $post->filter ) );

		$this->assertEquals( self::$post_id, $post->id );
		$this->assertEquals( $post->post_type, $post->type );
		$this->assertEquals( 0, $post->comment_count );

		$post->id = 22;
		$this->assertEquals( self::$post_id, $post->id );

		$post->type = 'post';
		$this->assertEquals( 'post', $post->post_type );

		$post->comment_count = 3;
		$this->assertEquals( 0, $post->comment_count );
	}

	public function test_setgetisset_meta() {
		$post = new Post( self::$manager, get_post( self::$post_id ) );

		$this->assertFalse( isset( $post->random_value ) );
		$this->assertNull( $post->random_value );

		$value = 'foobar';
		$post->random_value = $value;
		$this->assertTrue( isset( $post->random_value ) );
		$this->assertSame( $value, $post->random_value );

		$post->random_value = null;
		$this->assertFalse( isset( $post->random_value ) );
	}
}
