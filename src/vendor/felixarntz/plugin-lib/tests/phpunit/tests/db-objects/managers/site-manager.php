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
 * @group sites
 */
class Tests_Site_Manager extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	protected static $site1;
	protected static $site2;
	protected static $site3;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_site_manager_';

		self::$manager = self::setUpCoreManager( self::$prefix, 'site' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$manager = null;
	}

	public static function wpSetUpBeforeClass( $factory ) {
		self::$site1 = $factory->blog->create( array( 'domain' => 'wordpress.org', 'path' => '/' ) );
		self::$site2 = $factory->blog->create( array( 'domain' => 'wordpress.org', 'path' => '/blog/' ) );
		self::$site3 = $factory->blog->create( array( 'domain' => 'wordpress.net', 'path' => '/' ) );
	}

	public static function wpTearDownAfterClass() {
		wpmu_delete_blog( self::$site3, true );
		wpmu_delete_blog( self::$site2, true );
		wpmu_delete_blog( self::$site1, true );
	}

	public function test_create() {
		$site = self::$manager->create();
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Site', $site );
	}

	public function test_get() {
		$site = self::$manager->get( self::$site1 );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Site', $site );
		$this->assertEquals( self::$site1, $site->id );

		$result = self::$manager->get( 333333 );
		$this->assertNull( $result );
	}

	public function test_query() {
		$expected = array( self::$site1, self::$site2 );
		$result = self::$manager->query( array(
			'fields'  => 'ids',
			'domain'  => 'wordpress.org',
			'orderby' => array( 'id' => 'ASC' ),
		) );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Site_Collection', $result );
		$this->assertEquals( $expected, $result->get_raw() );
	}

	public function test_get_collection() {
		$site_ids = array( 1, 7, 9 );

		$collection = self::$manager->get_collection( $site_ids, 10, 'ids' );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Site_Collection', $collection );
		$this->assertEquals( $site_ids, $collection->get_raw() );
		$this->assertEquals( 10, $collection->get_total() );
	}

	public function test_add() {
		global $wpdb;

		// Ignore MySQL reopen table errors.
		$suppress_errors = $wpdb->suppress_errors( true );
		$args = array(
			'domain'       => 'example.com',
			'path'         => '/',
			'registered'   => current_time( 'mysql' ),
			'last_updated' => current_time( 'mysql' ),
		);
		$site_id = self::$manager->add( $args );
		$wpdb->suppress_errors( $suppress_errors );
		$this->assertInternalType( 'int', $site_id );

		$wp_site = get_site( $site_id );
		$this->assertInstanceOf( 'WP_Site', $wp_site );

		$result = self::$manager->add( array() );
		$this->assertFalse( $result );
	}

	public function test_update() {
		$new_path = '/subsite/';
		$result = self::$manager->update( self::$site1, array( 'path' => $new_path ) );
		$this->assertTrue( $result );

		$wp_site = get_site( self::$site1 );
		$this->assertInstanceOf( 'WP_Site', $wp_site );
		$this->assertEquals( $new_path, $wp_site->path );

		$result = self::$manager->update( 333333, array( 'path' => '/hello/' ) );
		$this->assertFalse( $result );
	}

	public function test_delete() {
		$result = self::$manager->delete( self::$site1 );
		$this->assertTrue( $result );

		$result = self::$manager->delete( 333333 );
		$this->assertFalse( $result );
	}

	public function test_fetch() {
		$expected = get_site( self::$site1 );
		$result = self::$manager->fetch( self::$site1 );
		$this->assertInstanceOf( 'WP_Site', $result );
		$this->assertEquals( $expected->id, $result->id );
		$this->assertEquals( $expected->domain, $result->domain );
	}

	public function test_add_to_cache() {
		$result = self::$manager->add_to_cache( 'randomkey1', 'foo' );
		$this->assertTrue( $result );

		$result = self::$manager->add_to_cache( 'randomkey1', 'bar' );
		$this->assertFalse( $result );

		$result = wp_cache_get( 'randomkey1', 'sites' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_delete_from_cache() {
		$result = self::$manager->delete_from_cache( 'randomkey2' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey2', 'foo', 'sites' );

		$result = self::$manager->delete_from_cache( 'randomkey2' );
		$this->assertTrue( $result );
	}

	public function test_get_from_cache() {
		$result = self::$manager->get_from_cache( 'randomkey3' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey3', 'foo', 'sites' );

		$result = self::$manager->get_from_cache( 'randomkey3' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_replace_in_cache() {
		$result = self::$manager->replace_in_cache( 'randomkey4', 'foo' );
		$this->assertFalse( $result );

		wp_cache_add( 'randomkey4', 'foo', 'sites' );

		$result = self::$manager->replace_in_cache( 'randomkey4', 'bar' );
		$this->assertTrue( $result );
	}

	public function test_set_in_cache() {
		$result = self::$manager->set_in_cache( 'randomkey1', 'foo' );
		$this->assertTrue( $result );

		$result = self::$manager->set_in_cache( 'randomkey1', 'bar' );
		$this->assertTrue( $result );

		$result = wp_cache_get( 'randomkey1', 'sites' );
		$this->assertEquals( 'bar', $result );
	}

	public function test_add_meta() {
		$result = self::$manager->add_meta( self::$site1, 'key', 'foo' );
		$this->assertInternalType( 'int', $result );

		$result = get_blog_option( self::$site1, 'key' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_update_meta() {
		$result = self::$manager->update_meta( self::$site1, 'key', 'foo' );
		$this->assertInternalType( 'int', $result );
	}

	public function test_delete_meta() {
		add_blog_option( self::$site1, 'key', 'foo' );

		$result = self::$manager->delete_meta( self::$site1, 'key' );
		$this->assertTrue( $result );
	}

	public function test_get_meta() {
		add_blog_option( self::$site1, 'key', 'foo' );

		$result = self::$manager->get_meta( self::$site1, 'key', true );
		$this->assertEquals( 'foo', $result );
	}

	public function test_meta_exists() {
		add_blog_option( self::$site1, 'key', 'foo' );

		$result = self::$manager->meta_exists( self::$site1, 'key' );
		$this->assertTrue( $result );
	}

	public function test_delete_all_meta() {
		for ( $i = 1; $i <= 5; $i++ ) {
			add_blog_option( self::$site1, 'key' . $i, 'foo' );
		}

		$result = self::$manager->delete_all_meta( self::$site1 );
		$this->assertFalse( $result );
	}
}

endif;
