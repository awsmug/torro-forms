<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Site;

if ( is_multisite() ) :

/**
 * @group db-objects
 * @group models
 * @group sites
 */
class Tests_Site extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	protected static $site_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_site_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'site' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public static function wpSetUpBeforeClass( $factory ) {
		self::$site_id = $factory->blog->create( array( 'domain' => 'wordpress.org', 'path' => '/' ) );
	}

	public static function wpTearDownAfterClass() {
		wpmu_delete_blog( self::$site_id, true );
	}

	public function test_setgetisset_property() {
		$site = new Site( self::$manager, get_site( self::$site_id ) );

		$this->assertTrue( isset( $site->id ) );
		$this->assertTrue( isset( $site->domain ) );
		$this->assertTrue( isset( $site->network_id ) );

		$this->assertEquals( self::$site_id, $site->id );
		$this->assertEquals( 'wordpress.org', $site->domain );
		$this->assertEquals( get_current_network_id(), $site->network_id );

		$site->id = 22;
		$this->assertEquals( self::$site_id, $site->id );

		$site->domain = 'wordpress.net';
		$this->assertEquals( 'wordpress.net', $site->domain );

		$site->network_id = 3;
		$this->assertEquals( 3, $site->network_id );
	}

	public function test_setgetisset_meta() {
		$site = new Site( self::$manager, get_site( self::$site_id ) );

		$this->assertTrue( isset( $site->home ) );
		$this->assertEquals( 'http://wordpress.org', $site->home );

		$this->assertFalse( isset( $site->random_value ) );
		$this->assertNull( $site->random_value );

		$value = 'foobar';
		$site->random_value = $value;
		$this->assertTrue( isset( $site->random_value ) );
		$this->assertSame( $value, $site->random_value );

		$site->random_value = null;
		$this->assertFalse( isset( $site->random_value ) );
	}
}

endif;
