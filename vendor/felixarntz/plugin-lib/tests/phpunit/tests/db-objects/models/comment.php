<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Comment;

/**
 * @group db-objects
 * @group models
 * @group comments
 */
class Tests_Comment extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	protected static $post_id;
	protected static $comment_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_comment_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'comment' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public static function wpSetUpBeforeClass( $factory ) {
		self::$post_id = $factory->post->create();
		self::$comment_id = $factory->comment->create( array( 'comment_post_ID' => self::$post_id, 'comment_approved' => '1' ) );
	}

	public static function wpTearDownAfterClass() {
		wp_delete_comment( self::$comment_id, true );
		wp_delete_post( self::$post_id, true );
	}

	public function test_setgetisset_property() {
		$comment = new Comment( self::$manager, get_comment( self::$comment_id ) );

		$this->assertTrue( isset( $comment->id ) );
		$this->assertTrue( isset( $comment->post_id ) );
		$this->assertTrue( isset( $comment->approved ) );
		$this->assertTrue( isset( $comment->comment_approved ) );

		$this->assertEquals( self::$comment_id, $comment->id );
		$this->assertEquals( self::$post_id, $comment->post_id );
		$this->assertEquals( $comment->comment_approved, $comment->approved );

		$comment->id = 22;
		$this->assertEquals( self::$comment_id, $comment->id );

		$comment->post_id = 44;
		$this->assertEquals( 44, $comment->post_id );

		$comment->approved = '0';
		$this->assertEquals( '0', $comment->comment_approved );
	}

	public function test_setgetisset_meta() {
		$comment = new Comment( self::$manager, get_comment( self::$comment_id ) );

		$this->assertFalse( isset( $comment->random_value ) );
		$this->assertNull( $comment->random_value );

		$value = 'foobar';
		$comment->random_value = $value;
		$this->assertTrue( isset( $comment->random_value ) );
		$this->assertSame( $value, $comment->random_value );

		$comment->random_value = null;
		$this->assertFalse( isset( $comment->random_value ) );
	}
}
