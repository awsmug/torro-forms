<?php

namespace awsmug\Torro_Forms\Tests;

use WP_UnitTest_Factory_For_Thing;
use WP_Error;

abstract class Factory_For_Thing extends WP_UnitTest_Factory_For_Thing {

	protected $service_slug;

	public function create_object( $args ) {
		$service_slug = $this->service_slug;

		$thing = torro()->$service_slug()->create();

		foreach ( $args as $key => $value ) {
			$thing->$key = $value;
		}

		$result = $thing->sync_upstream();
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $thing->id;
	}

	public function update_object( $id, $args ) {
		$service_slug = $this->service_slug;

		$thing = torro()->$service_slug()->get( $id );
		if ( ! $thing ) {
			return new WP_Error( 'invalid_id', 'Invalid ID!' );
		}

		foreach ( $args as $key => $value ) {
			$thing->$key = $value;
		}

		return $thing->sync_upstream();
	}

	public function get_object_by_id( $id ) {
		$service_slug = $this->service_slug;

		return torro()->$service_slug()->get( $id );
	}
}
