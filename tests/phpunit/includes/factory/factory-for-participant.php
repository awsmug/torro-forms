<?php

class Torro_UnitTest_Factory_For_Participant extends Torro_UnitTest_Factory_For_Thing implements Torro_UnitTest_Factory_For_Thing_With_Parent {
	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		if ( empty( $default_generation_definitions ) ) {
			$default_generation_definitions = array(
				'form_id'		=> 0,
				'user_id'		=> new WP_UnitTest_Generator_Sequence( '%s' ),
			);
		}
		parent::__construct( $factory, $default_generation_definitions );
	}

	public function create_object( $args ) {
		$form_id = $args['form_id'];
		unset( $args['form_id'] );

		$participant = torro()->participants()->create( $form_id, $args );
		if ( is_wp_error( $participant ) ) {
			return $participant;
		}

		return $participant->id;
	}

	public function update_object( $id, $args ) {
		$participant = torro()->participants()->update( $id, $args );
		if ( is_wp_error( $participant ) ) {
			return $participant;
		}

		return true;
	}

	public function update_parent( $id, $parent_id ) {
		return $this->update_object( $id, array( 'form_id' => $parent_id ) );
	}

	public function get_object_by_id( $id ) {
		return torro()->participants()->get( $id );
	}
}
