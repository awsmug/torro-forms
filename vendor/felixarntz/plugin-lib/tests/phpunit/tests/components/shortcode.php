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
use Leaves_And_Love\Plugin_Lib\Components\Shortcode;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_Error_Handler;

/**
 * @group components
 * @group shortcodes
 */
class Tests_Shortcode extends Unit_Test_Case {
	protected static $prefix;
	protected static $shortcodes;

	protected $random_property = '';
	protected $enqueue_count = 0;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_shortcode_';

		$error_handler = new Error_Handler( self::$prefix, new Translations_Error_Handler() );

		self::$shortcodes = new Shortcodes( self::$prefix, array(
			'cache'         => new Cache( self::$prefix ),
			'template'      => Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->template(),
			'error_handler' => $error_handler,
		) );
	}

	public function test_run() {
		// Test basic shortcode.
		$shortcode = new Shortcode( 'first_tag', array( $this, 'shortcode_hello_world' ), array(), self::$shortcodes );
		$result = $shortcode->run( array(), null );
		$this->assertSame( 'HelloWorld', $result );

		// Test shortcode with attributes.
		$shortcode = new Shortcode( 'second_tag', array( $this, 'shortcode_json_atts' ), array(), self::$shortcodes );
		$atts = array( 'key1' => 'foo', 'key2' => 'bar' );
		$result = $shortcode->run( $atts, null );
		$this->assertSame( json_encode( $atts ), $result );

		// Test shortcode with content.
		$shortcode = new Shortcode( 'third_tag', array( $this, 'shortcode_content_autop' ), array(), self::$shortcodes );
		$content = 'Hello, World!';
		$result = $shortcode->run( array(), $content );
		$this->assertSame( wpautop( $content ), $result );

		// Test defaults.
		$defaults = array( 'key' => 'value' );
		$shortcode = new Shortcode( 'fourth_tag', array( $this, 'shortcode_json_atts' ), array(
			'defaults' => $defaults,
		), self::$shortcodes );
		$atts = array( 'key1' => 'foo', 'key2' => 'bar' );
		$result = $shortcode->run( $atts, null );
		$this->assertSame( json_encode( shortcode_atts( $defaults, $atts, 'third_tag' ) ), $result );

		// Test shortcode cache.
		$shortcode = new Shortcode( 'fifth_tag', array( $this, 'shortcode_property' ), array(
			'cache' => true,
		), self::$shortcodes );
		$this->random_property = 'foo';
		$result = $shortcode->run( array(), null );
		$this->assertEquals( 'foo', $result );
		$this->random_property = 'bar';
		$result = $shortcode->run( array( 'key' => 'value' ), null );
		$this->assertEquals( 'bar', $result );
		$result = $shortcode->run( array(), null );
		$this->assertEquals( 'foo', $result );
	}

	public function test_get_tag() {
		$tag_name = 'verycustomtagname';
		$shortcode = new Shortcode( $tag_name, '__return_empty_string', array(), self::$shortcodes );
		$this->assertEquals( $tag_name, $shortcode->get_tag() );
	}

	public function test_enqueue_assets() {
		$shortcode = new Shortcode( 'shortcode_with_css', '__return_empty_string', array(
			'enqueue_callback' => array( $this, 'enqueue_stylesheet' ),
		), self::$shortcodes );
		$shortcode->enqueue_assets();
		$this->assertTrue( wp_style_is( 'plugin_lib_shortcode_test' ) );
		$this->assertSame( 1, $this->enqueue_count );
		$shortcode->enqueue_assets();
		$this->assertSame( 1, $this->enqueue_count );
	}

	public function test_has_enqueue_callback() {
		$shortcode = new Shortcode( 'shortcode_with_valid_enqueue', '__return_empty_string', array(
			'enqueue_callback' => array( $this, 'enqueue_stylesheet' ),
		), self::$shortcodes );
		$result = $shortcode->has_enqueue_callback();
		$this->assertTrue( $result );

		$shortcode = new Shortcode( 'shortcode_with_invalid_enqueue', '__return_empty_string', array(
			'enqueue_callback' => array( $this, 'invalid_method' ),
		), self::$shortcodes );
		$result = $shortcode->has_enqueue_callback();
		$this->assertFalse( $result );

		$shortcode = new Shortcode( 'shortcode_without_enqueue', '__return_empty_string', array(), self::$shortcodes );
		$result = $shortcode->has_enqueue_callback();
		$this->assertFalse( $result );
	}

	public function shortcode_hello_world( $atts, $content, $tag, $template ) {
		return 'HelloWorld';
	}

	public function shortcode_content_autop( $atts, $content, $tag, $template ) {
		return wpautop( $content );
	}

	public function shortcode_json_atts( $atts, $content, $tag, $template ) {
		return json_encode( $atts );
	}

	public function shortcode_property( $atts, $content, $tag, $template ) {
		return $this->random_property;
	}

	public function enqueue_stylesheet() {
		wp_enqueue_style( 'plugin_lib_shortcode_test', get_stylesheet_uri() );

		$this->enqueue_count++;
	}
}
