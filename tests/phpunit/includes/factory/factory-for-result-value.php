<?php

class Torro_UnitTest_Factory_For_Result_Value extends Torro_UnitTest_Factory_For_Thing implements Torro_UnitTest_Factory_For_Thing_With_Parent {
	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		if ( empty( $default_generation_definitions ) ) {
			$default_generation_definitions = array(
				'result_id'		=> 0,
				'element_id'	=> 0,
				'value'			=> new WP_UnitTest_Generator_Sequence( 'Result Value %s' ),
			);
		}
		parent::__construct( $factory, $default_generation_definitions );
	}

	public function create_object( $args ) {
		$result_id = $args['result_id'];
		unset( $args['result_id'] );

		$result_value = torro()->result_values()->create( $result_id, $args );
		if ( is_wp_error( $result_value ) ) {
			return $result_value;
		}

		return $result_value->id;
	}

	public function update_object( $id, $args ) {
		$result_value = torro()->result_values()->update( $id, $args );
		if ( is_wp_error( $result_value ) ) {
			return $result_value;
		}

		return true;
	}

	public function update_parent( $id, $parent_id ) {
		return $this->update_object( $id, array( 'result_id' => $parent_id ) );
	}

	public function get_object_by_id( $id ) {
		return torro()->result_values()->get( $id );
	}
}
