<?php

class Torro_UnitTest_Factory_For_Form extends Torro_UnitTest_Factory_For_Thing_With_Children {
	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		$this->child_factories = array(
			'containers'	=> 'container',
			'participants'	=> 'participant',
			'results'		=> 'result',
		);
		$this->id_field_name = 'form_id';

		if ( empty( $default_generation_definitions ) ) {
			$default_generation_definitions = array(
				'title'			=> new WP_UnitTest_Generator_Sequence( 'Form Title %s' ),
			);
		}
		parent::__construct( $factory, $default_generation_definitions );
	}

	public function create_object( $args ) {
		$form = torro()->forms()->create( $args );
		if ( is_wp_error( $form ) ) {
			return $form;
		}

		return $form->id;
	}

	public function update_object( $id, $args ) {
		$form = torro()->forms()->update( $id, $args );
		if ( is_wp_error( $form ) ) {
			return $form;
		}

		return true;
	}

	public function get_object_by_id( $id ) {
		return torro()->forms()->get( $id );
	}
}
