<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

/**
 * @group db-objects
 * @group model-type-managers
 * @group elements
 */
class Tests_Element_Type_Manager extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;
	protected static $default_type;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_element_type_manager_';

		self::$manager = self::setUpSampleManager( self::$prefix, 'tm_element' );

		self::$default_type = 'default';
		self::$manager->types()->register( self::$default_type );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::tearDownSampleManager( self::$prefix, 'tm_element' );
		self::$manager = null;
	}

	public function tearDown() {
		parent::tearDown();

		$types = self::$manager->types();

		foreach ( $types->query() as $slug => $type ) {
			$types->unregister( $slug );
		}

		$types->register( self::$default_type );
	}

	public function test_register() {
		$types = self::$manager->types();

		$result = $types->register( 'foo' );
		$this->assertTrue( $result );

		$result = $types->register( 'bar', array( 'public' => true ) );
		$this->assertTrue( $result );

		$result = $types->register( 'foo' );
		$this->assertFalse( $result );
	}

	public function test_get() {
		$types = self::$manager->types();

		$result = $types->get( self::$default_type );
		$this->assertInstanceOf( 'Leaves_And_Love\Sample_DB_Objects\Sample_Type', $result );

		$result = $types->get( 'invalid' );
		$this->assertNull( $result );
	}

	public function test_query() {
		$types = self::$manager->types();

		$public_types = array( 'foo', 'bar', 'fee' );
		$show_ui_types = array( 'bar', 'foobar' );
		foreach ( $public_types as $public_type ) {
			$args = array( 'public' => true );
			if ( ! in_array( $public_type, $show_ui_types, true ) ) {
				$args['show_ui'] = false;
			}

			$types->register( $public_type, $args );
		}
		$types->register( 'foobar', array( 'show_ui' => true ) );

		$expected = array_unique( array_merge( $public_types, $show_ui_types, array( self::$default_type ) ) );
		sort( $expected );
		$result = $types->query();
		$this->assertEquals( $expected, array_keys( $result ) );

		$expected = array( 'foo', 'fee', 'bar' );
		$result = $types->query( array(
			'public'  => true,
			'orderby' => 'slug',
			'order'   => 'DESC',
			'field'   => 'slug',
		) );
		$this->assertEquals( $expected, array_keys( $result ) );

		$expected = array_unique( array_merge( $public_types, $show_ui_types ) );
		sort( $expected );
		$result = $types->query( array(
			'public'   => true,
			'show_ui'  => true,
			'operator' => 'OR',
		) );
		$this->assertEquals( $expected, array_keys( $result ) );

		$expected = array( 'bar' );
		$result = $types->query( array(
			'public'   => true,
			'show_ui'  => true,
		) );
		$this->assertEquals( $expected, array_keys( $result ) );
	}

	public function test_unregister() {
		$types = self::$manager->types();

		$result = $types->unregister( self::$default_type );
		$this->assertTrue( $result );

		$result = $types->unregister( 'invalid' );
		$this->assertFalse( $result );
	}
}
