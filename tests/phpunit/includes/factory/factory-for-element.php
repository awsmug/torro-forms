<?php

class Torro_UnitTest_Factory_For_Element extends Torro_UnitTest_Factory_For_Thing_With_Children implements Torro_UnitTest_Factory_For_Thing_With_Parent {
	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		$this->child_factories = array(
			'element_answers'	=> 'element_answer',
			'element_settings'	=> 'element_setting',
		);
		$this->id_field_name = 'element_id';

		if ( empty( $default_generation_definitions ) ) {
			$default_generation_definitions = array(
				'container_id'	=> 0,
				'type'			=> new WP_UnitTest_Generator_Sequence( 'element_type_%s' ),
				'label'			=> new WP_UnitTest_Generator_Sequence( 'Element Label %s' ),
				'sort'			=> new WP_UnitTest_Generator_Sequence( '%s' ),
			);
		}
		parent::__construct( $factory, $default_generation_definitions );
	}

	public function create_object( $args ) {
		$container_id = $args['container_id'];
		unset( $args['container_id'] );

		$element = torro()->elements()->create( $container_id, $args );
		if ( is_wp_error( $element ) ) {
			return $element;
		}

		return $element->id;
	}

	public function update_object( $id, $args ) {
		$element = torro()->elements()->update( $id, $args );
		if ( is_wp_error( $element ) ) {
			return $element;
		}

		return true;
	}

	public function update_parent( $id, $parent_id ) {
		return $this->update_object( $id, array( 'container_id' => $parent_id ) );
	}

	public function get_object_by_id( $id ) {
		return torro()->elements()->get( $id );
	}
}
