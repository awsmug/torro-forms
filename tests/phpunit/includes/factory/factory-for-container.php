<?php

class Torro_UnitTest_Factory_For_Container extends Torro_UnitTest_Factory_For_Thing_With_Children implements Torro_UnitTest_Factory_For_Thing_With_Parent {
	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		$this->child_factories = array(
			'elements'	=> 'element',
		);
		$this->id_field_name = 'container_id';

		if ( empty( $default_generation_definitions ) ) {
			$default_generation_definitions = array(
				'form_id'		=> 0,
				'label'			=> new WP_UnitTest_Generator_Sequence( 'Page %s' ),
				'sort'			=> new WP_UnitTest_Generator_Sequence( '%s' ),
			);
		}
		parent::__construct( $factory, $default_generation_definitions );
	}

	public function create_object( $args ) {
		$form_id = $args['form_id'];
		unset( $args['form_id'] );

		$container = torro()->containers()->create( $form_id, $args );
		if ( is_wp_error( $container ) ) {
			return $container;
		}

		return $container->id;
	}

	public function update_object( $id, $args ) {
		$container = torro()->containers()->update( $id, $args );
		if ( is_wp_error( $container ) ) {
			return $container;
		}

		return true;
	}

	public function update_parent( $id, $parent_id ) {
		return $this->update_object( $id, array( 'form_id' => $parent_id ) );
	}

	public function get_object_by_id( $id ) {
		return torro()->containers()->get( $id );
	}
}
