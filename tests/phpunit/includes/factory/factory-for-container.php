<?php

namespace awsmug\Torro_Forms\Tests;

use WP_UnitTest_Generator_Sequence;

class Factory_For_Container extends Factory_For_Thing implements Factory_For_Thing_With_Parent, Factory_For_Thing_With_Children {

	use Factory_For_Thing_With_Parent_Trait;
	use Factory_For_Thing_With_Children_Trait;

	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		$this->service_slug         = 'containers';
		$this->parent_id_field_name = 'form_id';
		$this->child_id_field_name  = 'container_id';
		$this->child_factories      = array(
			'elements' => 'element',
		);

		if ( empty( $default_generation_definitions ) ) {
			$default_generation_definitions = array(
				'form_id' => 0,
				'label'   => new WP_UnitTest_Generator_Sequence( 'Page %s' ),
				'sort'    => new WP_UnitTest_Generator_Sequence( '%s' ),
			);
		}

		parent::__construct( $factory, $default_generation_definitions );
	}
}
