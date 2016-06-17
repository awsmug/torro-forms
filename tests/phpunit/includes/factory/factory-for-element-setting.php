<?php

class Torro_UnitTest_Factory_For_Element_Setting extends Torro_UnitTest_Factory_For_Thing implements Torro_UnitTest_Factory_For_Thing_With_Parent {
	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		if ( empty( $default_generation_definitions ) ) {
			$default_generation_definitions = array(
				'element_id'	=> 0,
				'name'			=> new WP_UnitTest_Generator_Sequence( 'setting_name_%s' ),
				'value'			=> new WP_UnitTest_Generator_Sequence( 'Setting Value %s' ),
			);
		}
		parent::__construct( $factory, $default_generation_definitions );
	}

	public function create_object( $args ) {
		$element_id = $args['element_id'];
		unset( $args['element_id'] );

		$element_setting = torro()->element_settings()->create( $element_id, $args );
		if ( is_wp_error( $element_setting ) ) {
			return $element_setting;
		}

		return $element_setting->id;
	}

	public function update_object( $id, $args ) {
		$element_setting = torro()->element_settings()->update( $id, $args );
		if ( is_wp_error( $element_setting ) ) {
			return $element_setting;
		}

		return true;
	}

	public function update_parent( $id, $parent_id ) {
		return $this->update_object( $id, array( 'element_id' => $parent_id ) );
	}

	public function get_object_by_id( $id ) {
		return torro()->element_settings()->get( $id );
	}
}
