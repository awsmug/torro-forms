<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Sample_DB_Objects\Sample_Collection;

/**
 * @group db-objects
 * @group collections
 * @group elements
 */
class Tests_Element_Collection extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_element_collection_';

		self::$manager = self::setUpSampleManager( self::$prefix, 'c_element' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::tearDownSampleManager( self::$prefix, 'c_element' );
		self::$manager = null;
	}

	public function test_transform_into_objects() {
		$model_ids = array();
		for ( $i = 1; $i <= 5; $i++ ) {
			$model_ids[] = self::$manager->add( array(
				'type' => 'type' . $i,
				'title' => 'Element Title ' . $i,
			) );
		}

		$collection = new Sample_Collection( self::$manager, $model_ids, 0, 'ids' );
		$this->assertSame( 'ids', $collection->get_fields() );
		$this->assertEquals( $model_ids, $collection->get_raw() );

		$collection->transform_into_objects();
		$this->assertSame( 'objects', $collection->get_fields() );
		$this->assertEquals( $model_ids, wp_list_pluck( $collection->get_raw(), 'id' ) );

		$collection->transform_into_objects();
		$this->assertSame( 'objects', $collection->get_fields() );
		$this->assertEquals( $model_ids, wp_list_pluck( $collection->get_raw(), 'id' ) );

		$collection = new Sample_Collection( self::$manager, array_fill( 0, 5, 0 ), 0, 'ids' );
		$collection->transform_into_objects();
		$this->assertEquals( array_fill( 0, 5, null ), $collection->get_raw() );
	}

	public function test_transform_into_ids() {
		$model_ids = array();
		$models = array();
		for ( $i = 1; $i <= 5; $i++ ) {
			$model_id = self::$manager->add( array(
				'type' => 'type' . $i,
				'title' => 'Element Title ' . $i,
			) );
			$model_ids[] = $model_id;
			$models[] = self::$manager->get( $model_id );
		}

		$collection = new Sample_Collection( self::$manager, $models, 0, 'objects' );
		$this->assertSame( 'objects', $collection->get_fields() );
		$this->assertEquals( $model_ids, wp_list_pluck( $collection->get_raw(), 'id' ) );

		$collection->transform_into_ids();
		$this->assertSame( 'ids', $collection->get_fields() );
		$this->assertEquals( $model_ids, $collection->get_raw() );

		$collection->transform_into_ids();
		$this->assertSame( 'ids', $collection->get_fields() );
		$this->assertEquals( $model_ids, $collection->get_raw() );

		$collection = new Sample_Collection( self::$manager, array_fill( 0, 5, null ), 0, 'objects' );
		$collection->transform_into_ids();
		$this->assertEquals( array_fill( 0, 5, 0 ), $collection->get_raw() );
	}

	public function test_get_fields() {
		$collection = new Sample_Collection( self::$manager, array(), 0, 'ids' );
		$this->assertSame( 'ids', $collection->get_fields() );

		$collection = new Sample_Collection( self::$manager, array(), 0, 'invalid' );
		$this->assertSame( 'ids', $collection->get_fields() );
	}

	public function test_get_total() {
		$model_ids = range( 1, 5 );

		$collection = new Sample_Collection( self::$manager, $model_ids, 0, 'ids' );
		$this->assertSame( count( $model_ids ), $collection->get_total() );

		$total_models = 20;
		$collection = new Sample_Collection( self::$manager, $model_ids, $total_models, 'ids' );
		$this->assertSame( $total_models, $collection->get_total() );
	}

	public function test_to_json() {
		$model_ids = array();
		for ( $i = 1; $i <= 5; $i++ ) {
			$model_ids[] = self::$manager->add( array(
				'type' => 'type' . $i,
				'title' => 'Element Title ' . $i,
			) );
		}

		$total_models = 10;
		$collection = new Sample_Collection( self::$manager, $model_ids, $total_models, 'ids' );

		$expected = array(
			'total'  => $total_models,
			'fields' => 'ids',
			'models' => $model_ids,
		);
		$json = $collection->to_json();
		$this->assertEquals( $expected, $json );

		$collection->transform_into_objects();

		$expected = array(
			'total'  => $total_models,
			'fields' => 'objects',
			'models' => array(),
		);
		foreach ( $model_ids as $model_id ) {
			$model = self::$manager->get( $model_id );
			$expected['models'][] = $model->to_json();
		}
		$json = $collection->to_json();
		$this->assertEquals( $expected, $json );

		$collection = new Sample_Collection( self::$manager, array_fill( 0, 5, null ), 0, 'objects' );
		$expected = array(
			'total'  => 5,
			'fields' => 'objects',
			'models' => array_fill( 0, 5, array( 'id' => 0 ) ),
		);
		$json = $collection->to_json();
		$this->assertEquals( $expected, $json );
	}

	public function test_array_access() {
		$model_ids = range( 1, 5 );

		$collection = new Sample_Collection( self::$manager, $model_ids, 0, 'ids' );

		$collection[0] = 500;
		unset( $collection[4] );

		$model_ids = array();
		for ( $i = 0; $i < count( $model_ids ); $i++ ) {
			if ( isset( $collection[ $i ] ) ) {
				$model_ids[] = $collection[ $i ];
			} else {
				$model_ids[] = 0;
			}
		}
		$this->assertSame( $model_ids, $model_ids );
	}

	public function test_array_access_methods() {
		$model_ids = range( 1, 5 );

		$collection = new Sample_Collection( self::$manager, $model_ids, 0, 'ids' );

		$collection->offsetSet( 0, 500 );
		$collection->offsetUnset( 4 );

		$model_ids_read = array();
		for ( $i = 0; $i < count( $model_ids ); $i++ ) {
			if ( $collection->offsetExists( $i ) ) {
				$model_ids_read[] = $collection->offsetGet( $i );
			} else {
				$model_ids_read[] = 0;
			}
		}
		$this->assertSame( $model_ids, $model_ids_read );
	}

	public function test_iterator() {
		$model_ids = range( 1, 5 );

		$collection = new Sample_Collection( self::$manager, $model_ids, 0, 'ids' );

		$model_ids = array();
		$keys = array();
		foreach ( $collection as $key => $model_id ) {
			$model_ids[] = $model_id;
			$keys[] = $key;
		}
		$this->assertSame( $model_ids, $model_ids );
		$this->assertSame( array_keys( $model_ids ), $keys );
	}

	public function test_iterator_methods() {
		$model_ids = range( 1, 5 );

		$collection = new Sample_Collection( self::$manager, $model_ids, 0, 'ids' );

		$model_ids = array();
		$keys = array();
		$collection->rewind();
		while ( $collection->valid() ) {
			$model_ids[] = $collection->current();
			$keys[] = $collection->key();
			$collection->next();
		}
		$this->assertSame( $model_ids, $model_ids );
		$this->assertSame( array_keys( $model_ids ), $keys );
		$this->assertNull( $collection->current() );
	}

	public function test_countable() {
		$model_ids = range( 1, 5 );

		$collection = new Sample_Collection( self::$manager, $model_ids, 0, 'ids' );
		$this->assertSame( count( $model_ids ), count( $collection ) );
	}

	public function test_countable_methods() {
		$model_ids = range( 1, 5 );

		$collection = new Sample_Collection( self::$manager, $model_ids, 0, 'ids' );
		$this->assertSame( count( $model_ids ), $collection->count() );
	}
}
