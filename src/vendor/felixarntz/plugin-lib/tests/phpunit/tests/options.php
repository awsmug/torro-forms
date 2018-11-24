<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\Options;

/**
 * @group general
 * @group options
 */
class Tests_Options extends Unit_Test_Case {
	protected static $prefix;
	protected static $options;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_options_';

		$stored_in_network = array( 'db_version', 'non_existing_global_option' );
		for ( $i = 1; $i <= 10; $i++ ) {
			$stored_in_network[] = 'globalkey' . $i;
		}

		self::$options = new Options( self::$prefix );
		self::$options->store_in_network( $stored_in_network );

		self::setUpHooks( self::$options );
	}

	public static function tearDownAfterClass() {
		self::tearDownHooks( self::$options );
	}

	public function test_get() {
		$version = '1000';
		if ( is_multisite() ) {
			update_network_option( null, self::$prefix . 'db_version', array( get_current_blog_id() => $version ) );
		} else {
			update_option( self::$prefix . 'db_version', $version );
		}
		$result = self::$options->get( 'db_version' );
		$this->assertSame( $version, $result );

		$val = 'some string';
		update_option( self::$prefix . 'custom_db_option', $val );
		$result = self::$options->get( 'custom_db_option' );
		$this->assertSame( $val, $result );

		$default = 3;
		$result = self::$options->get( 'non_existing_option', $default );
		$this->assertSame( $default, $result );

		$result = self::$options->get( 'non_existing_option' );
		$this->assertFalse( $result );

		$default = 3;
		$result = self::$options->get( 'non_existing_global_option', $default );
		$this->assertSame( $default, $result );
	}

	public function test_add() {
		$result = self::$options->add( 'localkey1', 33 );
		$this->assertTrue( $result );

		$result = self::$options->add( 'localkey1', 34 );
		$this->assertFalse( $result );

		$result = get_option( self::$prefix . 'localkey1' );
		$this->assertEquals( 33, $result );

		$result = self::$options->add( 'globalkey1', 35 );
		$this->assertTrue( $result );

		$result = self::$options->add( 'globalkey1', 36 );
		$this->assertFalse( $result );
	}

	public function test_update() {
		$result = self::$options->update( 'localkey2', 11 );
		$this->assertTrue( $result );

		$result = self::$options->update( 'localkey2', 12 );
		$this->assertTrue( $result );

		$result = get_option( self::$prefix . 'localkey2' );
		$this->assertEquals( 12, $result );

		$result = self::$options->update( 'globalkey2', 13 );
		$this->assertTrue( $result );

		$result = self::$options->update( 'globalkey2', 14 );
		$this->assertTrue( $result );
	}

	public function test_delete() {
		$result = self::$options->delete( 'non_existing_option' );
		$this->assertFalse( $result );

		update_option( self::$prefix . 'localkey3', 'hello' );
		$result = self::$options->delete( 'localkey3' );
		$this->assertTrue( $result );

		$result = get_option( self::$prefix . 'localkey3' );
		$this->assertFalse( $result );

		$result = self::$options->delete( 'globalkey3' );
		$this->assertFalse( $result );

		if ( is_multisite() ) {
			update_network_option( null, self::$prefix . 'globalkey3', array( get_current_blog_id() => 'hello' ) );
		} else {
			update_option( self::$prefix . 'globalkey3', 'hello' );
		}
		$result = self::$options->delete( 'globalkey3' );
		$this->assertTrue( $result );

		if ( is_multisite() ) {
			update_network_option( null, self::$prefix . 'globalkey3', array( get_current_blog_id() => 'hello', 3335 => 'this is gonna remain' ) );
		} else {
			update_option( self::$prefix . 'globalkey3', 'hello' );
		}
		$result = self::$options->delete( 'globalkey3' );
		$this->assertTrue( $result );
	}

	public function test_get_for_all_sites() {
		self::$options->update( 'localkey4', 'haha' );
		$result = self::$options->get_for_all_sites( 'localkey4' );
		$this->assertEquals( array( get_current_blog_id() => 'haha' ), $result );

		self::$options->update( 'globalkey4', 'hoho' );
		$result = self::$options->get_for_all_sites( 'globalkey4' );
		$this->assertEquals( array( get_current_blog_id() => 'hoho' ), $result );
	}

	public function test_get_networks_with_option() {
		self::$options->update( 'localkey5', 'hihi' );
		$result = self::$options->get_networks_with_option( 'localkey5' );
		$this->assertEquals( array( 1 ), $result );

		$result = self::$options->get_networks_with_option( 'non_existing_option' );
		$this->assertEmpty( $result );

		self::$options->update( 'globalkey5', 'hehe' );
		$result = self::$options->get_networks_with_option( 'globalkey5' );
		$this->assertEquals( array( 1 ), $result );

		$result = self::$options->get_networks_with_option( 'non_existing_global_option' );
		$this->assertEmpty( $result );
	}

	public function test_flush() {
		$result = self::$options->flush( 'non_existing_option' );
		$this->assertFalse( $result );

		update_option( self::$prefix . 'localkey6', 'hello' );
		$result = self::$options->flush( 'localkey6' );
		$this->assertTrue( $result );

		$result = self::$options->flush( 'globalkey6' );
		$this->assertFalse( $result );

		if ( is_multisite() ) {
			update_network_option( null, self::$prefix . 'globalkey6', array( get_current_blog_id() => 'hello' ) );
		} else {
			update_option( self::$prefix . 'globalkey6', 'hello' );
		}
		$result = self::$options->flush( 'globalkey6' );
		$this->assertTrue( $result );
	}

	public function test_is_stored_in_network() {
		$result = self::$options->is_stored_in_network( 'localkey7' );
		$this->assertFalse( $result );

		$result = self::$options->is_stored_in_network( 'globalkey7' );
		$this->assertTrue( $result );
	}

	public function test_store_in_network() {
		$result = self::$options->is_stored_in_network( 'globalkey8' );
		$this->assertTrue( $result );
	}

	public function test_migrate_to_network() {
		$base_options = array( 'somekey' => 'somevalue' );

		if ( is_multisite() ) {
			$result = apply_filters( 'populate_network_meta', $base_options, 1 );
			$this->assertEquals( $base_options, $result );
		} else {
			$new_value = '23';
			update_option( self::$prefix . 'globalkey9', $new_value );

			$expected = array_merge( $base_options, array( self::$prefix . 'globalkey9' => array( 1 => $new_value ) ) );
			$result = apply_filters( 'populate_network_meta', $base_options, 1 );
			$this->assertEquals( $expected, $result );
		}
	}
}
