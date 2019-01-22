<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love_Plugin_Loader;
use Leaves_And_Love\Plugin_Lib\Error_Handler;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Components\Shortcodes;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_Error_Handler;

/**
 * @group components
 * @group shortcodes
 */
class Tests_Shortcodes extends Unit_Test_Case {
	protected static $prefix;
	protected static $shortcodes;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_shortcodes_';

		$error_handler = new Error_Handler( self::$prefix, new Translations_Error_Handler() );

		self::$shortcodes = new Shortcodes( self::$prefix, array(
			'cache'         => new Cache( self::$prefix ),
			'template'      => Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->template(),
			'error_handler' => $error_handler,
		) );
	}

	public function test_add() {
		$shortcode_name = 'test_add_shortcode';

		$result = self::$shortcodes->add( '', '__return_empty_string' );
		$this->assertFalse( $result );
		$this->assertFalse( shortcode_exists( self::$prefix ) );

		$result = self::$shortcodes->add( $shortcode_name, '__return_empty_string' );
		$this->assertTrue( $result );
		$this->assertTrue( shortcode_exists( self::$prefix . $shortcode_name ) );
	}

	public function test_has() {
		$shortcode_name = 'test_has_shortcode';
		$shortcode_name2 = 'test_has_shortcode2';

		$result = self::$shortcodes->has( 'non_existing_shortcode' );
		$this->assertFalse( $result );

		add_shortcode( self::$prefix . $shortcode_name, '__return_empty_string' );
		$result = self::$shortcodes->has( $shortcode_name );
		$this->assertFalse( $result );

		self::$shortcodes->add( $shortcode_name2, '__return_empty_string' );
		$result = self::$shortcodes->has( $shortcode_name2 );
		$this->assertTrue( $result );
	}

	public function test_get() {
		$shortcode_name = 'test_get_shortcode';
		$shortcode_name2 = 'test_get_shortcode2';

		$result = self::$shortcodes->get( 'non_existing_shortcode' );
		$this->assertNull( $result );

		add_shortcode( self::$prefix . $shortcode_name, '__return_empty_string' );
		$result = self::$shortcodes->get( $shortcode_name );
		$this->assertNull( $result );

		self::$shortcodes->add( $shortcode_name2, '__return_empty_string' );
		$result = self::$shortcodes->get( $shortcode_name2 );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\Components\Shortcode', $result );
		$this->assertSame( self::$prefix . $shortcode_name2, $result->get_tag() );
	}

	public function test_remove() {
		$shortcode_name = 'test_remove_shortcode';
		$shortcode_name2 = 'test_remove_shortcode2';

		$result = self::$shortcodes->remove( 'non_existing_shortcode' );
		$this->assertFalse( $result );

		add_shortcode( self::$prefix . $shortcode_name, '__return_empty_string' );
		$result = self::$shortcodes->remove( $shortcode_name );
		$this->assertFalse( $result );

		self::$shortcodes->add( $shortcode_name2, '__return_empty_string' );
		$result = self::$shortcodes->remove( $shortcode_name2 );
		$this->assertTrue( $result );
		$this->assertFalse( shortcode_exists( self::$prefix . $shortcode_name2 ) );
	}
}
