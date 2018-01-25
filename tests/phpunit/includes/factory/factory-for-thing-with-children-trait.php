<?php

namespace awsmug\Torro_Forms\Tests;

trait Factory_For_Thing_With_Children_Trait {

	protected $child_id_field_name;
	protected $child_factories = array();

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
			$factory = Unit_Test_Case::factory();

			foreach ( $children as $field_name => $field_children ) {
				$current_factory = $factory->{$this->child_factories[ $field_name ]};

				foreach ( $field_children as $child_args ) {
					if ( $this->child_id_field_name ) {
						$child_args[ $this->child_id_field_name ] = $current_id;
					}

					if ( $current_factory instanceof Factory_For_Thing_With_Children ) {
						$child_id = $current_factory->create_full( $child_args );
					} else {
						$child_id = $current_factory->create( $child_args );
					}

					if ( ! $child_id || is_wp_error( $child_id ) ) {
						return $child_id;
					}
				}
			}
		}

		return $current_id;
	}
}
