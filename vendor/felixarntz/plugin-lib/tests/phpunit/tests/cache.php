<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\Cache;

/**
 * @group general
 * @group cache
 */
class Tests_Cache extends Unit_Test_Case {
	protected static $prefix;
	protected static $cache;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_cache_';
		self::$cache = new Cache( self::$prefix );
	}

	public function test_add() {
		$result = self::$cache->add( 'somekey', 'value1' );
		$this->assertTrue( $result );

		$result = self::$cache->add( 'somekey', 'value2' );
		$this->assertFalse( $result );
	}

	public function test_delete() {
		$result = self::$cache->delete( 'deletekey' );
		$this->assertFalse( $result );

		wp_cache_add( 'deletekey', 'value', self::$prefix . 'general' );
		$result = self::$cache->delete( 'deletekey' );
		$this->assertTrue( $result );
	}

	public function test_get() {
		$result = self::$cache->get( 'getkey' );
		$this->assertFalse( $result );

		wp_cache_add( 'getkey', 'getvalue', self::$prefix . 'general' );
		$result = self::$cache->get( 'getkey' );
		$this->assertSame( 'getvalue', $result );
	}

	public function test_replace() {
		$result = self::$cache->replace( 'replacekey', 'value' );
		$this->assertFalse( $result );

		wp_cache_add( 'replacekey', 'value', self::$prefix . 'general' );
		$result = self::$cache->replace( 'replacekey', 'value2' );
		$this->assertTrue( $result );
	}

	public function test_set() {
		$result = self::$cache->set( 'setkey', 'setvalue' );
		$this->assertTrue( $result );

		$result = wp_cache_get( 'setkey', self::$prefix . 'general' );
		$this->assertSame( 'setvalue', $result );
	}
}
