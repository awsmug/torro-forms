<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

/**
 * @group db-objects
 * @group managers
 * @group users
 */
class Tests_User_Manager extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	protected static $administrator_id;
	protected static $author_id;
	protected static $subscriber_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_user_manager_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'user' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public static function wpSetUpBeforeClass( $factory ) {
		self::$administrator_id = $factory->user->create( array( 'role' => 'administrator' ) );
		self::$author_id = $factory->user->create( array( 'role' => 'author' ) );
		self::$subscriber_id = $factory->user->create( array( 'role' => 'subscriber' ) );
	}

	public static function wpTearDownAfterClass() {
		self::delete_user( self::$subscriber_id );
		self::delete_user( self::$author_id );
		self::delete_user( self::$administrator_id );
	}

	public function test_create() {
		$user = self::$manager->create();
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\User', $user );
	}

	public function test_get() {
		$user = self::$manager->get( self::$administrator_id );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\User', $user );
		$this->assertEquals( self::$administrator_id, $user->id );

		$result = self::$manager->get( 333333 );
		$this->assertNull( $result );
	}

	public function test_query() {
		$expected = array( self::$author_id );
		$result = self::$manager->query( array(
			'fields'  => 'ids',
			'role'    => 'author',
		) );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\User_Collection', $result );
		$this->assertEquals( $expected, $result->get_raw() );
	}

	public function test_get_collection() {
		$user_ids = array( 1, 7, 9 );

		$collection = self::$manager->get_collection( $user_ids, 10, 'ids' );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\User_Collection', $collection );
		$this->assertEquals( $user_ids, $collection->get_raw() );
		$this->assertEquals( 10, $collection->get_total() );
	}

	public function test_add() {
		$args = array(
			'user_login' => 'Bob',
			'user_pass'  => 'password',
			'user_email' => 'bob@example.com',
			'role'       => 'subscriber',
		);
		$user_id = self::$manager->add( $args );
		$this->assertInternalType( 'int', $user_id );

		$wp_user = get_userdata( $user_id );
		$this->assertInstanceOf( 'WP_User', $wp_user );
	}

	public function test_update() {
		$new_nickname = 'wapuu';
		$result = self::$manager->update( self::$administrator_id, array( 'nickname' => $new_nickname ) );
		$this->assertTrue( $result );

		$wp_user = get_userdata( self::$administrator_id );
		$this->assertInstanceOf( 'WP_User', $wp_user );
		$this->assertEquals( $new_nickname, $wp_user->nickname );

		$result = self::$manager->update( 333333, array( 'nickname' => 'hello' ) );
		$this->assertFalse( $result );
	}

	public function test_delete() {
		$result = self::$manager->delete( self::$administrator_id );
		$this->assertTrue( $result );

		$result = self::$manager->delete( 333333 );
		$this->assertFalse( $result );
	}

	public function test_fetch() {
		$expected = get_userdata( self::$administrator_id );
		$result = self::$manager->fetch( self::$administrator_id );
		$this->assertInstanceOf( 'WP_User', $result );
		$this->assertEquals( $expected->ID, $result->ID );
		$this->assertEquals( $expected->user_login, $result->user_login );
	}

	public function test_add_to_cache() {
		$result = self::$manager->add_to_cache( 'randomkey1', 'foo' );
		$this->assertTrue( $result );

		$result = self::$manager->add_to_cache( 'randomkey1', 'bar' );
		$this->assertFalse( $result );

		$result = wp_cache_get( 'randomkey1', 'users' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_delete_from_cache() {
		$result = self::$manager->delete_from_cache( 'randomkey2' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey2', 'foo', 'users' );

		$result = self::$manager->delete_from_cache( 'randomkey2' );
		$this->assertTrue( $result );
	}

	public function test_get_from_cache() {
		$result = self::$manager->get_from_cache( 'randomkey3' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey3', 'foo', 'users' );

		$result = self::$manager->get_from_cache( 'randomkey3' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_replace_in_cache() {
		$result = self::$manager->replace_in_cache( 'randomkey4', 'foo' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey4', 'foo', 'users' );

		$result = self::$manager->replace_in_cache( 'randomkey4', 'bar' );
		$this->assertTrue( $result );
	}

	public function test_set_in_cache() {
		$result = self::$manager->set_in_cache( 'randomkey1', 'foo' );
		$this->assertTrue( $result );

		$result = self::$manager->set_in_cache( 'randomkey1', 'bar' );
		$this->assertTrue( $result );

		$result = wp_cache_get( 'randomkey1', 'users' );
		$this->assertEquals( 'bar', $result );
	}

	public function test_add_meta() {
		$result = self::$manager->add_meta( self::$administrator_id, 'key', 'foo' );
		$this->assertInternalType( 'int', $result );

		$result = get_user_meta( self::$administrator_id, 'key', true );
		$this->assertEquals( 'foo', $result );
	}

	public function test_update_meta() {
		$result = self::$manager->update_meta( self::$administrator_id, 'key', 'foo' );
		$this->assertInternalType( 'int', $result );
	}

	public function test_delete_meta() {
		add_user_meta( self::$administrator_id, 'key', 'foo' );

		$result = self::$manager->delete_meta( self::$administrator_id, 'key' );
		$this->assertTrue( $result );
	}

	public function test_get_meta() {
		add_user_meta( self::$administrator_id, 'key', 'foo' );

		$result = self::$manager->get_meta( self::$administrator_id, 'key', true );
		$this->assertEquals( 'foo', $result );
	}

	public function test_meta_exists() {
		add_user_meta( self::$administrator_id, 'key', 'foo' );

		$result = self::$manager->meta_exists( self::$administrator_id, 'key' );
		$this->assertTrue( $result );
	}

	public function test_delete_all_meta() {
		for ( $i = 1; $i <= 5; $i++ ) {
			add_user_meta( self::$administrator_id, 'key' . $i, 'foo' );
		}

		$result = self::$manager->delete_all_meta( self::$administrator_id );
		$this->assertFalse( $result );
	}
}
