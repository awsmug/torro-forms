<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

if ( is_multisite() ) :

/**
 * @group db-objects
 * @group managers
 * @group networks
 */
class Tests_Network_Manager extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	protected static $network1;
	protected static $network2;
	protected static $network3;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_network_manager_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'network' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public static function wpSetUpBeforeClass( $factory ) {
		self::$network1 = $factory->network->create( array( 'domain' => 'wordpress.org', 'path' => '/' ) );
		self::$network2 = $factory->network->create( array( 'domain' => 'make.wordpress.org', 'path' => '/' ) );
		self::$network3 = $factory->network->create( array( 'domain' => 'wordpress.net', 'path' => '/' ) );
	}

	public static function wpTearDownAfterClass() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->sitemeta} WHERE site_id = %d", self::$network3 ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->site} WHERE id= %d", self::$network3 ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->sitemeta} WHERE site_id = %d", self::$network2 ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->site} WHERE id= %d", self::$network2 ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->sitemeta} WHERE site_id = %d", self::$network1 ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->site} WHERE id= %d", self::$network1 ) );
	}

	public function test_create() {
		$network = self::$manager->create();
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Network', $network );
	}

	public function test_get() {
		$network = self::$manager->get( self::$network1 );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Network', $network );
		$this->assertEquals( self::$network1, $network->id );

		$result = self::$manager->get( 333333 );
		$this->assertNull( $result );
	}

	public function test_query() {
		$expected = array( self::$network1, self::$network2 );
		$result = self::$manager->query( array(
			'fields'  => 'ids',
			'search'  => 'wordpress.org',
			'orderby' => array( 'id' => 'ASC' ),
		) );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Network_Collection', $result );
		$this->assertEquals( $expected, $result->get_raw() );
	}

	public function test_get_collection() {
		$network_ids = array( 1, 7, 9 );

		$collection = self::$manager->get_collection( $network_ids, 10, 'ids' );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Network_Collection', $collection );
		$this->assertEquals( $network_ids, $collection->get_raw() );
		$this->assertEquals( 10, $collection->get_total() );
	}

	public function test_add() {
		$args = array(
			'domain'       => 'example.com',
			'path'         => '/',
		);
		$network_id = self::$manager->add( $args );
		if ( function_exists( 'add_network' ) ) {
			$this->assertInternalType( 'int', $network_id );

			$wp_network = get_network( $network_id );
			$this->assertInstanceOf( 'WP_Network', $wp_network );
		} else {
			$this->assertFalse( $network_id );
		}

		$result = self::$manager->add( array() );
		$this->assertFalse( $result );
	}

	public function test_update() {
		$new_domain = 'example.org';
		$result = self::$manager->update( self::$network1, array( 'domain' => $new_domain ) );
		if ( function_exists( 'update_network' ) ) {
			$this->assertTrue( $result );

			$wp_network = get_network( self::$network1 );
			$this->assertInstanceOf( 'WP_Network', $wp_network );
			$this->assertEquals( $new_domain, $wp_network->domain );
		} else {
			$this->assertFalse( $result );
		}

		$result = self::$manager->update( 333333, array( 'domain' => 'www.hello.org' ) );
		$this->assertFalse( $result );
	}

	public function test_delete() {
		$result = self::$manager->delete( self::$network1 );
		if ( function_exists( 'delete_network' ) ) {
			$this->assertTrue( $result );
		} else {
			$this->assertFalse( $result );
		}

		$result = self::$manager->delete( 333333 );
		$this->assertFalse( $result );
	}

	public function test_fetch() {
		$expected = get_network( self::$network1 );
		$result = self::$manager->fetch( self::$network1 );
		$this->assertInstanceOf( 'WP_Network', $result );
		$this->assertEquals( $expected->id, $result->id );
		$this->assertEquals( $expected->domain, $result->domain );
	}

	public function test_add_to_cache() {
		$result = self::$manager->add_to_cache( 'randomkey1', 'foo' );
		$this->assertTrue( $result );

		$result = self::$manager->add_to_cache( 'randomkey1', 'bar' );
		$this->assertFalse( $result );

		$result = wp_cache_get( 'randomkey1', 'networks' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_delete_from_cache() {
		$result = self::$manager->delete_from_cache( 'randomkey2' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey2', 'foo', 'networks' );

		$result = self::$manager->delete_from_cache( 'randomkey2' );
		$this->assertTrue( $result );
	}

	public function test_get_from_cache() {
		$result = self::$manager->get_from_cache( 'randomkey3' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey3', 'foo', 'networks' );

		$result = self::$manager->get_from_cache( 'randomkey3' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_replace_in_cache() {
		$result = self::$manager->replace_in_cache( 'randomkey4', 'foo' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey4', 'foo', 'networks' );

		$result = self::$manager->replace_in_cache( 'randomkey4', 'bar' );
		$this->assertTrue( $result );
	}

	public function test_set_in_cache() {
		$result = self::$manager->set_in_cache( 'randomkey1', 'foo' );
		$this->assertTrue( $result );

		$result = self::$manager->set_in_cache( 'randomkey1', 'bar' );
		$this->assertTrue( $result );

		$result = wp_cache_get( 'randomkey1', 'networks' );
		$this->assertEquals( 'bar', $result );
	}

	public function test_add_meta() {
		$result = self::$manager->add_meta( self::$network1, 'key', 'foo' );
		$this->assertInternalType( 'int', $result );

		$result = get_network_option( self::$network1, 'key' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_update_meta() {
		$result = self::$manager->update_meta( self::$network1, 'key', 'foo' );
		$this->assertInternalType( 'int', $result );
	}

	public function test_delete_meta() {
		add_network_option( self::$network1, 'key', 'foo' );

		$result = self::$manager->delete_meta( self::$network1, 'key' );
		$this->assertTrue( $result );
	}

	public function test_get_meta() {
		add_network_option( self::$network1, 'key', 'foo' );

		$result = self::$manager->get_meta( self::$network1, 'key', true );
		$this->assertEquals( 'foo', $result );
	}

	public function test_meta_exists() {
		add_network_option( self::$network1, 'key', 'foo' );

		$result = self::$manager->meta_exists( self::$network1, 'key' );
		$this->assertTrue( $result );
	}

	public function test_delete_all_meta() {
		for ( $i = 1; $i <= 5; $i++ ) {
			add_network_option( self::$network1, 'key' . $i, 'foo' );
		}

		$result = self::$manager->delete_all_meta( self::$network1 );
		$this->assertFalse( $result );
	}
}

endif;
