<?php

class Torro_UnitTest_Factory_For_Result extends Torro_UnitTest_Factory_For_Thing_With_Children implements Torro_UnitTest_Factory_For_Thing_With_Parent {
	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		$this->child_factories = array(
			'result_values'	=> 'result_value',
		);
		$this->id_field_name = 'result_id';

		if ( empty( $default_generation_definitions ) ) {
			$default_generation_definitions = array(
				'form_id'		=> 0,
				'user_id'		=> new WP_UnitTest_Generator_Sequence( '%s' ),
				'timestamp'		=> new WP_UnitTest_Generator_Sequence( substr( current_time( 'timestamp' ), 0, -1 ) . '%s' ),
				'remote_addr'	=> new WP_UnitTest_Generator_Sequence( '192.168.0.%s' ),
				'cookie_key'	=> '',
			);
		}
		parent::__construct( $factory, $default_generation_definitions );
	}

	public function create_object( $args ) {
		$form_id = $args['form_id'];
		unset( $args['form_id'] );

		$result = torro()->results()->create( $form_id, $args );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $result->id;
	}

	public function update_object( $id, $args ) {
		$result = torro()->results()->update( $id, $args );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return true;
	}

	public function update_parent( $id, $parent_id ) {
		return $this->update_object( $id, array( 'form_id' => $parent_id ) );
	}

	public function get_object_by_id( $id ) {
		return torro()->results()->get( $id );
	}
}
