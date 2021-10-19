<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love_Autoloader;

/**
 * @group general
 * @group autoloader
 */
class Tests_Autoloader extends Unit_Test_Case {
	protected static $vendor_name;
	protected static $project_name;
	protected static $basedir;
	protected static $original_namespaces;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$vendor_name = 'Leaves_And_Love';
		self::$project_name = 'Autoloader_Test';
		self::$basedir = LALPL_TESTS_DATA . 'autoloader/';

		self::$original_namespaces = Leaves_And_Love_Autoloader::get_registered_namespaces();

		foreach ( self::$original_namespaces as $vendor_name => $project_names ) {
			foreach ( $project_names as $project_name => $basedir ) {
				Leaves_And_Love_Autoloader::unregister_namespace( $vendor_name, $project_name );
			}
		}

		Leaves_And_Love_Autoloader::register_namespace( self::$vendor_name, self::$project_name, self::$basedir );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		Leaves_And_Love_Autoloader::unregister_namespace( self::$vendor_name, self::$project_name );

		foreach ( self::$original_namespaces as $vendor_name => $project_names ) {
			foreach ( $project_names as $project_name => $basedir ) {
				Leaves_And_Love_Autoloader::register_namespace( $vendor_name, $project_name, $basedir );
			}
		}
	}

	public function tearDown() {
		parent::tearDown();

		foreach ( Leaves_And_Love_Autoloader::get_registered_namespaces() as $vendor_name => $project_names ) {
			foreach ( $project_names as $project_name => $basedir ) {
				if ( self::$vendor_name === $vendor_name && self::$project_name === $project_name ) {
					continue;
				}

				Leaves_And_Love_Autoloader::unregister_namespace( $vendor_name, $project_name );
			}
		}
	}

	public function test_register_namespace() {
		$result = Leaves_And_Love_Autoloader::register_namespace( 'Custom_Vendor', 'Custom_Project', LALPL_TESTS_DATA . 'custom-project/' );
		$this->assertTrue( $result );

		$result = Leaves_And_Love_Autoloader::register_namespace( self::$vendor_name, 'Another_Project', LALPL_TESTS_DATA . 'another-project/' );
		$this->assertTrue( $result );

		$result = Leaves_And_Love_Autoloader::register_namespace( self::$vendor_name, self::$project_name, self::$basedir );
		$this->assertFalse( $result );
	}

	public function test_namespace_registered() {
		$result = Leaves_And_Love_Autoloader::namespace_registered( 'Invalid_Vendor', 'Invalid_Project' );
		$this->assertFalse( $result );

		$result = Leaves_And_Love_Autoloader::namespace_registered( self::$vendor_name, 'Another_Project' );
		$this->assertFalse( $result );

		$result = Leaves_And_Love_Autoloader::namespace_registered( self::$vendor_name, self::$project_name );
		$this->assertTrue( $result );
	}

	public function test_unregister_namespace() {
		$vendor_name = 'Custom_Vendor';
		$project1_name = 'Custom_Project_1';
		$project2_name = 'Custom_Project_2';

		Leaves_And_Love_Autoloader::register_namespace( self::$vendor_name, $project1_name, LALPL_TESTS_DATA . 'autoloader_test/' );
		Leaves_And_Love_Autoloader::register_namespace( self::$vendor_name, $project2_name, LALPL_TESTS_DATA . 'autoloader_test/' );

		$result = Leaves_And_Love_Autoloader::unregister_namespace( 'Invalid_Vendor', 'Invalid_Project' );
		$this->assertFalse( $result );

		$result = Leaves_And_Love_Autoloader::unregister_namespace( self::$vendor_name, $project1_name );
		$this->assertTrue( $result );

		$result = Leaves_And_Love_Autoloader::unregister_namespace( self::$vendor_name, $project2_name );
		$this->assertTrue( $result );
	}

	public function test_get_registered_namespaces() {
		$expected = array(
			self::$vendor_name => array(
				self::$project_name => self::$basedir,
			),
		);

		$result = Leaves_And_Love_Autoloader::get_registered_namespaces();
		$this->assertEquals( $expected, $result );
	}

	public function test_load_class() {
		$main_class    = self::$vendor_name . '\\' . self::$project_name . '\\Main_Class';
		$sub_class     = self::$vendor_name . '\\' . self::$project_name . '\\Sub_Dir\\Sub_Class';
		$missing_class = self::$vendor_name . '\\' . self::$project_name . '\\Missing_Class';

		$result = Leaves_And_Love_Autoloader::load_class( $main_class );
		$this->assertTrue( $result );

		$result = Leaves_And_Love_Autoloader::load_class( $sub_class );
		$this->assertTrue( $result );

		$result = Leaves_And_Love_Autoloader::load_class( $missing_class );
		$this->assertFalse( $result );

		$result = Leaves_And_Love_Autoloader::load_class( 'Invalid_Vendor\\Invalid_Project\\Invalid_Class' );
		$this->assertFalse( $result );

		$result = Leaves_And_Love_Autoloader::load_class( self::$vendor_name . '\\Autoloader\\Main_Class' );
		$this->assertFalse( $result );
	}
}
