<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love_Plugin_Loader;

/**
 * @group general
 * @group template
 */
class Tests_Template extends Unit_Test_Case {
	protected static $template_instance;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$template_instance = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->template();
	}

	public function test_get_partial() {
		$base = 'Basic template.';
		$base_suffixed = 'Template with suffix.';
		$data = 'Data: %s';

		$content = 'Some custom content.';

		ob_start();
		self::$template_instance->get_partial( 'base' );
		$result = ob_get_clean();
		$this->assertEquals( $base, $result );

		ob_start();
		self::$template_instance->get_partial( 'invalid' );
		$result = ob_get_clean();
		$this->assertEmpty( $result );

		ob_start();
		self::$template_instance->get_partial( 'base', array( 'template_suffix' => 'suffixed' ) );
		$result = ob_get_clean();
		$this->assertEquals( $base_suffixed, $result );

		ob_start();
		self::$template_instance->get_partial( 'base', array( 'template_suffix' => 'invalid' ) );
		$result = ob_get_clean();
		$this->assertEquals( $base, $result );

		ob_start();
		self::$template_instance->get_partial( 'data', array( 'content' => $content ) );
		$result = ob_get_clean();
		$this->assertEquals( sprintf( $data, $content ), $result );
	}

	public function test_locate_file() {
		$plugin_template_location = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->path( 'templates/' );

		$base = 'base.php';

		$result = self::$template_instance->locate_file( array( $base ) );
		$this->assertEquals( $plugin_template_location . $base, $result );

		$result = self::$template_instance->locate_file( array( 'something-invalid.php', $base ) );
		$this->assertEquals( $plugin_template_location . $base, $result );

		$result = self::$template_instance->locate_file( $base );
		$this->assertEquals( $plugin_template_location . $base, $result );

		$result = self::$template_instance->locate_file( array( 'something-invalid.php' ) );
		$this->assertEmpty( $result );
	}

	public function test_load_file() {
		$require_file = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->path( 'templates/base.php' );
		$require_once_file = LALPL_TESTS_DATA . 'templates/require-this-once.php';

		$require_content = 'Basic template.';
		$require_once_content = 'Require this once!';

		ob_start();
		self::$template_instance->load_file( $require_file, array(), false );
		$result = ob_get_clean();
		$this->assertEquals( $require_content, $result );

		ob_start();
		self::$template_instance->load_file( $require_once_file, array(), true );
		$result = ob_get_clean();
		$this->assertEquals( $require_once_content, $result );

		ob_start();
		self::$template_instance->load_file( $require_once_file, array(), true );
		$result = ob_get_clean();
		$this->assertEmpty( $result );
	}

	public function test_register_location() {
		$custom_location = LALPL_TESTS_DATA . 'templates/';

		$base = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->path( 'templates/base.php' );
		$base_overridden = $custom_location . 'base.php';

		$result = self::$template_instance->locate_file( 'base.php' );
		$this->assertEquals( $base, $result );

		$result = self::$template_instance->register_location( 'custom_loc', $custom_location );
		$this->assertTrue( $result );

		$result = self::$template_instance->locate_file( 'base.php' );
		$this->assertEquals( $base_overridden, $result );

		self::$template_instance->unregister_location( 'custom_loc' );

		$result = self::$template_instance->locate_file( 'base.php' );
		$this->assertEquals( $base, $result );
	}

	public function test_unregister_location() {
		$location_name = 'my_location';

		self::$template_instance->register_location( $location_name, LALPL_TESTS_DATA . 'templates/' );

		$result = self::$template_instance->unregister_location( $location_name );
		$this->assertTrue( $result );

		$result = self::$template_instance->unregister_location( $location_name );
		$this->assertFalse( $result );
	}
}
