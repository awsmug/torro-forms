<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Network;

if ( is_multisite() ) :

/**
 * @group db-objects
 * @group models
 * @group networks
 */
class Tests_Network extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	protected static $network;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_network_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'network' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public static function wpSetUpBeforeClass( $factory ) {
		self::$network = $factory->network->create( array( 'domain' => 'wordpress.org', 'path' => '/' ) );
		update_network_option( self::$network, 'site_name', 'WordPress' );
	}

	public static function wpTearDownAfterClass() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->sitemeta} WHERE site_id = %d", self::$network ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->site} WHERE id= %d", self::$network ) );
	}

	public function test_setgetisset_property() {
		$network = new Network( self::$manager, get_network( self::$network ) );

		$this->assertTrue( isset( $network->id ) );
		$this->assertTrue( isset( $network->domain ) );
		$this->assertTrue( isset( $network->site_id ) );
		$this->assertTrue( isset( $network->cookie_domain ) );

		$this->assertEquals( self::$network, $network->id );
		$this->assertEquals( 'wordpress.org', $network->domain );
		$this->assertEquals( null, $network->site_id );
		$this->assertEquals( 'wordpress.org', $network->cookie_domain );

		$network->id = 22;
		$this->assertEquals( self::$network, $network->id );

		$network->domain = 'wordpress.net';
		$this->assertEquals( 'wordpress.net', $network->domain );

		$network->site_id = 3;
		$this->assertEquals( null, $network->site_id );

		$network->cookie_domain = 'example.com';
		$this->assertEquals( 'wordpress.net', $network->cookie_domain );
	}

	public function test_setgetisset_meta() {
		$network = new Network( self::$manager, get_network( self::$network ) );

		$this->assertTrue( isset( $network->site_name ) );
		$this->assertEquals( 'WordPress', $network->site_name );

		$this->assertFalse( isset( $network->random_value ) );
		$this->assertNull( $network->random_value );

		$value = 'foobar';
		$network->random_value = $value;
		$this->assertTrue( isset( $network->random_value ) );
		$this->assertSame( $value, $network->random_value );

		$network->random_value = null;
		$this->assertFalse( isset( $network->random_value ) );
	}
}

endif;
