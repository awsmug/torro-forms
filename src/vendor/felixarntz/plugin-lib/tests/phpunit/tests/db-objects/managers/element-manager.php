<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

/**
 * @group db-objects
 * @group managers
 * @group elements
 */
class Tests_Element_Manager extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_element_manager_';

		self::$manager = self::setUpSampleManager( self::$prefix, 'm_element' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::tearDownSampleManager( self::$prefix, 'm_element' );
		self::$manager = null;
	}

	public function test_create() {
		$model = self::$manager->create();
		$this->assertInstanceOf( 'Leaves_And_Love\Sample_DB_Objects\Sample', $model );
	}

	public function test_get() {
		$model = self::$manager->create();

		$model = self::$manager->get( $model );
		$this->assertInstanceOf( 'Leaves_And_Love\Sample_DB_Objects\Sample', $model );

		$model->sync_upstream();

		$model = self::$manager->get( $model->id );
		$this->assertInstanceOf( 'Leaves_And_Love\Sample_DB_Objects\Sample', $model );

		$this->assertNull( self::$manager->get( 0 ) );
	}

	public function test_query() {
		$model_ids = array();

		for ( $i = 0; $i < 5; $i++ ) {
			$model_ids[] = self::$manager->add( array(
				'type'      => 'very_unique_type',
				'parent_id' => $i,
			) );
		}

		for ( $i = 0; $i < 4; $i++ ) {
			self::$manager->add( array(
				'type' => 'another_type',
			) );
		}

		$expected = array_reverse( $model_ids );

		$result = self::$manager->query( array(
			'fields'  => 'ids',
			'type'    => 'very_unique_type',
			'orderby' => array( 'parent_id' => 'DESC' ),
		) );
		$this->assertInstanceOf( 'Leaves_And_Love\Sample_DB_Objects\Sample_Collection', $result );
		$this->assertEquals( $expected, $result->to_json()['models'] );
	}

	public function test_get_collection() {
		$model_ids = array( 1, 3, 5, 7, 9 );

		$collection = self::$manager->get_collection( $model_ids, 10, 'ids' );
		$this->assertInstanceOf( 'Leaves_And_Love\Sample_DB_Objects\Sample_Collection', $collection );
		$this->assertEquals( $model_ids, $collection->to_json()['models'] );
		$this->assertSame( 10, $collection->get_total() );
	}

	public function test_add() {
		$model_id = self::$manager->add( array(
			'type'    => 'some_type',
			'title'   => 'A Title',
			'content' => rand_long_str( 500 ),
		) );
		$this->assertInternalType( 'int', $model_id );

		$model = self::$manager->get( $model_id );
		$this->assertInstanceOf( 'Leaves_And_Love\Sample_DB_Objects\Sample', $model );
		$this->assertSame( $model_id, $model->id );
	}

	public function test_update() {
		$type = 'some_type';

		$model_id = self::$manager->add( array(
			'type'    => $type,
			'title'   => 'A Title',
			'content' => rand_long_str( 500 ),
		) );

		$title = 'Another title';
		$content = rand_long_str( 350 );
		$parent_id = 32;

		$result = self::$manager->update( $model_id, array(
			'title'     => $title,
			'content'   => $content,
			'parent_id' => $parent_id,
		) );
		$this->assertTrue( $result );

		$model = self::$manager->get( $model_id );
		$this->assertInstanceOf( 'Leaves_And_Love\Sample_DB_Objects\Sample', $model );
		$this->assertSame( $type, $model->type );
		$this->assertSame( $title, $model->title );
		$this->assertSame( $content, $model->content );
		$this->assertSame( $parent_id, $model->parent_id );
	}

	public function test_delete() {
		$model_id = self::$manager->add( array( 'type' => 'randomtype' ) );

		$model = self::$manager->get( $model_id );
		$this->assertInstanceOf( 'Leaves_And_Love\Sample_DB_Objects\Sample', $model );

		$result = self::$manager->delete( $model_id );
		$this->assertTrue( $result );

		$model = self::$manager->get( $model_id );
		$this->assertNull( $model );
	}

	public function test_fetch() {
		$type = 'foo';
		$title = 'Foo';

		$model_id = self::$manager->add( array(
			'type'  => $type,
			'title' => $title,
		) );

		$model_raw = self::$manager->fetch( $model_id );
		$this->assertInstanceOf( 'stdClass', $model_raw );
		$this->assertEquals( $type, $model_raw->type );
		$this->assertEquals( $title, $model_raw->title );

		$this->assertNull( self::$manager->fetch( 0 ) );
	}

	public function test_get_primary_property() {
		$this->assertSame( 'id', self::$manager->get_primary_property() );
	}

	public function test_get_type_property() {
		$this->assertSame( 'type', self::$manager->get_type_property() );
	}

	public function test_get_message() {
		$message = self::$manager->get_message( 'db_insert_error' );
		$this->assertSame( 'Could not insert m_element into the database.', $message );

		$message = self::$manager->get_message( 'invalid' );
		$this->assertEmpty( $message );
	}

	public function test_add_to_cache() {
		$result = self::$manager->add_to_cache( 'randomkey1', 'foo' );
		$this->assertTrue( $result );

		$result = self::$manager->add_to_cache( 'randomkey1', 'bar' );
		$this->assertFalse( $result );
	}

	public function test_delete_from_cache() {
		$result = self::$manager->delete_from_cache( 'randomkey2' );
		$this->assertFalse( $result );

		self::$manager->add_to_cache( 'randomkey2', 'foo' );

		$result = self::$manager->delete_from_cache( 'randomkey2' );
		$this->assertTrue( $result );
	}

	public function test_get_from_cache() {
		$result = self::$manager->get_from_cache( 'randomkey3' );
		$this->assertFalse( $result );

		self::$manager->add_to_cache( 'randomkey3', 'foo' );

		$result = self::$manager->get_from_cache( 'randomkey3' );
		$this->assertEquals( 'foo', $result );
	}

	public function test_replace_in_cache() {
		$result = self::$manager->replace_in_cache( 'randomkey4', 'foo' );
		$this->assertFalse( $result );

		self::$manager->add_to_cache( 'randomkey4', 'foo' );

		$result = self::$manager->replace_in_cache( 'randomkey4', 'bar' );
		$this->assertTrue( $result );
	}

	public function test_set_in_cache() {
		$result = self::$manager->set_in_cache( 'randomkey1', 'foo' );
		$this->assertTrue( $result );

		$result = self::$manager->set_in_cache( 'randomkey1', 'bar' );
		$this->assertTrue( $result );
	}

	public function test_add_meta() {
		$model_id = self::$manager->add( array( 'type' => 'randomtype' ) );

		$result = self::$manager->add_meta( $model_id, 'key', 'foo' );
		$this->assertInternalType( 'int', $result );
	}

	public function test_update_meta() {
		$model_id = self::$manager->add( array( 'type' => 'randomtype' ) );

		$result = self::$manager->update_meta( $model_id, 'key', 'foo' );
		$this->assertInternalType( 'int', $result );
	}

	public function test_delete_meta() {
		$model_id = self::$manager->add( array( 'type' => 'randomtype' ) );

		self::$manager->add_meta( $model_id, 'key', 'foo' );

		$result = self::$manager->delete_meta( $model_id, 'key' );
		$this->assertTrue( $result );
	}

	public function test_get_meta() {
		$model_id = self::$manager->add( array( 'type' => 'randomtype' ) );

		self::$manager->add_meta( $model_id, 'key', 'foo' );

		$result = self::$manager->get_meta( $model_id, 'key', true );
		$this->assertEquals( 'foo', $result );
	}

	public function test_meta_exists() {
		$model_id = self::$manager->add( array( 'type' => 'randomtype' ) );

		self::$manager->add_meta( $model_id, 'key', 'foo' );

		$result = self::$manager->meta_exists( $model_id, 'key' );
		$this->assertTrue( $result );
	}

	public function test_delete_all_meta() {
		$model_id = self::$manager->add( array( 'type' => 'randomtype' ) );

		for ( $i = 1; $i <= 5; $i++ ) {
			self::$manager->add_meta( $model_id, 'key' . $i, 'foo' );
		}

		$result = self::$manager->delete_all_meta( $model_id );
		$this->assertTrue( $result );
	}

	public function test_register_type() {
		$result = self::$manager->register_type( 'type1' );
		$this->assertTrue( $result );
	}

	public function test_get_type() {
		self::$manager->register_type( 'type2' );

		$type = self::$manager->get_type( 'type2' );
		$this->assertInstanceOf( 'Leaves_And_Love\Sample_DB_Objects\Sample_Type', $type );
	}

	public function test_query_types() {
		$args = array( 'key' => 'foo' );
		self::$manager->register_type( 'type3', $args );

		$types = self::$manager->query_types( array_merge( $args, array(
			'field' => 'slug',
		) ) );
		$this->assertEquals( array( 'type3' => 'type3' ), $types );
	}

	public function test_unregister_type() {
		self::$manager->register_type( 'type4' );

		$result = self::$manager->unregister_type( 'type4' );
		$this->assertTrue( $result );

		$result = self::$manager->unregister_type( 'invalid' );
		$this->assertFalse( $result );
	}
}
