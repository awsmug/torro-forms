<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\Error_Handler;
use Leaves_And_Love\Plugin_Lib\Options;
use Leaves_And_Love\Plugin_Lib\DB;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_Error_Handler;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_DB;

/**
 * @group general
 * @group db
 */
class Tests_DB extends Unit_Test_Case {
	protected static $prefix;
	protected static $db;

	public static function setUpBeforeClass() {
		global $wpdb;

		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_db_';

		$error_handler = new Error_Handler( self::$prefix, new Translations_Error_Handler() );

		self::$db = new DB( self::$prefix, array(
			'options'       => new Options( self::$prefix ),
			'error_handler' => $error_handler,
		), new Translations_DB() );

		self::$db->add_table( 'rows', array(
			"id bigint(20) unsigned NOT NULL auto_increment",
			"type varchar(32) NOT NULL default ''",
			"PRIMARY KEY  (id)",
			"KEY type (type)",
		) );
		self::$db->set_version( 20160905 );

		self::$db->check();

		$prefixed_table = self::$prefix . 'rows';

		$wpdb->insert( $wpdb->$prefixed_table, array(
			'type' => 'default',
		), array( '%s' ) );
	}

	public static function tearDownAfterClass() {
		global $wpdb;

		parent::tearDownAfterClass();

		$prefixed_table_name = self::$prefix . 'rows';

		$db_table_name = $wpdb->$prefixed_table_name;
		$wpdb->query( "DROP TABLE $db_table_name" );

		$key = array_search( $prefixed_table_name, $wpdb->tables );
		if ( false !== $key ) {
			unset( $wpdb->tables[ $key ] );
			$wpdb->tables = array_values( $wpdb->tables );
		}

		unset( $wpdb->$prefixed_table_name );

		delete_network_option( null, self::$prefix . 'db_version' );
	}

	public function test_query() {
		global $wpdb;

		$prefixed_table = self::$prefix . 'rows';

		$number = 3;
		$type = 'some_type';

		for ( $i = 0; $i < $number; $i++ ) {
			$wpdb->insert( $wpdb->$prefixed_table, array(
				'type' => $type,
			), array( '%s' ) );
		}

		$result = self::$db->query( "SELECT * FROM %rows% WHERE type = %s", $type );
		$this->assertSame( $number, $result );
	}

	public function test_get_var() {
		global $wpdb;

		$prefixed_table = self::$prefix . 'rows';

		$type = 'mysupercustomtype';

		$wpdb->insert( $wpdb->$prefixed_table, array(
			'type' => $type,
		), array( '%s' ) );

		$id = (int) $wpdb->insert_id;

		$result = self::$db->get_var( "SELECT type FROM %rows% WHERE id = %d", $id );
		$this->assertSame( $type, $result );
	}

	public function test_get_row() {
		global $wpdb;

		$prefixed_table = self::$prefix . 'rows';

		$type = 'mysupercustomtype2';

		$wpdb->insert( $wpdb->$prefixed_table, array(
			'type' => $type,
		), array( '%s' ) );

		$id = (int) $wpdb->insert_id;

		$result = self::$db->get_row( "SELECT * FROM %rows% WHERE id = %d", $id );
		$this->assertEquals( $id, $result->id );
		$this->assertEquals( $type, $result->type );
	}

	public function test_get_col() {
		global $wpdb;

		$prefixed_table = self::$prefix . 'rows';

		$number = 3;
		$type = 'mysupercustomtype3';

		$ids = array();

		for ( $i = 0; $i < $number; $i++ ) {
			$wpdb->insert( $wpdb->$prefixed_table, array(
				'type' => $type,
			), array( '%s' ) );

			$ids[] = (int) $wpdb->insert_id;
		}

		$result = self::$db->get_col( "SELECT type FROM %rows% WHERE id IN ( " . implode( ',', array_fill( 0, $number, '%d' ) ) . " )", $ids );
		$this->assertEquals( array_fill( 0, $number, $type ), $result );
	}

	public function test_get_results() {
		global $wpdb;

		$prefixed_table = self::$prefix . 'rows';

		$number = 4;
		$type = 'mysupercustomtype4';

		$ids = array();

		for ( $i = 0; $i < $number; $i++ ) {
			$wpdb->insert( $wpdb->$prefixed_table, array(
				'type' => $type,
			), array( '%s' ) );

			$ids[] = (int) $wpdb->insert_id;
		}

		$ids = array_reverse( $ids );

		$result = self::$db->get_results( "SELECT * FROM %rows% WHERE type = %s ORDER BY id DESC", $type );
		$this->assertEquals( $ids, wp_list_pluck( $result, 'id' ) );
	}

	public function test_insert() {
		global $wpdb;

		$prefixed_table = self::$prefix . 'rows';
		$type = 'mysupercustomtype5';

		$result = self::$db->insert( 'rows', array( 'type' => $type ) );
		$this->assertEquals( 1, $result );

		$id = (int) self::$db->insert_id;

		$result = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->$prefixed_table} WHERE type = %s", $type ) );
		$this->assertEquals( $id, $result );
	}

	public function test_replace() {
		global $wpdb;

		$prefixed_table = self::$prefix . 'rows';
		$type = 'mysupercustomtype6';
		$id = 32;

		$result = self::$db->replace( 'rows', array( 'id' => $id, 'type' => $type ) );
		$this->assertEquals( 1, $result );

		$result = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->$prefixed_table} WHERE type = %s", $type ) );
		$this->assertEquals( $id, $result );
	}

	public function test_update() {
		global $wpdb;

		$prefixed_table = self::$prefix . 'rows';
		$old_type = 'another_unusual_type';
		$type = 'mysupercustomtype7';

		$wpdb->insert( $wpdb->$prefixed_table, array(
			'type' => $old_type,
		), array( '%s' ) );

		$id = (int) $wpdb->insert_id;

		$result = self::$db->update( 'rows', array( 'type' => $type ), array( 'type' => $old_type ) );
		$this->assertEquals( 1, $result );

		$result = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->$prefixed_table} WHERE type = %s", $type ) );
		$this->assertEquals( $id, $result );
	}

	public function test_delete() {
		global $wpdb;

		$prefixed_table = self::$prefix . 'rows';
		$type = 'mysupercustomtype8';

		$wpdb->insert( $wpdb->$prefixed_table, array(
			'type' => $type,
		), array( '%s' ) );

		$result = self::$db->delete( 'rows', array( 'type' => $type ) );
		$this->assertEquals( 1, $result );

		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->$prefixed_table} WHERE type = %s", $type ) );
		$this->assertEmpty( $result );
	}

	public function test__isset() {
		$this->assertTrue( isset( self::$db->insert_id ) );
		$this->assertFalse( isset( self::$db->random_prop ) );
	}

	public function test__get() {
		$this->assertInternalType( 'int', self::$db->insert_id );
		$this->assertNull( self::$db->random_prop );
	}

	public function test_check() {
		global $wpdb;

		self::$db->check();

		$fields = $wpdb->get_results( "DESCRIBE " . $wpdb->prefix . self::$prefix . "rows;" );
		$this->assertSame( 2, count( $fields ) );
	}

	public function uninstall() {
		global $wpdb;

		self::$db->uninstall();

		$fields = $wpdb->get_results( "DESCRIBE " . $wpdb->prefix . self::$prefix . "rows;" );
		$this->assertEmpty( $fields );

		// reinstall after uninstalling
		self::$db->check();
	}

	public function test_add_table() {
		global $wpdb;

		$result = self::$db->add_table( 'rows', array(
			"random_id bigint(20) unsigned NOT NULL auto_increment",
			"PRIMARY KEY  (random_id)",
		) );
		$this->assertWPError( $result );

		$result = self::$db->add_table( 'custom' );
		$this->assertWPError( $result );

		$result = self::$db->add_table( 'custom', array(
			"custom_id bigint(20) unsigned NOT NULL auto_increment",
			"PRIMARY KEY  (custom_id)",
		) );
		$this->assertTrue( $result );

		self::$db->check( true );

		$fields = $wpdb->get_results( "DESCRIBE " . $wpdb->prefix . self::$prefix . "custom;" );
		$this->assertSame( 1, count( $fields ) );
	}
}
