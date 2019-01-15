<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Models\User;

/**
 * @group db-objects
 * @group models
 * @group users
 */
class Tests_User extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	protected static $user_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_user_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'user' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( array( 'user_login' => 'john', 'role' => 'administrator', 'description' => 'A user.' ) );
	}

	public static function wpTearDownAfterClass() {
		self::delete_user( self::$user_id );
	}

	public function test_setgetisset_property() {
		$user = new User( self::$manager, get_userdata( self::$user_id ) );

		$this->assertTrue( isset( $user->id ) );
		$this->assertTrue( isset( $user->login ) );
		$this->assertTrue( isset( $user->user_login ) );
		$this->assertTrue( isset( $user->description ) );
		$this->assertFalse( isset( $user->filter ) );

		$this->assertEquals( self::$user_id, $user->id );
		$this->assertEquals( $user->user_login, $user->login );
		$this->assertEquals( 'A user.', $user->description );

		$user->id = 22;
		$this->assertEquals( self::$user_id, $user->id );

		$user->login = 'bob';
		$this->assertEquals( 'bob', $user->user_login );

		$user->description = 'A new description.';
		$this->assertEquals( 'A new description.', $user->description );
	}

	public function test_setgetisset_meta() {
		$user = new User( self::$manager, get_userdata( self::$user_id ) );

		$this->assertTrue( isset( $user->use_ssl ) );
		$this->assertEquals( 0, $user->use_ssl );

		$this->assertFalse( isset( $user->random_value ) );
		$this->assertNull( $user->random_value );

		$value = 'foobar';
		$user->random_value = $value;
		$this->assertTrue( isset( $user->random_value ) );
		$this->assertSame( $value, $user->random_value );

		$user->random_value = null;
		$this->assertFalse( isset( $user->random_value ) );
	}
}
