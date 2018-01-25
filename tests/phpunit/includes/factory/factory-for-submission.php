<?php

namespace awsmug\Torro_Forms\Tests;

use WP_UnitTest_Generator_Sequence;

class Factory_For_Submission extends Factory_For_Thing implements Factory_For_Thing_With_Parent, Factory_For_Thing_With_Children {

	use Factory_For_Thing_With_Parent_Trait;
	use Factory_For_Thing_With_Children_Trait;

	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		$this->service_slug         = 'submissions';
		$this->parent_id_field_name = 'form_id';
		$this->child_id_field_name  = 'submission_id';
		$this->child_factories      = array(
			'submission_values' => 'submission_value',
		);

		if ( empty( $default_generation_definitions ) ) {
			$default_generation_definitions = array(
				'form_id'     => 0,
				'user_id'     => new WP_UnitTest_Generator_Sequence( '%s' ),
				'timestamp'   => new WP_UnitTest_Generator_Sequence( substr( current_time( 'timestamp' ), 0, -1 ) . '%s' ),
				'remote_addr' => new WP_UnitTest_Generator_Sequence( '192.168.0.%s' ),
				'user_key'    => '',
				'status'      => 'completed',
			);
		}

		parent::__construct( $factory, $default_generation_definitions );
	}
}
