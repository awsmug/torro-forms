<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love_Plugin_Loader;

/**
 * @group traits
 */
class Tests_Actions_Trait extends Unit_Test_Case {
	protected static $actions;
	protected static $filters;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$actions = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->actions();
		self::$filters = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->filters();
	}

	public function test_action_func() {
		$mode = 'func';

		self::$actions->add( $this->prefix . 'some_action', $mode );

		$result = self::$actions->has( $this->prefix . 'some_action', $mode );
		$this->assertEquals( 10, $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEquals( $mode, $result );

		self::$actions->remove( $this->prefix . 'some_action', $mode );

		$result = self::$actions->has( $this->prefix . 'some_action', $mode );
		$this->assertFalse( $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEmpty( $result );
	}

	public function test_action_public_method() {
		$mode = 'public';

		self::$actions->add( $this->prefix . 'some_action', $mode );

		$result = self::$actions->has( $this->prefix . 'some_action', $mode );
		$this->assertEquals( 10, $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEquals( $mode, $result );

		self::$actions->remove( $this->prefix . 'some_action', $mode );

		$result = self::$actions->has( $this->prefix . 'some_action', $mode );
		$this->assertFalse( $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEmpty( $result );
	}

	public function test_action_private_method() {
		$mode = 'private';

		self::$actions->add( $this->prefix . 'some_action', $mode );

		$result = self::$actions->has( $this->prefix . 'some_action', $mode );
		$this->assertEquals( 10, $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEquals( $mode, $result );

		self::$actions->remove( $this->prefix . 'some_action', $mode );

		$result = self::$actions->has( $this->prefix . 'some_action', $mode );
		$this->assertFalse( $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEmpty( $result );
	}

	public function test_filter_func() {
		$mode = 'func';

		self::$filters->add( $this->prefix . 'some_filter', $mode );

		$result = self::$filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertEquals( 10, $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEquals( $mode, $result );

		self::$filters->remove( $this->prefix . 'some_filter', $mode );

		$result = self::$filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertFalse( $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEmpty( $result );
	}

	public function test_filter_public_method() {
		$mode = 'public';

		self::$filters->add( $this->prefix . 'some_filter', $mode );

		$result = self::$filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertEquals( 10, $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEquals( $mode, $result );

		self::$filters->remove( $this->prefix . 'some_filter', $mode );

		$result = self::$filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertFalse( $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEmpty( $result );
	}

	public function test_filter_private_method() {
		$mode = 'private';

		self::$filters->add( $this->prefix . 'some_filter', $mode );

		$result = self::$filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertEquals( 10, $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEquals( $mode, $result );

		self::$filters->remove( $this->prefix . 'some_filter', $mode );

		$result = self::$filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertFalse( $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEmpty( $result );
	}
}
