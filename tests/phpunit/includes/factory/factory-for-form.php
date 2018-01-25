<?php

namespace awsmug\Torro_Forms\Tests;

use WP_UnitTest_Generator_Sequence;

class Factory_For_Form extends Factory_For_Thing implements Factory_For_Thing_With_Children {

	use Factory_For_Thing_With_Children_Trait;

	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		$this->service_slug        = 'forms';
		$this->child_id_field_name = 'form_id';
		$this->child_factories     = array(
			'containers'  => 'container',
			'submissions' => 'submission',
		);

		if ( empty( $default_generation_definitions ) ) {
			$default_generation_definitions = array(
				'title'  => new WP_UnitTest_Generator_Sequence( 'Form Title %s' ),
				'status' => 'publish',
			);
		}

		parent::__construct( $factory, $default_generation_definitions );
	}
}
