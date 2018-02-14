<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love_Plugin_Loader;

/**
 * @group general
 * @group plugin
 */
class Tests_Plugin extends Unit_Test_Case {
	protected static $plugin_instance;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$plugin_instance = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' );
	}

	public function test__call() {
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\Options', self::$plugin_instance->options() );

		$this->assertFalse( self::$plugin_instance->get_deactivation_hook() );

		$this->assertNull( self::$plugin_instance->main_file() );
	}

	public function test_load() {
		self::$plugin_instance->load();
		self::$plugin_instance->load();

		$this->assertEquals( 1, did_action( 'sp_loaded' ) );
	}

	public function test_start() {
		self::$plugin_instance->start();
		self::$plugin_instance->start();

		$this->assertEquals( 1, did_action( 'sp_started' ) );
	}

	public function test_path() {
		$subpath = 'src/sp-main.php';

		$expected = WP_PLUGIN_DIR . '/sample-plugin/' . $subpath;

		$this->assertEquals( $expected, self::$plugin_instance->path( $subpath ) );
		$this->assertEquals( $expected, self::$plugin_instance->path( '/' . $subpath ) );
	}

	public function test_url() {
		$subpath = 'src/sp-main.php';

		$expected = WP_PLUGIN_URL . '/sample-plugin/' . $subpath;

		$this->assertEquals( $expected, self::$plugin_instance->url( $subpath ) );
		$this->assertEquals( $expected, self::$plugin_instance->url( '/' . $subpath ) );
	}
}
