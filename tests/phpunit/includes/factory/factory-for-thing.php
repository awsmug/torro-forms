<?php

abstract class Torro_UnitTest_Factory_For_Thing extends WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		parent::__construct( $factory, $default_generation_definitions );
	}

	public function create_full( $args = array() ) {
		return $this->create( $args );
	}
}
