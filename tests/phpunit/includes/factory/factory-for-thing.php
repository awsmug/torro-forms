<?php

abstract class Torro_UnitTest_Factory_For_Thing extends WP_UnitTest_Factory_For_Thing {

	public function create_full( $args = array() ) {
		return $this->create( $args );
	}
}
