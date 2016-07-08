<?php

class Tests_Torro_Forms_Manager extends Torro_UnitTestCase {
	public function test_get_current() {
		$this->go_to( home_url() );
		$current_form = torro()->forms()->get_current();
		$this->assertInstanceOf( 'Torro_Error', $current_form );

		$form_id = self::factory()->form->create();
		$this->go_to( get_permalink( $form_id ) );
		$current_form = torro()->forms()->get_current();
		$this->assertInstanceOf( 'Torro_Form', $current_form );
		$this->assertEquals( $form_id, $current_form->id );
	}

	public function test_get_current_form_id() {
		$this->go_to( home_url() );
		$current_id = torro()->forms()->get_current_form_id();
		$this->assertNull( $current_id );

		$form_id = self::factory()->form->create();
		$this->go_to( get_permalink( $form_id ) );
		$current_id = torro()->forms()->get_current_form_id();
		$this->assertEquals( $form_id, $current_id );
	}

	public function test_get_content() {
		$this->go_to( home_url() );
		$current_content = torro()->forms()->get_content();
		$this->assertEmpty( $current_content );

		$form_id = self::factory()->form->create();
		$this->go_to( get_permalink( $form_id ) );
		$current_content = torro()->forms()->get_content();
		$this->assertContains( $form_id, $current_content ); // There might be a more accurate assertion for that test.
	}

	public function test_create() {
		$title = 'Test Form';

		$form = torro()->forms()->create( array( 'title' => $title ) );
		$this->assertInstanceOf( 'Torro_Form', $form );
		$this->assertEquals( $title, $form->title );
	}

	public function test_update() {
		$form_id = self::factory()->form->create();

		$title = 'Test Form Title';
		$form = torro()->forms()->update( $form_id, array( 'title' => $title ) );
		$this->assertInstanceOf( 'Torro_Form', $form );
		$this->assertEquals( $form_id, $form->id );
		$this->assertEquals( $title, $form->title );
	}

	public function test_get() {
		$form_id = self::factory()->form->create();

		$form = torro()->forms()->get( $form_id );
		$this->assertInstanceOf( 'Torro_Form', $form );
		$this->assertEquals( $form_id, $form->id );
		$this->assertEquals( $title, $form->title );
	}

	public function test_query() {
		$form_ids = self::factory()->form->create_many( 5 );

		$queried_forms = torro()->forms()->query( array( 'number' => -1 ) );
		$this->assertContainsOnlyInstancesOf( 'Torro_Form', $queried_forms );
		$queried_form_ids = wp_list_pluck( $queried_forms, 'id' );
		$this->assertArraySubset( $form_ids, $queried_form_ids );

		$form_count = count( $queried_forms );
		$queried_forms = torro()->forms()->query( array( 'number' => 2 ) );
		$this->assertCount( 2, $queried_forms );
		$queried_forms = torro()->forms()->query( array( 'number' => 100 ) );
		$this->assertCount( $form_count, $queried_forms );

		$queried_forms = torro()->forms()->query( array( 'post__in' => $form_ids, 'orderby' => 'ID', 'order' => 'DESC' ) );
		$queried_form_ids = wp_list_pluck( $queried_forms, 'id' );
		rsort( $form_ids );
		$this->assertEquals( $form_ids, $queried_form_ids );
	}

	public function test_copy() {
		$form_id = self::factory()->form->create();

		$form = torro()->forms()->get( $form_id );
		$copied_form = torro()->forms()->copy( $form_id );
		$this->assertEquals( $form->title, $copied_form->title );
		$copied_form_post = get_post( $copied_form->id );
		$this->assertEquals( 'publish', $copied_form_post->post_status );

		$copied_form = torro()->forms()->copy( $form_id, array( 'as_draft' => true ) );
		$copied_form_post = get_post( $copied_form->id );
		$this->assertEquals( 'draft', $copied_form_post->post_status );
	}

	public function test_delete() {
		$form_id = self::factory()->form->create();

		$form = torro()->forms()->delete( $form_id );

		$form_post = get_post( $form_id );
		$this->assertNull( $form_post );
	}
}
