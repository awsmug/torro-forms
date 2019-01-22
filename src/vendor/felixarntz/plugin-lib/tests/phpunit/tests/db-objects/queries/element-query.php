<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Sample_DB_Objects\Sample_Query;

/**
 * @group db-objects
 * @group queries
 * @group elements
 */
class Tests_Element_Query extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_element_query_';

		self::$manager = self::setUpSampleManager( self::$prefix, 'q_element' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::tearDownSampleManager( self::$prefix, 'q_element' );
		self::$manager = null;
	}

	public function data_parse_query() {
		return array(
			'negative number' => array(
				array(
					'type'   => 'something',
					'number' => -1,
				),
				array(
					'number'        => 0,
					'no_found_rows' => true,
				),
			),
			'positive string number' => array(
				array(
					'number' => '10',
				),
				array(
					'number'        => 10,
					'no_found_rows' => false,
				),
			),
			'with no_found_rows' => array(
				array(
					'number'        => 5,
					'offset'        => '5',
					'no_found_rows' => true,
				),
				array(
					'offset' => 5,
				),
			),
		);
	}

	/**
	 * @dataProvider data_parse_query
	 */
	public function test_parse_query( $args, $overridden ) {
		$query = new Sample_Query( self::$manager );
		$query->query( $args );

		$defaults = $query->query_var_defaults;

		$this->assertEqualSets( array_merge( $defaults, $args, $overridden ), $query->query_vars );
	}

	public function test_get_results_from_db() {
		$m1 = self::$manager->add( array( 'type' => 'foo' ) );
		$m2 = self::$manager->add( array( 'type' => 'bar' ) );
		$m3 = self::$manager->add( array( 'type' => 'foobar' ) );
		$m4 = self::$manager->add( array( 'type' => 'fee' ) );
		$m5 = self::$manager->add( array( 'type' => 'foo' ) );
		$m6 = self::$manager->add( array( 'type' => 'foobar' ) );

		$args = array(
			'fields'  => 'objects',
			'type'    => 'foobar',
			'orderby' => array( 'id' => 'DESC' ),
		);

		$query = new Sample_Query( self::$manager );
		$results = $query->query( $args );

		$this->assertEquals( array( $m6, $m3 ), wp_list_pluck( $results->get_raw(), 'id' ) );
	}

	public function test_get_results_with_meta_query() {
		$m1 = self::$manager->add( array( 'type' => 'foo' ) );
		$m2 = self::$manager->add( array( 'type' => 'foo' ) );
		$m3 = self::$manager->add( array( 'type' => 'foo' ) );
		$m4 = self::$manager->add( array( 'type' => 'foo' ) );

		$meta_key   = 'foometa';
		$meta_value = 'something';

		self::$manager->update_meta( $m2, $meta_key, $meta_value );
		self::$manager->update_meta( $m2, 'barmeta', '4' );
		self::$manager->update_meta( $m3, $meta_key, $meta_value );
		self::$manager->update_meta( $m3, 'barmeta', '3' );
		self::$manager->update_meta( $m4, $meta_key, 'something else' );

		$args = array(
			'fields'      => 'ids',
			'meta_key'    => $meta_key,
			'meta_value'  => $meta_value,
			'meta_query'  => array(
				'relation'  => 'AND',
				'foometa'   => array(
					'key'     => $meta_key,
					'value'   => $meta_value,
				),
				'barmeta'   => array(
					'key'     => 'barmeta',
					'compare' => 'EXISTS',
				),
			),
			'orderby'    => array( 'barmeta' => 'ASC' ),
		);

		$query = new Sample_Query( self::$manager );
		$results = $query->query( $args );

		$this->assertEquals( array( $m3, $m2 ), $results->get_raw() );
	}

	public function test_get_results_from_cache() {
		$query = new Sample_Query( self::$manager );
		$defaults = $query->query_var_defaults;

		$args = array(
			'fields'        => 'ids',
			'number'        => 3,
			'no_found_rows' => true,
			'type'          => 'uniquetype',
		);

		$key_args = wp_array_slice_assoc( array_merge( $defaults, $args ), array_keys( $defaults ) );
		$key_args = array_diff_key( $key_args, array_flip( array( 'fields', 'update_cache', 'update_meta_cache' ) ) );
		$key = md5( serialize( $key_args ) );

		$last_changed = self::$manager->get_from_cache( 'last_changed' );
		if ( ! $last_changed ) {
			$last_changed = microtime();
			self::$manager->set_in_cache( 'last_changed', $last_changed );
		}

		$expected = array(
			'model_ids' => array( 33, 82, 42 ),
			'total'     => 3,
		);

		$cache_key = "get_results:$key:$last_changed";
		self::$manager->add_to_cache( $cache_key, $expected );

		$results = $query->query( $args );

		$this->assertEquals( $expected['model_ids'], $results->get_raw() );
	}

	public function test_get_results_from_db_with_refreshed_cache() {
		$m1 = self::$manager->add( array( 'type' => 'foo' ) );
		$m2 = self::$manager->add( array( 'type' => 'bar' ) );
		$m3 = self::$manager->add( array( 'type' => 'foobar' ) );
		$m4 = self::$manager->add( array( 'type' => 'fee' ) );
		$m5 = self::$manager->add( array( 'type' => 'foo' ) );
		$m6 = self::$manager->add( array( 'type' => 'foobar' ) );

		$args = array(
			'fields'  => 'objects',
			'type'    => 'foobar',
			'orderby' => array( 'id' => 'DESC' ),
		);

		$query = new Sample_Query( self::$manager );
		$results = $query->query( $args );

		$m7 = self::$manager->add( array( 'type' => 'foobar' ) );

		$query = new Sample_Query( self::$manager );
		$results = $query->query( $args );

		$this->assertEquals( array( $m7, $m6, $m3 ), wp_list_pluck( $results->get_raw(), 'id' ) );
	}
}
