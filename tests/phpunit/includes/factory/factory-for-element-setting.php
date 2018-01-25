<?php

namespace awsmug\Torro_Forms\Tests;

use WP_UnitTest_Generator_Sequence;

class Factory_For_Element_Setting extends Factory_For_Thing implements Factory_For_Thing_With_Parent {

	use Factory_For_Thing_With_Parent_Trait;

	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		$this->service_slug         = 'element_settings';
		$this->parent_id_field_name = 'element_id';

		if ( empty( $default_generation_definitions ) ) {
			$default_generation_definitions = array(
				'element_id' => 0,
				'name'       => new WP_UnitTest_Generator_Sequence( 'setting_name_%s' ),
				'value'      => new WP_UnitTest_Generator_Sequence( 'Setting Value %s' ),
			);
		}

		parent::__construct( $factory, $default_generation_definitions );
	}
}
