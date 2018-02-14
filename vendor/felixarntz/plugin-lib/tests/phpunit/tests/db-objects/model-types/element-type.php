<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Sample_DB_Objects\Sample_Type;

/**
 * @group db-objects
 * @group model-types
 * @group elements
 */
class Tests_Element_Type extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_element_type_';

		self::$manager = self::setUpSampleManager( self::$prefix, 't_element' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::tearDownSampleManager( self::$prefix, 't_element' );
		self::$manager = null;
	}

	public function test_setgetisset() {
		$type = new Sample_Type( self::$manager->types(), 'foo', new \stdClass() );

		$this->assertTrue( isset( $type->slug ) );
		$this->assertTrue( isset( $type->public ) );
		$this->assertFalse( isset( $type->invalid ) );

		$this->assertSame( 'foo', $type->slug );
		$this->assertSame( false, $type->public );
		$this->assertNull( $type->invalid );

		$type->slug = 'bar';
		$this->assertSame( 'bar', $type->slug );

		$type->something = 'bee';
		$this->assertTrue( isset( $type->something ) );
		$this->assertSame( 'bee', $type->something );
	}

	public function test_to_json() {
		$args = array(
			'label'   => 'Foos',
			'labels'  => array(
				'name'          => 'Foos',
				'singular_name' => 'Foo',
				'all_items'     => 'All Foos',
			),
			'public'  => false,
			'show_ui' => true,
		);

		$type = new Sample_Type( self::$manager->types(), 'foo', $args );

		$expected = array_merge( array( 'slug' => 'foo' ), $args, array( 'default' => false ) );
		$result = $type->to_json();
		$this->assertEquals( $expected, $result );
	}
}
