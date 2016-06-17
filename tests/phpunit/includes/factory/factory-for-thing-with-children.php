<?php

abstract class Torro_UnitTest_Factory_For_Thing_With_Children extends Torro_UnitTest_Factory_For_Thing {
	protected $child_factories = array();
	protected $id_field_name;

	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		parent::__construct( $factory, $default_generation_definitions );
	}

	public function create_full( $args = array() ) {
		$children = array();
		foreach ( $this->child_factories as $field_name => $factory_name ) {
			if ( isset( $args[ $field_name ] ) ) {
				$children[ $field_name ] = $args[ $field_name ];
				unset( $args[ $field_name ] );
			}
		}

		$current_id = $this->create( $args );
		if ( ! $current_id || is_wp_error( $current_id ) ) {
			return $current_id;
		}

		if ( ! empty( $children ) ) {
			$factory = Torro_UnitTestCase::factory();

			foreach ( $children as $field_name => $field_children ) {
				$current_factory = $factory->{$this->child_factories[ $field_name ]};

				foreach ( $field_children as $child_args ) {
					if ( $this->id_field_name ) {
						$child_args[ $this->id_field_name ] = $current_id;
					}
					$child_id = $current_factory->create_full( $child_args );
					if ( ! $child_id || is_wp_error( $child_id ) ) {
						return $child_id;
					}
				}
			}
		}

		return $current_id;
	}
}
