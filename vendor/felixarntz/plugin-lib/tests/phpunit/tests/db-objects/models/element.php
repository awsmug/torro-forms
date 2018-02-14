<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Sample_DB_Objects\Sample;

/**
 * @group db-objects
 * @group models
 * @group elements
 */
class Tests_Element extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;
	protected static $other_site_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_element_';

		self::$manager = self::setUpSampleManager( self::$prefix, 'element' );

		if ( is_multisite() ) {
			self::$other_site_id = self::factory()->blog->create();
		}
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::tearDownSampleManager( self::$prefix, 'element' );
		self::$manager = null;

		if ( is_multisite() ) {
			wpmu_delete_blog( self::$other_site_id, true );
		}
	}

	public function test_setgetisset_property() {
		$model = new Sample( self::$manager );

		$this->assertSame( 0, $model->id );

		$model->id = 4;
		$this->assertSame( 0, $model->id );

		$this->assertTrue( isset( $model->title ) );

		$title = 'Element Title 1';
		$model->title = $title;
		$this->assertSame( $title, $model->title );
	}

	public function test_setgetisset_meta() {
		$model = new Sample( self::$manager );

		$this->assertFalse( isset( $model->random_value ) );
		$this->assertNull( $model->random_value );

		$value = 'foobar';
		$model->random_value = $value;
		$this->assertTrue( isset( $model->random_value ) );
		$this->assertSame( $value, $model->random_value );

		$model->random_value2 = 'foo';
		$model->random_value2 = null;
		$this->assertFalse( isset( $model->random_value2 ) );

		$model->sync_upstream();
		$this->assertTrue( isset( $model->random_value ) );
		$this->assertSame( $value, $model->random_value );

		$value = 'bar';
		$model->random_value = $value;
		$this->assertTrue( isset( $model->random_value ) );
		$this->assertSame( $value, $model->random_value );

		$model->random_value = null;
		$this->assertFalse( isset( $model->random_value ) );

		$this->assertFalse( isset( $model->invalid_meta_key ) );
		$this->assertNull( $model->invalid_meta_key );
	}

	public function test_setgetisset_invalid() {
		$model = new Sample( self::$manager );

		$this->assertFalse( isset( $model->pending_properties ) );
		$this->assertNull( $model->pending_properties );
		$model->pending_properties = '';
		$this->assertNull( $model->pending_properties );
	}

	public function test_sync_upstream() {
		$model = new Sample( self::$manager );

		$model->type = 'foo';
		$model->title = 'Element Title';
		$model->content = rand_long_str( 1500 );
		$model->author_name = 'John Doe';

		$result = $model->sync_upstream();
		$this->assertTrue( $result );

		$this->assertTrue( 0 != $model->id );
	}

	public function test_sync_downstream() {
		$model = new Sample( self::$manager );

		$result = $model->sync_downstream();
		$this->assertWPError( $result );

		$type = 'foo';
		$title = 'Element Title';
		$random = array( 1, 2, 3 );

		$model->type = $type;
		$model->title = $title;
		$model->random = $random;

		$model->sync_upstream();

		$new_type = 'bar';
		$new_title = 'Element Bar Title';
		$new_random = array( 'bar' );

		self::$manager->update( $model->id, array(
			'type'  => $new_type,
			'title' => $new_title,
		) );
		self::$manager->update_meta( $model->id, 'random', $new_random );

		$this->assertSame( $type, $model->type );
		$this->assertSame( $title, $model->title );

		$result = $model->sync_downstream();
		$this->assertTrue( $result );

		$this->assertSame( $new_type, $model->type );
		$this->assertSame( $new_title, $model->title );
		$this->assertSame( $new_random, $model->random );
	}

	public function test_sync_while_switched() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Test only runs in multisite' );
		}

		$title = 'Very Unique Title';

		$model = new Sample( self::$manager );
		$model->title = $title;

		$current_site_id = get_current_blog_id();

		switch_to_blog( self::$other_site_id );

		$this->assertSame( $current_site_id, $model->get_site_id() );

		$model->sync_upstream();

		restore_current_blog();

		$db_object = self::$manager->fetch( $model->id );
		$this->assertInstanceOf( 'stdClass', $db_object );
		$this->assertEquals( $title, $db_object->title );
	}

	public function test_delete() {
		$model = new Sample( self::$manager );

		$result = $model->delete();
		$this->assertWPError( $result );

		$model->sync_upstream();

		$result = $model->delete();
		$this->assertTrue( $result );

		$this->assertSame( 0, $model->id );
	}

	public function test_to_json() {
		$properties = array(
			'type'      => 'foo',
			'status'    => 'bar',
			'author_id' => 22,
			'title'     => 'Hello',
			'content'   => rand_long_str( 500 ),
			'parent_id' => 3,
			'priority'  => 4.4,
			'active'    => false,
		);

		$meta = array(
			'author_name' => 'Bruce Wayne',
			'status'      => 'published',
		);

		$model_id = self::$manager->add( $properties );
		foreach ( $meta as $key => $value ) {
			self::$manager->add_meta( $model_id, $key, $value );
		}

		$model = self::$manager->get( $model_id );

		$expected = array_merge( array( 'id' => $model_id ), $properties, $meta );
		$this->assertEqualSetsWithIndex( $expected, $model->to_json() );
	}

	public function test_construct_set() {
		$db_obj = new \stdClass();
		$db_obj->id        = '23';
		$db_obj->type      = 'foo';
		$db_obj->status    = 'bar';
		$db_obj->author_id = '13';
		$db_obj->title     = 'Foo';
		$db_obj->content   = 'Some text content.';
		$db_obj->parent_id = '11';
		$db_obj->priority  = '1.7';
		$db_obj->active    = '1';
		// Invalid properties.
		$db_obj->manager   = 'This must not be set.';
		$db_obj->invalid   = 'This must not be set.';

		$model = new Sample( self::$manager, $db_obj );
		$this->assertSame( 23,                   $model->id );
		$this->assertSame( 'foo',                $model->type );
		$this->assertSame( 'bar',                $model->status );
		$this->assertSame( 13,                   $model->author_id );
		$this->assertSame( 'Foo',                $model->title );
		$this->assertSame( 'Some text content.', $model->content );
		$this->assertSame( 11,                   $model->parent_id );
		$this->assertSame( 1.7,                  $model->priority );
		$this->assertSame( true,                 $model->active );
		$this->assertNull( $model->manager );
		$this->assertNull( $model->invalid );
	}
}
