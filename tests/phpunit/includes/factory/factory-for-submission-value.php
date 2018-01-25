<?php

namespace awsmug\Torro_Forms\Tests;

use WP_UnitTest_Generator_Sequence;

class Factory_For_Submission_Value extends Factory_For_Thing implements Factory_For_Thing_With_Parent {

	use Factory_For_Thing_With_Parent_Trait;

	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		$this->service_slug         = 'submission_values';
		$this->parent_id_field_name = 'submission_id';

		if ( empty( $default_generation_definitions ) ) {
			$default_generation_definitions = array(
				'submission_id' => 0,
				'element_id'    => 0,
				'field'         => '',
				'value'         => new WP_UnitTest_Generator_Sequence( 'Submission Value %s' ),
			);
		}

		parent::__construct( $factory, $default_generation_definitions );
	}
}
