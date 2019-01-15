<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\Error_Handler;
use Leaves_And_Love\Plugin_Lib\Options;
use Leaves_And_Love\Plugin_Lib\DB;
use Leaves_And_Love\Plugin_Lib\Meta;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_Error_Handler;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_DB;

/**
 * @group general
 * @group meta
 */
class Tests_Meta extends Unit_Test_Case {
	protected static $prefix;
	protected static $element_id;
	protected static $meta;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = $prefix = 'lalpl_tests_meta_';

		$error_handler = new Error_Handler( self::$prefix, new Translations_Error_Handler() );

		$db = new DB( self::$prefix, array(
			'options'       => new Options( self::$prefix ),
			'error_handler' => $error_handler,
		), new Translations_DB() );

		$max_index_length = 191;
		$db->add_table( 'elementmeta', array(
			"meta_id bigint(20) unsigned NOT NULL auto_increment",
			"{$prefix}element_id bigint(20) unsigned NOT NULL default '0'",
			"meta_key varchar(255) default NULL",
			"meta_value longtext",
			"PRIMARY KEY  (meta_id)",
			"KEY {$prefix}element_id ({$prefix}element_id)",
			"KEY meta_key (meta_key($max_index_length))",
		) );
		$db->set_version( 20160905 );

		$db->check();

		self::$element_id = 1;
		self::$meta = new Meta( self::$prefix, array(
			'db'            => $db,
			'error_handler' => $error_handler,
		) );
	}

	public static function tearDownAfterClass() {
		global $wpdb;

		parent::tearDownAfterClass();

		$prefixed_table_name = self::$prefix . 'elementmeta';

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

	public function test_add() {
		add_metadata( self::$prefix . 'element', self::$element_id, 'test_key', 'test_value' );
		$result = self::$meta->add( 'element', self::$element_id, 'test_key', 'second_value', true );
		$this->assertFalse( $result );

		$result = self::$meta->add( 'element', self::$element_id, 'test_key', 'second_value' );
		$this->assertInternalType( 'int', $result );

		$result = self::$meta->add( 'randtype', self::$element_id, 'test_key', 'second_value' );
		$this->assertFalse( $result );
	}

	public function test_update() {
		$result = self::$meta->update( 'element', self::$element_id, 'test_key2', 'test_value2' );
		$this->assertInternalType( 'int', $result );

		add_metadata( self::$prefix . 'element', self::$element_id, 'test_key3', 'test_value' );
		$result = self::$meta->update( 'element', self::$element_id, 'test_key3', 'new_value' );
		$this->assertTrue( $result );

		$result = self::$meta->update( 'element', self::$element_id, 'test_key3', 'newer_value', 'invalid_value' );
		$this->assertFalse( $result );
	}

	public function test_delete() {
		$result = self::$meta->delete( 'element', self::$element_id, 'delete_key' );
		$this->assertFalse( $result );

		add_metadata( self::$prefix . 'element', self::$element_id, 'delete_key', 'value' );
		$result = self::$meta->delete( 'element', self::$element_id, 'delete_key' );
		$this->assertTrue( $result );
	}

	public function test_get() {
		$result = self::$meta->get( 'element', self::$element_id, 'invalid_key' );
		$this->assertEmpty( $result );

		$result = self::$meta->get( 'element', self::$element_id, 'invalid_key', true );
		$this->assertFalse( $result );

		update_metadata( self::$prefix . 'element', self::$element_id, 'test_key4', 'test_value' );
		$result = self::$meta->get( 'element', self::$element_id, 'test_key4', true );
		$this->assertSame( 'test_value', $result );
	}

	public function test_exists() {
		$result = self::$meta->exists( 'element', self::$element_id, 'invalid_key' );
		$this->assertFalse( $result );

		add_metadata( self::$prefix . 'element', self::$element_id, 'test_key5', 'test_value' );
		$result = self::$meta->exists( 'element', self::$element_id, 'test_key5' );
		$this->assertTrue( $result );
	}

	public function test_delete_all() {
		$id = 34;

		self::$meta->update( 'element', $id, 'key1', 'value1' );
		self::$meta->update( 'element', $id, 'key2', 'value2' );
		self::$meta->update( 'element', $id, 'key3', 'value3' );
		self::$meta->update( 'element', $id, 'key4', 'value4' );

		$result = self::$meta->delete_all( 'element', $id );
		$this->assertTrue( $result );

		$result = self::$meta->get( 'element', $id );
		$this->assertEmpty( $result );
	}
}
